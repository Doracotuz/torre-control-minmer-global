<?php

namespace App\Http\Controllers;

use App\Models\CsOrder;
use App\Models\CsOrderEvent;
use App\Models\Guia;
use App\Models\AuditIncidencia;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\UnidadArriboMail;


class AuditController extends Controller
{
    /**
     * Muestra el dashboard principal para auditores con las cargas del día.
     */
    public function index(Request $request)
    {
        $query = CsOrder::query()
            ->with(['plannings.guia', 'details']);

        // Si hay un término de búsqueda, la aplicamos primero y de forma amplia
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('so_number', 'like', "%{$searchTerm}%")
                ->orWhere('invoice_number', 'like', "%{$searchTerm}%")
                ->orWhereHas('plannings.guia', function ($guiaQuery) use ($searchTerm) {
                    $guiaQuery->where('guia', 'like', "%{$searchTerm}%");
                });
            });
            // Cuando se busca, no se aplica ningún otro filtro de estatus por defecto
        } else {
            // Si NO hay búsqueda, mostramos solo las tareas pendientes
            $query->where(function ($q) {
                $q->where('audit_status', 'Pendiente')
                ->orWhereHas('plannings.guia', function ($guiaQuery) {
                    $guiaQuery->whereIn('estatus', ['Planeada', 'En Cortina']);
                });
            });

            // --- CORRECCIÓN ---
            // Esta regla para ocultar completadas AHORA solo se aplica cuando NO estás buscando.
            $query->whereDoesntHave('plannings.guia', function ($guiaQuery) {
                $guiaQuery->whereIn('estatus', ['Completada', 'En Tránsito']);
            });
        }
        
        // La línea que causaba el error fue movida de aquí hacia arriba, dentro del 'else'.

        // Filtro por rango de fecha de creación
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('creation_date', [$request->start_date, $request->end_date]);
        }
        
        // Filtro por estatus de Tarea (este sigue funcionando en conjunto con la búsqueda)
        if ($request->filled('status') && is_array($request->status)) {
            $selectedStatuses = $request->status;
            
            $query->where(function ($q) use ($selectedStatuses) {
                if (in_array('Pendiente', $selectedStatuses)) {
                    $q->orWhere('audit_status', 'Pendiente');
                }

                $guiaStatuses = array_intersect($selectedStatuses, ['Planeada', 'En Cortina']);
                if (!empty($guiaStatuses)) {
                    $q->orWhereHas('plannings.guia', function ($guiaQuery) use ($guiaStatuses) {
                        $guiaQuery->whereIn('estatus', $guiaStatuses);
                    });
                }
            });
        }

        $auditableOrders = $query->orderBy('creation_date', 'desc')->paginate(15);
        return view('audit.index', compact('auditableOrders'));
    }



    /**
     * Muestra el formulario para la primera auditoría (Almacén).
     */
    public function showWarehouseAudit($orderId)
    {
        $order = CsOrder::with('details.product', 'details.upc')->findOrFail($orderId);
        return view('audit.warehouse', compact('order'));
    }

    /**
     * Guarda los datos de la auditoría de almacén.
     */
    public function storeWarehouseAudit(Request $request, $orderId)
    {
        $order = CsOrder::findOrFail($orderId);
        
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.calidad' => 'required|string',
            'observaciones' => 'nullable|string',
            'tarimas_cantidad' => 'required|integer|min:0',
            'tarimas_tipo' => 'required|string',
            'emplayado_correcto' => 'sometimes|boolean',
            'etiquetado_correcto' => 'sometimes|boolean',
            'distribucion_correcta' => 'sometimes|boolean',
            'items.*.sku_validado' => 'sometimes|boolean',
            'items.*.piezas_validadas' => 'sometimes|boolean',
            'items.*.upc_validado' => 'sometimes|boolean',
        ]);

        DB::transaction(function () use ($validated, $order, $request) {
            
            foreach ($validated['items'] as $detailId => $data) {
                $detail = $order->details()->find($detailId);
                if ($detail) {
                    $detail->update([
                        'audit_calidad' => $data['calidad'],
                        'audit_sku_validado' => $request->input("items.{$detailId}.sku_validado", false),
                        'audit_piezas_validadas' => $request->input("items.{$detailId}.piezas_validadas", false),
                        'audit_upc_validado' => $request->input("items.{$detailId}.upc_validado", false),
                    ]);
                }
            }
            
            $order->audit_almacen_observaciones = $validated['observaciones'];
            $order->audit_status = 'Completado'; 
            
            $order->audit_tarimas_cantidad = $validated['tarimas_cantidad'];
            $order->audit_tarimas_tipo = $validated['tarimas_tipo'];
            $order->audit_emplayado_correcto = $request->has('emplayado_correcto');
            $order->audit_etiquetado_correcto = $request->has('etiquetado_correcto');
            $order->audit_distribucion_correcta = $request->has('distribucion_correcta');
            
            $order->save();

            CsOrderEvent::create([
                'cs_order_id' => $order->id,
                'user_id' => Auth::id(),
                // Se ajusta la descripción del evento
                'description' => 'Auditoría de almacén completada por ' . Auth::user()->name . '.'
            ]);
        });

        return redirect()->route('audit.index')->with('success', 'Auditoría de almacén completada para SO: ' . $order->so_number);
    }

    /**
     * Muestra el formulario para la segunda auditoría (Arribo de Unidad).
     */
    public function showPatioAudit($guiaId)
    {
        $guia = Guia::findOrFail($guiaId);
        return view('audit.patio', compact('guia'));
    }

    /**
     * Guarda los datos de la auditoría de patio y envía notificación.
     */
    public function storePatioAudit(Request $request, $guiaId)
    {
        $guia = Guia::findOrFail($guiaId);
        $validated = $request->validate([
            'operador' => 'required|string',
            'placas' => 'required|string',
            'arribo_fecha' => 'required|date',
            'arribo_hora' => 'required|date_format:H:i',
            'caja_estado' => 'required|string',
            'llantas_estado' => 'required|string',
            'combustible_nivel' => 'required|string',
            'presenta_maniobra' => 'sometimes|in:1',
            'equipo_sujecion' => 'required|string',
            'foto_unidad' => 'required|image|max:5120', // 5MB max
            'foto_llantas' => 'required|image|max:5120',
        ]);

        DB::transaction(function () use ($validated, $guia, $request) {
            $fotoUnidadPath = $request->file('foto_unidad')->store('audit_patio_unidad', 's3');
            $fotoLlantasPath = $request->file('foto_llantas')->store('audit_patio_llantas', 's3');

            $guia->update([
                'operador' => $validated['operador'],
                'placas' => $validated['placas'],
                'audit_patio_arribo' => $validated['arribo_fecha'] . ' ' . $validated['arribo_hora'],
                'audit_patio_caja_estado' => $validated['caja_estado'],
                'audit_patio_llantas_estado' => $validated['llantas_estado'],
                'audit_patio_combustible_nivel' => $validated['combustible_nivel'],
                'audit_patio_presenta_maniobra' => $request->has('presenta_maniobra'),
                'audit_patio_equipo_sujecion' => $validated['equipo_sujecion'],
                'audit_patio_fotos' => json_encode([
                    'unidad' => $fotoUnidadPath,
                    'llantas' => $fotoLlantasPath,
                ]),
                'estatus' => 'En Cortina',
            ]);

            $order = $guia->plannings->first()->order;
            if ($order) {
                CsOrderEvent::create([
                    'cs_order_id' => $order->id,
                    'user_id' => Auth::id(),
                    'description' => 'Auditoría de patio completada por ' . Auth::user()->name . ' para la guía ' . $guia->guia . '. Unidad en cortina.'
                ]);
            }

            Mail::to(['auditoria@tuempresa.com', 'trafico@tuempresa.com'])->send(new UnidadArriboMail($guia));
        });

        return redirect()->route('audit.index')->with('success', 'Auditoría de patio completada para Guía: ' . $guia->guia);
    }

    /**
     * Muestra el formulario para la tercera auditoría (Carga de Unidad).
     */
    public function showLoadingAudit($guiaId)
    {
        $guia = Guia::with('plannings.order.customer', 'facturas')->findOrFail($guiaId);

        // Creamos una estructura para agrupar especificaciones por SO/Factura
        $requirementsByOrder = [];

        foreach ($guia->plannings as $planning) {
            $order = $planning->order;
            if ($order && $order->customer && !empty($order->customer->delivery_specifications)) {
                
                // Usamos el SO o la factura como identificador
                $identifier = $order->so_number ?? $order->invoice_number;
                
                // Obtenemos las especificaciones del cliente
                $specs = $order->customer->delivery_specifications;
                
                // Inicializamos si no existe
                if (!isset($requirementsByOrder[$identifier])) {
                    $requirementsByOrder[$identifier] = [
                        'entrega' => [],
                        'documentacion' => []
                    ];
                }
                
                // Llenamos las listas con las especificaciones que están marcadas como true
                foreach ($specs as $specName => $isRequired) {
                    if ($isRequired) {
                        // Aquí necesitarás una lógica para saber si es de 'Entrega' o 'Documentación'
                        // Por ahora, asumimos que lo podemos determinar por el nombre
                        if (in_array($specName, ['REVISION DE UPC VS FACTURA - Entrega', /* ...otros de entrega... */])) {
                            $requirementsByOrder[$identifier]['entrega'][] = $specName;
                        } else {
                            $requirementsByOrder[$identifier]['documentacion'][] = $specName;
                        }
                    }
                }
            }
        }

        return view('audit.loading', compact('guia', 'requirementsByOrder'));
    }

    /**
     * Guarda los datos de la auditoría de carga.
     */
    public function storeLoadingAudit(Request $request, $guiaId)
    {
        $guia = Guia::findOrFail($guiaId);
        $validated = $request->validate([
            'foto_caja_vacia' => 'required|image|max:5120', // 5MB
            'fotos_carga' => 'required|array|min:3',
            'fotos_carga.*' => 'image|max:5120', // 5MB
            'marchamo_numero' => 'nullable|string|max:255',
            'foto_marchamo' => 'nullable|image|max:5120', // 5MB
            'lleva_custodia' => 'required|boolean',
            'incidencias' => 'nullable|array',
            'incidencias.*' => 'string',
            'validated_specs' => 'nullable|array', 
        ]);

        DB::transaction(function () use ($validated, $guia, $request) {
            
            // --- INICIA LÓGICA PARA GUARDAR ARCHIVOS ---

            $fotoCajaVaciaPath = $request->file('foto_caja_vacia')->store('audit_carga/caja_vacia', 's3');
            
            $fotosCargaPaths = [];
            if ($request->hasFile('fotos_carga')) {
                foreach ($request->file('fotos_carga') as $foto) {
                    $path = $foto->store('audit_carga/proceso', 's3');
                    $fotosCargaPaths[] = $path;
                }
            }

            $fotoMarchamoPath = null;
            if ($request->hasFile('foto_marchamo')) {
                $fotoMarchamoPath = $request->file('foto_marchamo')->store('audit_carga/marchamo', 's3');
            }

            // --- TERMINA LÓGICA PARA GUARDAR ARCHIVOS ---

            if ($request->has('incidencias') && !empty($validated['incidencias'])) {
                foreach($validated['incidencias'] as $incidencia) {
                    // Asegurarse que la incidencia no esté vacía
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

            // Actualiza la guía con la información y las rutas de las fotos
            $guia->update([
                'marchamo_numero' => $validated['marchamo_numero'],
                'lleva_custodia' => $validated['lleva_custodia'],
                'audit_carga_fotos' => json_encode([
                    'caja_vacia' => $fotoCajaVaciaPath,
                    'proceso_carga' => $fotosCargaPaths,
                    'marchamo' => $fotoMarchamoPath,
                    'validated_specifications' => $validatedSpecs 
                ]),
                'estatus' => 'En Tránsito', // Cambia el estatus de la guía
            ]);

            // Registra el evento en la orden de venta principal
            $order = $guia->plannings->first()->order;
            if ($order) {
                CsOrderEvent::create([
                    'cs_order_id' => $order->id, 
                    'user_id' => Auth::id(),
                    'description' => 'Auditoría de carga completada por ' . Auth::user()->name . ' para la guía ' . $guia->guia . '. Unidad en tránsito.'
                ]);
            }
            
            // TODO: Si se modifica, enviar correo a Tráfico.
        });

        return redirect()->route('audit.index')->with('success', 'Auditoría de carga completada para Guía: ' . $guia->guia);
    }
}