<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WMS\ValueAddedServiceAssignment;
use App\Models\WMS\Pallet;
use App\Models\Warehouse;
use App\Models\Area;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WMSBillingReportController extends Controller
{
    // Placeholder rate for storage calculation (could be moved to DB later)
    const DAILY_PALLET_RATE = 15.00;

    public function index(Request $request)
    {
        $warehouses = Warehouse::orderBy('name')->get();
        $clients = Area::orderBy('name')->get();

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        $kpis = $this->calculateBillingMetrics($request, $startDate, $endDate);

        return view('wms.reports.billing.index', compact('warehouses', 'clients', 'kpis', 'startDate', 'endDate'));
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $kpis = $this->calculateBillingMetrics($request, $startDate, $endDate);
        
        // Add context for PDF
        $kpis['filters'] = [
            'start_date' => Carbon::parse($startDate)->format('d/m/Y'),
            'end_date' => Carbon::parse($endDate)->format('d/m/Y'),
            'warehouse' => $request->warehouse_id ? Warehouse::find($request->warehouse_id)->name : 'Todos',
            'client' => $request->area_id ? Area::find($request->area_id)->name : 'Todos',
        ];

        $pdf = Pdf::loadView('wms.reports.billing.pdf', compact('kpis'));
        return $pdf->stream('reporte_facturacion.pdf');
    }

    public function exportCsv(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $warehouseId = $request->warehouse_id;
        $areaId = $request->area_id;

        $fileName = 'detalle_facturacion_' . $startDate . '_al_' . $endDate . '.csv';

        $callback = function() use ($startDate, $endDate, $warehouseId, $areaId) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM

            // --- Section 1: Value Added Services ---
            fputcsv($file, ['DETALLE DE SERVICIOS DE VALOR AGREGADO']);
            fputcsv($file, ['Fecha', 'Folio Origen', 'Cliente', 'Almacen', 'Servicio', 'Cantidad', 'Costo Unit.', 'Total']);

            $servicesQuery = ValueAddedServiceAssignment::with(['service', 'assignable'])
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

            if ($warehouseId || $areaId) {
                // Determine context to filter (this covers POs, SOs, SRs)
                // This is complex for polymorphic, filtering generically here implies loading
                // For performance in CSV, acceptable to filter in PHP or complex joins.
                // Doing simpler check here:
            }
            
            // For now, simpler retrieval
            $services = $servicesQuery->get();
            
            foreach ($services as $svc) {
                // Filter logic (manual for polymorphic simplicity in this snippet)
                $assignmentContext = $this->resolveAssignmentContext($svc);
                if ($warehouseId && $assignmentContext['warehouse_id'] != $warehouseId) continue;
                if ($areaId && $assignmentContext['area_id'] != $areaId) continue;

                fputcsv($file, [
                    $svc->created_at->format('Y-m-d'),
                    $assignmentContext['folio'],
                    $assignmentContext['area_name'],
                    $assignmentContext['warehouse_name'],
                    $svc->service->description,
                    $svc->quantity,
                    $svc->cost_snapshot,
                    $svc->quantity * $svc->cost_snapshot
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['ESTIMACION DE ALMACENAJE']);
            fputcsv($file, ['Pallet LPN', 'Cliente', 'Almacen', 'Fecha Entrada', 'Fecha Salida', 'Dias en Periodo', 'Tarifa Dia', 'Total']);
            
            // Storage Calculation Logic for CSV
            $pallets = Pallet::with(['purchaseOrder.area', 'location.warehouse'])
                ->where('created_at', '<=', $endDate . ' 23:59:59')
                ->get(); // Optimized chunking recommended for prod

            foreach($pallets as $p) {
                // Logic duplicates metrics calculation, could be refactored
                $arrival = $p->created_at;
                // Assuming we track departure on pallet soft delete or separate log. 
                // Using NOW() if not deleted? Or deleted_at if using SoftDeletes
                $departure = $p->deleted_at ?? Carbon::now();
                if ($arrival > $departure) continue; // Sanity check

                // Check overlap with period
                $periodStart = Carbon::parse($startDate);
                $periodEnd = Carbon::parse($endDate)->endOfDay();

                $calcStart = $arrival->max($periodStart);
                $calcEnd = $departure->min($periodEnd);

                if ($calcStart <= $calcEnd) {
                    $days = $calcStart->diffInDays($calcEnd) + 1;
                    if ($days < 0) $days = 0;

                     // Filter check
                    $pWarehouseId = $p->location->warehouse_id ?? ($p->purchaseOrder->warehouse_id ?? 0); // specific logic might vary
                    $pAreaId = $p->purchaseOrder->area_id ?? 0;

                    if ($warehouseId && $pWarehouseId != $warehouseId) continue;
                    if ($areaId && $pAreaId != $areaId) continue;

                    fputcsv($file, [
                        $p->lpn,
                        $p->purchaseOrder->area->name ?? 'N/A',
                        $p->location->warehouse->name ?? 'N/A',
                        $arrival->format('Y-m-d'),
                        $p->deleted_at ? $p->deleted_at->format('Y-m-d') : 'Presente',
                        $days,
                        $p->purchaseOrder && $p->purchaseOrder->area && $p->purchaseOrder->area->storage_rate ? $p->purchaseOrder->area->storage_rate : self::DAILY_PALLET_RATE,
                        $days * ($p->purchaseOrder && $p->purchaseOrder->area && $p->purchaseOrder->area->storage_rate ? $p->purchaseOrder->area->storage_rate : self::DAILY_PALLET_RATE)
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }

    private function calculateBillingMetrics(Request $request, $startDate, $endDate)
    {
        $warehouseId = $request->warehouse_id;
        $areaId = $request->area_id;
        $periodStart = Carbon::parse($startDate);
        $periodEnd = Carbon::parse($endDate)->endOfDay();

        // --- 1. Value Added Services ---
        $vasQuery = ValueAddedServiceAssignment::with(['service', 'assignable'])
            ->whereBetween('created_at', [$periodStart, $periodEnd]);
        
        $allVas = $vasQuery->get();
        // Post-filter for polymorphic relationships complexity
        $filteredVas = $allVas->filter(function($svc) use ($warehouseId, $areaId) {
            $ctx = $this->resolveAssignmentContext($svc);
            if ($warehouseId && isset($ctx['warehouse_id']) && $ctx['warehouse_id'] != $warehouseId) return false;
            if ($areaId && isset($ctx['area_id']) && $ctx['area_id'] != $areaId) return false;
            return true;
        });

        $totalVasCost = $filteredVas->sum(fn($i) => $i->quantity * $i->cost_snapshot);
        $vasByService = $filteredVas->groupBy('service.description')
            ->map(fn($group) => $group->sum(fn($i) => $i->quantity * $i->cost_snapshot))
            ->sortDesc()
            ->take(5);

        // --- 2. Storage Estimation ---
        // Logic: Find pallets that existed during the range
        // Optimization: In real DB, use specialized query. Here, Eloquent loop for simplicity on "small" dataset assumption or prototype
        $palletsQuery = Pallet::with(['purchaseOrder.area', 'location.warehouse', 'items'])
            ->withTrashed() // Include deleted pallets if they were here during the period
            ->where('created_at', '<=', $endDate . ' 23:59:59'); // Created before end of period
        
        // Improve query efficiency
         if ($areaId) {
            $palletsQuery->whereHas('purchaseOrder', fn($q) => $q->where('area_id', $areaId));
        }
        if ($warehouseId) {
             $palletsQuery->whereHas('location', fn($q) => $q->where('warehouse_id', $warehouseId));
        }

        $pallets = $palletsQuery->get();
        $totalStorageCost = 0;
        $activePalletsCount = 0;

        foreach($pallets as $p) {
             // Exclude pallets with 0 items (historical 0 logic approx: if currently 0 and not deleted, it's effectively empty space usage? 
             // User requested "no considerar pallets en 0". 
             // Logic: If pallet has no items, skip. 
             if ($p->items->sum('quantity') <= 0) continue;

             $arrival = $p->created_at;
             $departure = $p->deleted_at ?? Carbon::now();
             
             // If deleted before period start, ignore
             if ($departure < $periodStart) continue;

             $calcStart = $arrival->max($periodStart);
             $calcEnd = $departure->min($periodEnd);

             if ($calcStart <= $calcEnd) {
                 $days = $calcStart->diffInDays($calcEnd);
                 // Correction: same day counts as 1? or 24h? Usually warehousing bills by 'night' or 'day presence'.
                 // Let's assume inclusive day count.
                 $days += 1; 

                 // Get rate from Area or default
                 $rate = $p->purchaseOrder && $p->purchaseOrder->area ? $p->purchaseOrder->area->storage_rate : self::DAILY_PALLET_RATE;
                 // Allow fallback to constant if null in DB
                 if (!$rate) $rate = self::DAILY_PALLET_RATE; 

                 $totalStorageCost += ($days * $rate);
                 $activePalletsCount++;
             }
        }

        // --- 3. Additional Metrics ---
        // Inbound POs (Received)
        $inboundPosQuery = \App\Models\WMS\PurchaseOrder::where('status', 'Completed')
            ->whereBetween('updated_at', [$periodStart, $periodEnd]); // approx completion time
        if ($warehouseId) $inboundPosQuery->where('warehouse_id', $warehouseId);
        if ($areaId) $inboundPosQuery->where('area_id', $areaId);
        $inboundPosCount = $inboundPosQuery->count();

        // Outbound SOs (Shipped)
        $outboundSosQuery = \App\Models\WMS\SalesOrder::whereIn('status', ['Packed', 'Shipped'])
            ->whereBetween('updated_at', [$periodStart, $periodEnd]);
        if ($warehouseId) $outboundSosQuery->where('warehouse_id', $warehouseId);
        if ($areaId) $outboundSosQuery->where('area_id', $areaId);
        $outboundSosCount = $outboundSosQuery->count();

        // Shipped Pieces/Cases
        // Query SalesOrderLines for shipped orders
        $shippedLinesQuery = \App\Models\WMS\SalesOrderLine::whereHas('salesOrder', function($q) use ($periodStart, $periodEnd, $warehouseId, $areaId) {
            $q->whereIn('status', ['Packed', 'Shipped']) // Only shipped orders
              ->whereBetween('updated_at', [$periodStart, $periodEnd]);
            if ($warehouseId) $q->where('warehouse_id', $warehouseId);
            if ($areaId) $q->where('area_id', $areaId);
        })->with('product');

        $shippedLines = $shippedLinesQuery->get();
        $shippedPieces = $shippedLines->sum('quantity');
        $shippedCases = $shippedLines->sum(function($line) {
            $piecesPerCase = $line->product->pieces_per_case ?? 1;
            return $piecesPerCase > 0 ? $line->quantity / $piecesPerCase : $line->quantity;
        });

        // --- 4. Advanced Chart Data (V4 Financial Style) ---
        
        // A. Cost Evolution (Stacked Area): Storage vs VAS Daily
        // B. Logistics Activity (Multi-Bar): POs vs SOs Daily
        // C. Shipped Volume (Line): Pieces Daily
        
        $dates = [];
        $period = \Carbon\CarbonPeriod::create($periodStart, $periodEnd);
        $dailyData = [];
        
        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $dailyData[$formattedDate] = [
                'date' => $formattedDate,
                'vas' => 0,
                'storage' => 0,
                'pos' => 0, // Inbound POs
                'sos' => 0, // Outbound SOs
                'pieces' => 0 // Shipped Pieces
            ];
            $dates[] = $formattedDate;
        }

        // Fill VAS Daily
        foreach ($filteredVas as $vas) {
            $d = $vas->created_at->format('Y-m-d');
            if (isset($dailyData[$d])) {
                $dailyData[$d]['vas'] += ($vas->quantity * $vas->cost_snapshot);
            }
        }

        // Fill Storage Daily
        foreach($pallets as $p) {
             if ($p->items->sum('quantity') <= 0) continue;
             $arrival = $p->created_at;
             $departure = $p->deleted_at ?? Carbon::now();
             if ($departure < $periodStart) continue;

             $calcStart = $arrival->max($periodStart);
             $calcEnd = $departure->min($periodEnd);

             if ($calcStart <= $calcEnd) {
                 $curr = clone $calcStart;
                 while ($curr <= $calcEnd) {
                     $d = $curr->format('Y-m-d');
                     if (isset($dailyData[$d])) {
                         $rate = $p->purchaseOrder && $p->purchaseOrder->area && $p->purchaseOrder->area->storage_rate 
                                 ? $p->purchaseOrder->area->storage_rate 
                                 : self::DAILY_PALLET_RATE;
                         $dailyData[$d]['storage'] += $rate;
                     }
                     $curr->addDay();
                 }
             }
        }
        
        // Fill Logistics Activity Daily (POs, SOs, Pieces)
        // Optimization: Run single agg query for chart if needed, but loop is okay for now
        $inboundPosQuery->get()->groupBy(function($item) {
            return $item->updated_at->format('Y-m-d');
        })->each(function($group, $date) use (&$dailyData) {
            if(isset($dailyData[$date])) $dailyData[$date]['pos'] = $group->count();
        });

        $outboundSosQuery->get()->groupBy(function($item) {
             return $item->updated_at->format('Y-m-d');
        })->each(function($group, $date) use (&$dailyData) {
            if(isset($dailyData[$date])) $dailyData[$date]['sos'] = $group->count();
        });

        $shippedLines->groupBy(function($line) {
            return $line->salesOrder->updated_at->format('Y-m-d');
        })->each(function($group, $date) use (&$dailyData) {
             if(isset($dailyData[$date])) $dailyData[$date]['pieces'] = $group->sum('quantity');
        });

        // Prepare Series for ApexCharts
        $labels = array_keys($dailyData);
        $chartDaily = [
            'labels' => $labels,
            'storage' => array_column($dailyData, 'storage'),
            'vas' => array_column($dailyData, 'vas'),
            'pos' => array_column($dailyData, 'pos'),
            'sos' => array_column($dailyData, 'sos'),
            'pieces' => array_column($dailyData, 'pieces'),
        ];

        // B. Services Analysis
        // Grouping by Service Description
        $serviceDist = $filteredVas->groupBy('service.description')
            ->map(function($group) {
                return [
                    'count' => $group->sum('quantity'),
                    'cost' => $group->sum(fn($i) => $i->quantity * $i->cost_snapshot)
                ];
            })
            ->sortByDesc('cost');
        
        $chartService = [
            'labels' => $serviceDist->keys()->toArray(),
            'counts' => $serviceDist->pluck('count')->toArray(),
            'costs' => $serviceDist->pluck('cost')->toArray(),
        ];
        
        // Logo Processing
        $logoBase64 = '';
        $logoPath = public_path('images/LogoAzul.png');
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $data = file_get_contents($logoPath);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        return [
            'total_vas' => (float)$totalVasCost,
            'total_storage' => (float)$totalStorageCost,
            'grand_total' => (float)($totalVasCost + $totalStorageCost),
            'active_pallets' => $activePalletsCount,
            'vas_breakdown' => $vasByService,
            'metrics_count' => $filteredVas->count(),
            'chart_vas_vs_storage' => [(float)$totalVasCost, (float)$totalStorageCost], // Donut Data
            'chart_daily' => $chartDaily, // Daily Trends (Area, Bar, Line)
            'chart_services' => $chartService, // Radar/Polar/Bar Data
            'inbound_pos' => $inboundPosCount,
            'outbound_sos' => $outboundSosCount,
            'shipped_pieces' => $shippedPieces,
            'shipped_cases' => round($shippedCases, 1),
            'logo_base64' => $logoBase64,
        ];
    }

    private function resolveAssignmentContext($assignment)
    {
        // Helper to get warehouse and area from polymorphic parents
        $ctx = ['warehouse_id' => null, 'area_id' => null, 'folio' => 'N/A', 'area_name' => 'N/A', 'warehouse_name' => 'N/A'];
        
        $related = $assignment->assignable;
        if (!$related) return $ctx;

        if ($assignment->assignable_type === \App\Models\WMS\PurchaseOrder::class) { // Using string map in controller is safer but class check works if model loaded
             $ctx['warehouse_id'] = $related->warehouse_id;
             // Looking at PO model, it has warehouse_id? No, usually location based.
             // But for billing, we assume where it was received? 
             // Simplification: context from PO content or generic
             $ctx['area_id'] = $related->area_id;
             $ctx['folio'] = $related->po_number;
             $ctx['area_name'] = $related->area->name ?? 'N/A';
             $ctx['warehouse_name'] = $related->warehouse->name ?? 'N/A';
        } 
        elseif ($assignment->assignable_type === \App\Models\WMS\SalesOrder::class) {
             $ctx['warehouse_id'] = $related->warehouse_id;
             $ctx['area_id'] = $related->area_id;
             $ctx['folio'] = $related->so_number;
             $ctx['area_name'] = $related->area->name ?? 'N/A';
             $ctx['warehouse_name'] = $related->warehouse->name ?? 'N/A';
        }
        elseif ($assignment->assignable_type === \App\Models\WMS\ServiceRequest::class) {
             $ctx['warehouse_id'] = $related->warehouse_id;
             $ctx['area_id'] = $related->area_id;
             $ctx['folio'] = $related->folio;
             $ctx['area_name'] = $related->area->name ?? 'N/A';
             $ctx['warehouse_name'] = $related->warehouse->name ?? 'N/A';
        }
        
        // For PO/SO, try to infer warehouse if missing? Or just leave null (global)
        
        return $ctx;
    }
}
