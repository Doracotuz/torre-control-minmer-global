<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\CsOrder;
use App\Models\CsOrderEvent;
use App\Models\Guia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\UnidadArriboMail;
use App\Models\AuditIncidencia;
use Illuminate\Support\Facades\Storage;
use App\Mail\AuditReabiertaMail;
use App\Models\CsPlanning;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $activeQuery = Audit::query()
            ->where('status', '!=', 'Finalizada')
            ->with(['guia.facturas', 'order.details']);

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $activeQuery->where(function ($q) use ($searchTerm) {
                $q->whereHas('order', fn($oq) => $oq->where('so_number', 'like', $searchTerm)
                                                    ->orWhere('invoice_number', 'like', 'searchTerm'))
                    ->orWhere('location', 'like', $searchTerm)
                    ->orWhereHas('guia', fn($gq) => $gq->where('guia', 'like', $searchTerm));
            });
        }
        if ($request->filled('status') && is_array($request->status)) {
            $activeQuery->whereIn('status', $request->status);
        }
        if ($request->filled('location')) {
            $activeQuery->where('location', $request->location);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $activeQuery->whereHas('order', function($q) use ($request) {
                $q->whereBetween('creation_date', [$request->start_date, $request->end_date]);
            });
        }
        $audits = $activeQuery->orderBy('created_at', 'desc')->paginate(15, ['*'], 'page');

        $completedGuidesQuery = Guia::query()
            ->whereDoesntHave('audits', function ($query) {
                $query->where('status', '!=', 'Finalizada');
            })
            ->whereHas('audits')
            ->with('audits.order');

        if ($request->filled('search_completed')) {
            $searchTerm = '%' . $request->search_completed . '%';
            $completedGuidesQuery->where(function($q) use ($searchTerm) {
                $q->where('guia', 'like', $searchTerm)
                    ->orWhereHas('audits.order', fn($oq) => $oq->where('so_number', 'like', $searchTerm));
            });
        }
        $completedGuides = $completedGuidesQuery->orderBy('updated_at', 'desc')->paginate(10, ['*'], 'completedPage');
        $baseQuery = Audit::where('status', '!=', 'Finalizada');
        $auditStatuses = (clone $baseQuery)->whereNotNull('status')->distinct()->orderByRaw("FIELD(status, 'Pendiente Almacén', 'Pendiente Patio', 'Pendiente Carga')")->pluck('status');
        $locations = (clone $baseQuery)->whereNotNull('location')->distinct()->pluck('location');
        
        return view('audit.index', compact('audits', 'completedGuides', 'auditStatuses', 'locations'));
    }

    public function showWarehouseAudit(Audit $audit)
    {
        $audit->load('order.details.product', 'order.details.upc');
        return view('audit.warehouse', compact('audit'));
    }

    public function storeWarehouseAudit(Request $request, Audit $audit)
    {
        $validatedData = $request->validate([
            'observaciones' => 'nullable|string',
            'items' => 'required|array',
            'items.*.calidad' => 'required|string',
            'items.*.sku_validado' => 'sometimes|boolean',
            'items.*.piezas_validadas' => 'sometimes|boolean',
            'items.*.upc_validado' => 'sometimes|boolean',
        ]);
        
        $audit->update([
            'warehouse_audit_data' => $validatedData,
            'status' => 'Pendiente Patio',
            'user_id' => Auth::id()
        ]);
        
        CsOrderEvent::create(['cs_order_id' => $audit->cs_order_id, 'user_id' => Auth::id(), 'description' => 'Auditoría de almacén completada en ' . $audit->location]);
        
        return redirect()->route('audit.index')->with('success', 'Auditoría de almacén completada.');
    }

    public function showPatioAudit(Audit $audit)
    {
        $audit->load('order', 'guia.plannings.order.audits');
        $guia = $audit->guia;

        if (!$guia) {
            return redirect()->route('audit.index')->with('error', 'Esta auditoría aún no tiene una guía asignada.');
        }

        $pendingOrders = $guia->plannings->map(fn($p) => $p->order)->filter(function($order) use ($audit) {
            if (!$order) return false;
            $auditForLocation = $order->audits->where('location', $audit->location)->first();
            return !$auditForLocation || $auditForLocation->status !== 'Pendiente Patio';
        });

        if ($pendingOrders->isNotEmpty()) {
            $pendingSoNumbers = $pendingOrders->pluck('so_number')->join(', ');
            return redirect()->route('audit.index')->with('error', 'Aún no todas las órdenes de esta guía han completado la auditoría de almacén. Faltan: SO ' . $pendingSoNumbers);
        }

        return view('audit.patio', compact('audit', 'guia'));
    }

    public function storePatioAudit(Request $request, Audit $audit)
    {
        $guia = $audit->guia;
        if (!$guia) {
            return redirect()->route('audit.index')->with('error', 'La guía no fue encontrada.');
        }

        $validatedData = $request->validate([
            'operador' => 'required|string|max:255',
            'placas' => 'required|string|max:255',
            'arribo_fecha' => 'required|date',
            'arribo_hora' => 'required|date_format:H:i',
            'caja_estado' => 'required|string',
            'llantas_estado' => 'required|string',
            'combustible_nivel' => 'required|string',
            'equipo_sujecion' => 'required|string',
            'presenta_maniobra' => 'sometimes|boolean',
            'maniobra_personas' => 'required_if:presenta_maniobra,1|nullable|integer|min:1',
            'foto_unidad' => 'required|image|max:100120',
            'foto_llantas' => 'required|image|max:100120',
        ]);

        DB::transaction(function () use ($validatedData, $guia, $request, $audit) {
            $fotoUnidadPath = $request->file('foto_unidad')->store('audit_patio_unidad', 's3');
            $fotoLlantasPath = $request->file('foto_llantas')->store('audit_patio_llantas', 's3');
            
            $patioAuditData = array_merge($validatedData, [
                'presenta_maniobra' => $request->has('presenta_maniobra'),
                'maniobra_personas' => $request->input('maniobra_personas'),
                'arribo_completo' => $validatedData['arribo_fecha'] . ' ' . $validatedData['arribo_hora'],
                'foto_unidad_path' => $fotoUnidadPath,
                'foto_llantas_path' => $fotoLlantasPath
            ]);

            $guia->update([
                'operador' => $validatedData['operador'],
                'placas' => $validatedData['placas'],
            ]);
            
            $orderIds = $guia->plannings->pluck('cs_order_id');
            Audit::whereIn('cs_order_id', $orderIds)
                ->where('location', $audit->location)
                ->where('status', 'Pendiente Patio')
                ->update([
                    'patio_audit_data' => $patioAuditData,
                    'status' => 'Pendiente Carga',
                    'user_id' => Auth::id()
                ]);
            
            $areaNames = ['Auditoría', 'Tráfico'];
            $recipients = User::whereHas('area', fn($q) => $q->whereIn('name', $areaNames))->get();
            if ($recipients->isNotEmpty()) {
                Mail::to($recipients->pluck('email')->all())->send(new UnidadArriboMail($guia));
            }
        });

        CsOrderEvent::create(['cs_order_id' => $audit->cs_order_id, 'user_id' => Auth::id(), 'description' => 'Auditoría de patio completada en ' . $audit->location]);
        
        return redirect()->route('audit.index')->with('success', 'Auditoría de patio completada.');
    }

    public function showLoadingAudit(Audit $audit)
    {
        $audit->load('order.customer', 'guia.plannings.order.customer', 'guia.facturas');
        $guia = $audit->guia;

        if (!$guia) {
            return redirect()->route('audit.index')->with('error', 'Esta auditoría aún no tiene una guía asignada.');
        }

        $ordersInGuia = $guia->plannings->map(fn($p) => $p->order)->filter();
        $pendingOrders = $ordersInGuia->filter(function($order) use ($audit) {
            $auditForLocation = $order->audits->where('location', $audit->location)->first();
            return !$auditForLocation || $auditForLocation->status !== 'Pendiente Carga';
        });

        if ($pendingOrders->isNotEmpty()) {
            $pendingSoNumbers = $pendingOrders->pluck('so_number')->join(', ');
            return redirect()->route('audit.index')->with('error', 'Aún no todas las órdenes de esta guía han completado la auditoría de patio. Faltan: SO ' . $pendingSoNumbers);
        }

        $requirementsByCustomer = [];
        foreach ($ordersInGuia as $order) {
            if ($order && $order->customer && !empty($order->customer->delivery_specifications)) {
                $customerName = $order->customer->name;
                $identifier = $order->so_number ?? $order->invoice_number;
                if (!isset($requirementsByCustomer[$customerName])) {
                    $specs = $order->customer->delivery_specifications;
                    $requirementsByCustomer[$customerName] = ['entrega' => [], 'documentacion' => [], 'orders' => []];
                    foreach ($specs as $specName => $isRequired) {
                        if ($isRequired) {
                            if (str_contains($specName, 'Entrega')) $requirementsByCustomer[$customerName]['entrega'][] = $specName;
                            else $requirementsByCustomer[$customerName]['documentacion'][] = $specName;
                        }
                    }
                }
                $requirementsByCustomer[$customerName]['orders'][] = $identifier;
            }
        }
        
        return view('audit.loading', compact('audit', 'guia', 'requirementsByCustomer'));
    }
    
    public function storeLoadingAudit(Request $request, Audit $audit)
    {
        $guia = $audit->guia;
        if (!$guia) {
            return redirect()->route('audit.index')->with('error', 'La guía no fue encontrada.');
        }
        
        $validatedData = $request->validate([
            'foto_caja_vacia' => 'required|image|max:100120',
            'fotos_carga' => 'required|array|min:3',
            'fotos_carga.*' => 'image|max:100120',
            'marchamo_numero' => 'nullable|string|max:255',
            'foto_marchamo' => 'nullable|image|max:100120',
            'lleva_custodia' => 'required|boolean',
            'incidencias' => 'nullable|array',
            'incidencias.*' => 'string',
            'validated_specs' => 'nullable|array',
            'incluye_tarimas' => 'sometimes|boolean',
            'tarimas_tipo' => 'required_if:incluye_tarimas,1|in:Chep,Estándar,Ambas,Tarima Liverpool',
            'tarimas_cantidad_chep' => 'required_if:tarimas_tipo,Chep|required_if:tarimas_tipo,Ambas|nullable|integer|min:0',
            'tarimas_cantidad_estandar' => 'required_if:tarimas_tipo,Estándar|required_if:tarimas_tipo,Ambas|nullable|integer|min:0',
            'tarimas_cantidad_liverpool' => 'required_if:tarimas_tipo,Tarima Liverpool|nullable|integer|min:0',
        ]);
        
        DB::transaction(function () use ($validatedData, $guia, $request, $audit) {
            $fotoCajaVaciaPath = $request->file('foto_caja_vacia')->store('audit_carga/caja_vacia', 's3');
            $fotosCargaPaths = [];
            if ($request->hasFile('fotos_carga')) {
                foreach ($request->file('fotos_carga') as $foto) { $fotosCargaPaths[] = $foto->store('audit_carga/proceso', 's3'); }
            }
            $fotoMarchamoPath = null;
            if ($request->hasFile('foto_marchamo')) {
                $fotoMarchamoPath = $request->file('foto_marchamo')->store('audit_carga/marchamo', 's3');
            }

            if ($request->has('incidencias')) {
                foreach($validatedData['incidencias'] as $incidencia) {
                    if(!empty($incidencia)) {
                        \App\Models\AuditIncidencia::create(['guia_id' => $guia->id, 'user_id' => Auth::id(), 'tipo_incidencia' => $incidencia]);
                    }
                }
            }

            $loadingAuditData = array_merge($validatedData, [
                'audit_carga_fotos' => [
                    'caja_vacia' => $fotoCajaVaciaPath,
                    'proceso_carga' => $fotosCargaPaths,
                    'marchamo' => $fotoMarchamoPath,
                ]
            ]);
            
            $guia->update([
                'marchamo_numero' => $validatedData['marchamo_numero'],
                'lleva_custodia' => $validatedData['lleva_custodia'],
            ]);
            
            $orderIds = $guia->plannings->pluck('cs_order_id');
            Audit::whereIn('cs_order_id', $orderIds)
                ->where('location', $audit->location)
                ->where('status', 'Pendiente Carga')
                ->update([
                    'loading_audit_data' => json_encode($loadingAuditData),
                    'status' => 'Finalizada',
                    'completed_at' => now(),
                    'user_id' => Auth::id()
                ]);
        });

        CsOrderEvent::create(['cs_order_id' => $audit->cs_order_id, 'user_id' => Auth::id(), 'description' => 'Auditoría de carga finalizada en ' . $audit->location]);
        
        return redirect()->route('audit.index')->with('success', 'Auditoría de carga completada exitosamente.');
    }

    public function showCargaPlan(Request $request)
    {
        $selectedDate = $request->input('fecha', now()->format('Y-m-d'));
        $selectedOrigen = $request->input('origen');

        $origenes = CsPlanning::whereDate('fecha_carga', $selectedDate)
            ->whereNotNull('origen')
            ->distinct()
            ->pluck('origen');

        $query = Guia::whereDate('fecha_asignacion', $selectedDate)
            ->where('estatus', '!=', 'Cancelado')
            ->with([
                'plannings.order.customer',
                'plannings.order.audits',
            ]);

        if ($selectedOrigen) {
            $query->whereHas('plannings', function ($q) use ($selectedOrigen) {
                $q->where('origen', $selectedOrigen);
            });
        }
        
        $guias = $query->orderBy('hora_planeada', 'asc')->get();

        $displayItems = collect();
        foreach ($guias as $guia) {
            $hasPendingWarehouse = $guia->plannings->some(function ($planning) {
                return $planning->order && $planning->order->audits->where('status', 'Pendiente Almacén')->isNotEmpty();
            });

            if ($hasPendingWarehouse) {
                foreach ($guia->plannings as $planning) {
                    if ($planning->order) {
                        $displayItems->push((object)[
                            'type' => 'individual',
                            'planning' => $planning,
                            'guia' => $guia
                        ]);
                    }
                }
            } else {
                if ($guia->plannings->isNotEmpty()) {
                    $displayItems->push((object)[
                        'type' => 'group',
                        'guia' => $guia
                    ]);
                }
            }
        }

        $allPlannings = $guias->flatMap->plannings;
        $stats = [
            'totalGuias' => $guias->count(),
            'totalDocumentos' => $allPlannings->pluck('cs_order_id')->unique()->count(),
            'totalCajas' => $allPlannings->sum('cajas'),
            'totalPiezas' => $allPlannings->sum('pzs'),
        ];

        return view('audit.plan-de-carga', compact('displayItems', 'selectedDate', 'origenes', 'selectedOrigen', 'stats'));
    }

    public function reopenAudit(Guia $guia)
    {

        $orderIds = $guia->plannings->pluck('cs_order_id');
        
        $auditsToReopen = Audit::whereIn('cs_order_id', $orderIds)
                            ->where('status', 'Finalizada')
                            ->get();

        if ($auditsToReopen->isEmpty()) {
            return redirect()->route('audit.index')
                            ->with('error', 'No se encontraron auditorías finalizadas para reabrir.');
        }

        $pathsToDelete = [];
        foreach($auditsToReopen as $audit) {
            $patioData = $audit->patio_audit_data ?? [];
            if (!empty($patioData['foto_unidad_path'])) $pathsToDelete[] = $patioData['foto_unidad_path'];
            if (!empty($patioData['foto_llantas_path'])) $pathsToDelete[] = $patioData['foto_llantas_path'];

            $loadingData = $audit->loading_audit_data ?? [];
            $cargaFotos = $loadingData['audit_carga_fotos'] ?? [];
            if (!empty($cargaFotos['caja_vacia'])) $pathsToDelete[] = $cargaFotos['caja_vacia'];
            if (!empty($cargaFotos['marchamo'])) $pathsToDelete[] = $cargaFotos['marchamo'];
            if (!empty($cargaFotos['proceso_carga'])) $pathsToDelete = array_merge($pathsToDelete, $cargaFotos['proceso_carga']);
        }

        if (!empty($pathsToDelete)) {
            Storage::disk('s3')->delete($pathsToDelete);
        }

        $userWhoReopened = Auth::user();
        foreach($auditsToReopen as $audit) {
            $audit->update([
                'status' => 'Pendiente Almacén', 
                'completed_at' => null,
                'warehouse_audit_data' => null,
                'patio_audit_data' => null,
                'loading_audit_data' => null,
                'user_id' => null
            ]);

            CsOrderEvent::create([
                'cs_order_id' => $audit->cs_order_id, 
                'user_id' => $userWhoReopened->id, 
                'description' => 'Auditoría reabierta por ' . $userWhoReopened->name . '. Evidencias anteriores eliminadas.'
            ]);
        }

        $areaNames = ['Auditoría', 'Tráfico'];
        $recipients = User::whereHas('area', fn($q) => $q->whereIn('name', $areaNames))->get();
        if ($recipients->isNotEmpty()) {
            Mail::to($recipients->pluck('email')->all())->send(new AuditReabiertaMail($guia, $userWhoReopened));
        }

        return redirect()->route('audit.index')->with('success', 'La auditoría para la guía ' . $guia->guia . ' ha sido reabierta.');
    }
}