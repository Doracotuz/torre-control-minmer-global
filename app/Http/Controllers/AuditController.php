<?php

namespace App\Http\Controllers;

use App\Models\CsOrder;
use App\Models\CsOrderEvent;
use App\Models\Guia;
use App\Models\AuditIncidencia;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\UnidadArriboMail;


class AuditController extends Controller
{
    /**
     * Muestra el dashboard principal para auditores.
     */
    public function index(Request $request)
    {
        // --- Consulta para Órdenes ACTIVAS ---
        $activeQuery = CsOrder::query()->with(['plannings.guia', 'details']);
        if ($request->filled('search')) {
            // Si hay una búsqueda general, se aplica aquí
            $searchTerm = $request->search;
            $activeQuery->where(function($q) use ($searchTerm) {
                $q->where('so_number', 'like', "%{$searchTerm}%")
                  ->orWhere('invoice_number', 'like', "%{$searchTerm}%")
                  ->orWhereHas('plannings.guia', fn($gq) => $gq->where('guia', 'like', "%{$searchTerm}%"));
            });
        } else {
            // La consulta por defecto busca todos los estatus de auditoría que no sean 'Finalizada'.
            $activeQuery->where('audit_status', '!=', 'Finalizada');
        }
        if ($request->filled('status') && is_array($request->status)) {
            $activeQuery->whereIn('audit_status', $request->status);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $activeQuery->whereBetween('creation_date', [$request->start_date, $request->end_date]);
        }
        $auditableOrders = $activeQuery->orderBy('creation_date', 'desc')->paginate(15, ['*'], 'page');

        // --- Consulta para Órdenes TERMINADAS ---
        $completedQuery = CsOrder::query()->with(['plannings.guia', 'details'])
                                 ->where('audit_status', 'Finalizada');
        if ($request->filled('search_completed')) {
            $searchTerm = $request->search_completed;
            $completedQuery->where(function($q) use ($searchTerm) {
                $q->where('so_number', 'like', "%{$searchTerm}%")
                  ->orWhere('invoice_number', 'like', "%{$searchTerm}%")
                  ->orWhereHas('plannings.guia', fn($gq) => $gq->where('guia', 'like', "%{$searchTerm}%"));
            });
        }
        $completedOrders = $completedQuery->orderBy('updated_at', 'desc')->paginate(10, ['*'], 'completedPage');

        // --- Lista de estatus para los filtros ---
        $auditStatuses = ['Pendiente Almacén', 'Pendiente Patio', 'Pendiente Carga'];

        return view('audit.index', compact('auditableOrders', 'completedOrders', 'auditStatuses'));
    }

    /**
     * Muestra el formulario para la auditoría de Almacén.
     */
    public function showWarehouseAudit($orderId)
    {
        $order = CsOrder::with('details.product', 'details.upc')->findOrFail($orderId);
        return view('audit.warehouse', compact('order'));
    }

    /**
     * Guarda los datos de la auditoría de almacén (versión simplificada).
     */
    public function storeWarehouseAudit(Request $request, $orderId)
    {
        $order = CsOrder::findOrFail($orderId);
        
        $validated = $request->validate([ 'items' => 'required|array', 'items.*.calidad' => 'required|string', 'observaciones' => 'nullable|string', 'items.*.sku_validado' => 'sometimes|boolean', 'items.*.piezas_validadas' => 'sometimes|boolean', 'items.*.upc_validado' => 'sometimes|boolean' ]);

        DB::transaction(function () use ($validated, $order, $request) {
            foreach ($validated['items'] as $detailId => $data) {
                $detail = $order->details()->find($detailId);
                if ($detail) {
                    $detail->update([ 'audit_calidad' => $data['calidad'], 'audit_sku_validado' => $request->input("items.{$detailId}.sku_validado", false), 'audit_piezas_validadas' => $request->input("items.{$detailId}.piezas_validadas", false), 'audit_upc_validado' => $request->input("items.{$detailId}.upc_validado", false) ]);
                }
            }
            
            $order->audit_almacen_observaciones = $validated['observaciones'];
            // --- CAMBIO CLAVE ---
            // Solo se actualiza el estatus de la auditoría.
            $order->audit_status = 'Pendiente Patio'; 
            $order->save();

            CsOrderEvent::create(['cs_order_id' => $order->id, 'user_id' => Auth::id(), 'description' => 'Auditoría de almacén completada. Esperando auditoría de patio.' ]);
        });

        return redirect()->route('audit.index')->with('success', 'Auditoría de almacén completada.');
    }

    /**
     * Muestra el formulario para la auditoría de Patio.
     */
    public function showPatioAudit($guiaId)
    {
        $guia = Guia::with('plannings.order')->findOrFail($guiaId);

        // --- INICIA VALIDACIÓN DE SINCRONIZACIÓN ---
        // 1. Obtenemos solo las órdenes que no están listas para el patio.
        $pendingOrders = $guia->plannings
            ->map(fn($p) => $p->order)
            ->filter(fn($o) => $o && $o->audit_status !== 'Pendiente Patio');

        // 2. Si hay alguna, construimos el mensaje y redirigimos.
        if ($pendingOrders->isNotEmpty()) {
            $pendingSoNumbers = $pendingOrders->pluck('so_number')->join(', ');
            $errorMessage = 'Aún no todas las órdenes de la guía han completado la auditoría de almacén. Faltan: SO ' . $pendingSoNumbers;
            return redirect()->route('audit.index')->with('error', $errorMessage);
        }
        // --- TERMINA VALIDACIÓN DE SINCRONIZACIÓN ---

        return view('audit.patio', compact('guia'));
    }

    /**
     * Guarda los datos de la auditoría de patio.
     */
    public function storePatioAudit(Request $request, $guiaId)
    {
        $guia = Guia::findOrFail($guiaId);
        $validated = $request->validate([ 'operador' => 'required|string', 'placas' => 'required|string', 'arribo_fecha' => 'required|date', 'arribo_hora' => 'required|date_format:H:i', 'caja_estado' => 'required|string', 'llantas_estado' => 'required|string', 'combustible_nivel' => 'required|string', 'equipo_sujecion' => 'required|string', 'presenta_maniobra' => 'sometimes|boolean', 'maniobra_personas' => 'required_if:presenta_maniobra,1|nullable|integer|min:1', 'foto_unidad' => 'required|image|max:5120', 'foto_llantas' => 'required|image|max:5120' ]);

        DB::transaction(function () use ($validated, $guia, $request) {
            $fotoUnidadPath = $request->file('foto_unidad')->store('audit_patio_unidad', 's3');
            $fotoLlantasPath = $request->file('foto_llantas')->store('audit_patio_llantas', 's3');

            $guia->update([
                // Se guardan los datos de la auditoría en la guía
                'operador' => $validated['operador'], 'placas' => $validated['placas'], 'audit_patio_arribo' => $validated['arribo_fecha'] . ' ' . $validated['arribo_hora'], 'audit_patio_caja_estado' => $validated['caja_estado'], 'audit_patio_llantas_estado' => $validated['llantas_estado'], 'audit_patio_combustible_nivel' => $validated['combustible_nivel'], 'audit_patio_equipo_sujecion' => $validated['equipo_sujecion'], 'audit_patio_presenta_maniobra' => $request->has('presenta_maniobra'), 'audit_patio_maniobra_personas' => $request->input('maniobra_personas'), 'audit_patio_fotos' => json_encode(['unidad' => $fotoUnidadPath, 'llantas' => $fotoLlantasPath]),
                // --- CAMBIO CLAVE ---
                // YA NO SE ACTUALIZA el campo 'estatus' de la guía.
            ]);

            // Ahora actualizamos el audit_status de TODAS las órdenes en la guía.
            foreach($guia->plannings as $planning) {
                if($planning->order) {
                    $planning->order->update(['audit_status' => 'Pendiente Carga']);
                    CsOrderEvent::create(['cs_order_id' => $planning->order->id, 'user_id' => Auth::id(), 'description' => 'Auditoría de patio completada. Esperando auditoría de carga.' ]);
                }
            }

            // El envío de correo sigue funcionando igual
            $areaNames = ['Auditoría', 'Tráfico'];
            $recipients = User::whereHas('area', function ($query) use ($areaNames) { $query->whereIn('name', $areaNames); })->get();
            $recipientEmails = $recipients->pluck('email')->all();
            if (!empty($recipientEmails)) {
                Mail::to($recipientEmails)->send(new UnidadArriboMail($guia));
            }
        });

        return redirect()->route('audit.index')->with('success', 'Auditoría de patio completada.');
    }

    /**
     * Muestra el formulario para la auditoría de Carga de Unidad.
     */
    public function showLoadingAudit($guiaId)
    {
        $guia = Guia::with('plannings.order.customer', 'facturas')->findOrFail($guiaId);

        // --- INICIA VALIDACIÓN DE SINCRONIZACIÓN ---
        $pendingOrders = $guia->plannings
            ->map(fn($p) => $p->order)
            ->filter(fn($o) => $o && $o->audit_status !== 'Pendiente Carga');

        if ($pendingOrders->isNotEmpty()) {
            $pendingSoNumbers = $pendingOrders->pluck('so_number')->join(', ');
            $errorMessage = 'Aún no todas las órdenes de la guía han completado la auditoría de patio. Faltan: SO ' . $pendingSoNumbers;
            return redirect()->route('audit.index')->with('error', $errorMessage);
        }
        $requirementsByCustomer = [];
        foreach ($guia->plannings as $planning) {
            $order = $planning->order;
            
            if ($order && $order->customer && !empty($order->customer->delivery_specifications)) {
                $customerName = $order->customer->name;
                $identifier = $order->so_number ?? $order->invoice_number;

                // Si es la primera vez que vemos a este cliente, guardamos sus especificaciones
                if (!isset($requirementsByCustomer[$customerName])) {
                    $specs = $order->customer->delivery_specifications;
                    $requirementsByCustomer[$customerName] = [
                        'entrega' => [],
                        'documentacion' => [],
                        'orders' => [] // Aquí guardaremos las órdenes de este cliente
                    ];

                    foreach ($specs as $specName => $isRequired) {
                        if ($isRequired) {
                            if (str_contains($specName, 'Entrega')) {
                                $requirementsByCustomer[$customerName]['entrega'][] = $specName;
                            } else {
                                $requirementsByCustomer[$customerName]['documentacion'][] = $specName;
                            }
                        }
                    }
                }
                // Añadimos la orden actual a la lista de este cliente
                $requirementsByCustomer[$customerName]['orders'][] = $identifier;
            }
        }
        return view('audit.loading', compact('guia', 'requirementsByCustomer'));
    }

    /**
     * Guarda los datos de la auditoría de carga (versión extendida).
     */
    public function storeLoadingAudit(Request $request, $guiaId)
    {
        $guia = Guia::findOrFail($guiaId);
        $validated = $request->validate([ 'foto_caja_vacia' => 'required|image|max:5120',
            'fotos_carga' => 'required|array|min:3', 
            'fotos_carga.*' => 'image|max:5120', 
            'marchamo_numero' => 'nullable|string|max:255', 
            'foto_marchamo' => 'nullable|image|max:5120', 
            'lleva_custodia' => 'required|boolean', 
            'incidencias' => 'nullable|array', 
            'incidencias.*' => 'string', 
            'validated_specs' => 'nullable|array', 
            // 'emplayado_correcto' => 'sometimes|boolean', 
            // 'etiquetado_correcto' => 'sometimes|boolean', 
            // 'distribucion_correcta' => 'sometimes|boolean', 
            'incluye_tarimas' => 'sometimes|boolean',
            'tarimas_tipo' => 'required_if:incluye_tarimas,1|in:Chep,Estándar,Ambas',
            'tarimas_cantidad_chep' => 'required_if:tarimas_tipo,Chep|required_if:tarimas_tipo,Ambas|nullable|integer|min:0',
            'tarimas_cantidad_estandar' => 'required_if:tarimas_tipo,Estándar|required_if:tarimas_tipo,Ambas|nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($validated, $guia, $request) {
            $fotoCajaVaciaPath = $request->file('foto_caja_vacia')->store('audit_carga/caja_vacia', 's3');
            $fotosCargaPaths = [];
            if ($request->hasFile('fotos_carga')) {
                foreach ($request->file('fotos_carga') as $foto) {
                    $fotosCargaPaths[] = $foto->store('audit_carga/proceso', 's3');
                }
            }
            $fotoMarchamoPath = null;
            if ($request->hasFile('foto_marchamo')) {
                $fotoMarchamoPath = $request->file('foto_marchamo')->store('audit_carga/marchamo', 's3');
            }

            if ($request->has('incidencias') && !empty($validated['incidencias'])) {
                foreach($validated['incidencias'] as $incidencia) {
                    if(!empty($incidencia)) {
                        AuditIncidencia::create([
                            'guia_id' => $guia->id,
                            'user_id' => Auth::id(),
                            'tipo_incidencia' => $incidencia,
                        ]);
                    }
                }
            }

            $validatedSpecs = $request->input('validated_specs', []);

            $guia->update([
                // Se guardan los datos de la auditoría en la guía
                'marchamo_numero' => $validated['marchamo_numero'], 
                'lleva_custodia' => $validated['lleva_custodia'], 
                // 'audit_carga_emplayado_correcto' => $request->has('emplayado_correcto'), 
                // 'audit_carga_etiquetado_correcto' => $request->has('etiquetado_correcto'), 
                // 'audit_carga_distribucion_correcta' => $request->has('distribucion_correcta'), 
                'audit_carga_incluye_tarimas' => $request->has('incluye_tarimas'),
                'audit_carga_tarimas_chep' => $request->input('tarimas_cantidad_chep'),
                'audit_carga_tarimas_estandar' => $request->input('tarimas_cantidad_estandar'),
                'audit_carga_fotos' => json_encode([ 'caja_vacia' => $fotoCajaVaciaPath, 
                'proceso_carga' => $fotosCargaPaths, 'marchamo' => $fotoMarchamoPath, 
                'validated_specifications' => $validatedSpecs ]),
                // --- CAMBIO CLAVE ---
                // YA NO SE ACTUALIZA el campo 'estatus' de la guía.
            ]);
            
            // Actualizamos el audit_status de TODAS las órdenes en la guía.
            foreach($guia->plannings as $planning) {
                if($planning->order) {
                    $planning->order->update(['audit_status' => 'Finalizada']);
                    CsOrderEvent::create(['cs_order_id' => $planning->order->id, 'user_id' => Auth::id(), 'description' => 'Proceso de auditoría de carga finalizado.' ]);
                }
            }
        });

        return redirect()->route('audit.index')->with('success', 'Auditoría de carga completada.');
    }

    public function reopenAudit(CsOrder $order)
    {
        if ($order->audit_status === 'Finalizada') {
            $order->update(['audit_status' => 'Pendiente Almacén']);

            CsOrderEvent::create([
                'cs_order_id' => $order->id,
                'user_id' => Auth::id(),
                'description' => 'Auditoría reabierta por ' . Auth::user()->name . '. El estatus ha vuelto a Pendiente Almacén.'
            ]);

            return redirect()->route('audit.index')->with('success', 'La auditoría para la SO ' . $order->so_number . ' ha sido reabierta.');
        }

        return redirect()->route('audit.index')->with('error', 'Esta auditoría no se puede reabrir.');
    }

}