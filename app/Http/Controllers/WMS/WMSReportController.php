<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\WMS\InventoryStock;
use App\Models\WMS\StockMovement;
use App\Models\WMS\Pallet;
use App\Models\WMS\PalletItem;
use App\Models\WMS\PurchaseOrder;
use App\Models\WMS\PhysicalCountTask;
use App\Models\Location;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WMS\Quality;
use App\Models\Area;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class WMSReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->hasFfPermission('wms.reports')) {
                abort(403, 'No tienes permiso para ver reportes WMS.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $warehouses = Warehouse::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();
        return view('wms.reports.index', compact('warehouses', 'areas'));
    }

    public function inventoryDashboard(Request $request)
    {
        $warehouseId = $request->input('warehouse_id');
        $areaId = $request->input('area_id');
        
        $warehouses = Warehouse::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();

        $baseStockQuery = PalletItem::query()
            ->join('pallets', 'pallet_items.pallet_id', '=', 'pallets.id')
            ->select('pallet_items.*');

        $basePalletItemQuery = PalletItem::query();
        $baseMovementQuery = StockMovement::query();
        $baseTaskQuery = PhysicalCountTask::query();
        $baseLocationQuery = Location::query();

        if ($warehouseId) {
            $baseStockQuery->where('pallets.location_id', function($q) use ($warehouseId) {
                $q->select('id')->from('locations')->where('warehouse_id', $warehouseId);
            });
            $basePalletItemQuery->whereHas('pallet.location', fn($q) => $q->where('warehouse_id', $warehouseId));
            $baseMovementQuery->whereHas('location', fn($q) => $q->where('warehouse_id', $warehouseId));
            $baseTaskQuery->whereHas('location', fn($q) => $q->where('warehouse_id', $warehouseId));
            $baseLocationQuery->where('warehouse_id', $warehouseId);
        }

        if ($areaId) {
            $areaFilter = fn($q) => $q->where('area_id', $areaId);
            
            $baseStockQuery->whereHas('pallet.purchaseOrder', $areaFilter);
            $basePalletItemQuery->whereHas('pallet.purchaseOrder', $areaFilter);
            $baseMovementQuery->whereHas('palletItem.pallet.purchaseOrder', $areaFilter);
        }

        $totalUnits = (clone $baseStockQuery)->sum('pallet_items.quantity');
        $skusWithStock = (clone $baseStockQuery)->where('pallet_items.quantity', '>', 0)->distinct('pallet_items.product_id')->count();
        $locationsUsed = (clone $baseStockQuery)->where('pallet_items.quantity', '>', 0)->distinct('pallets.location_id')->count();

        $totalTasks = (clone $baseTaskQuery)->whereIn('status', ['resolved', 'discrepancy'])->count();
        $resolvedTasks = (clone $baseTaskQuery)->where('status', 'resolved')->count();
        $inventoryAccuracy = ($totalTasks > 0) ? ($resolvedTasks / $totalTasks) * 100 : 0;

        $agingData = [
            '0-30 días' => (clone $basePalletItemQuery)->whereHas('pallet', fn($q) => $q->where('created_at', '>=', now()->subDays(30)))->sum('quantity'),
            '31-60 días' => (clone $basePalletItemQuery)->whereHas('pallet', fn($q) => $q->whereBetween('created_at', [now()->subDays(60), now()->subDays(31)]))->sum('quantity'),
            '61-90 días' => (clone $basePalletItemQuery)->whereHas('pallet', fn($q) => $q->whereBetween('created_at', [now()->subDays(90), now()->subDays(61)]))->sum('quantity'),
            '90+ días' => (clone $basePalletItemQuery)->whereHas('pallet', fn($q) => $q->where('created_at', '<', now()->subDays(90)))->sum('quantity'),
        ];
        $agingData = array_map(fn($v) => (int) $v, $agingData);

        $inboundData = (clone $baseMovementQuery)->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('SUM(quantity) as total'))
            ->where('quantity', '>', 0)
            ->where('movement_type', 'like', '%RECEPCION%')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $outboundData = (clone $baseMovementQuery)->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('SUM(ABS(quantity)) as total'))
            ->where('quantity', '<', 0)
            ->where('movement_type', 'like', '%SALIDA%')
             ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $trendLabels = collect([]);
        for ($i = 6; $i >= 0; $i--) { $trendLabels->push(now()->subMonths($i)->format('Y-m')); }
        $inboundTrend = ['labels' => $trendLabels->toArray(), 'data' => $trendLabels->map(fn($month) => $inboundData->get($month, 0))->toArray()];
        $outboundTrend = ['labels' => $trendLabels->toArray(), 'data' => $trendLabels->map(fn($month) => $outboundData->get($month, 0))->toArray()];

        $topProductsQtyData = (clone $baseStockQuery)->with('product:id,name')
            ->select('pallet_items.product_id', DB::raw('SUM(pallet_items.quantity) as total_quantity'))
            ->where('pallet_items.quantity', '>', 0)
            ->groupBy('pallet_items.product_id')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();
        $topProductsQty = [
            'names' => $topProductsQtyData->pluck('product.name')->toArray(),
            'quantities' => $topProductsQtyData->pluck('total_quantity')->toArray()
        ];
        
         $topProductsFreqData = (clone $baseMovementQuery)->with('product:id,name')
            ->select('product_id', DB::raw('COUNT(*) as movement_count'))
            ->groupBy('product_id')
            ->orderBy('movement_count', 'desc')
            ->limit(10)
            ->get();
         $topProductsFreq = [
            'names' => $topProductsFreqData->pluck('product.name')->toArray(),
            'frequencies' => $topProductsFreqData->pluck('movement_count')->toArray()
         ];

         $productQuantities = (clone $baseStockQuery)->select('pallet_items.product_id', DB::raw('SUM(pallet_items.quantity) as total_quantity'))
            ->where('pallet_items.quantity', '>', 0)
            ->groupBy('pallet_items.product_id')
            ->orderBy('total_quantity', 'desc')
            ->get();
         $totalOverallQuantity = $productQuantities->sum('total_quantity');
         $cumulativePercentage = 0;
         $abcCounts = ['A' => 0, 'B' => 0, 'C' => 0];
         $abcQuantities = ['A' => 0, 'B' => 0, 'C' => 0];

         if ($totalOverallQuantity > 0) {
             foreach ($productQuantities as $pq) {
                 $percentage = ($pq->total_quantity / $totalOverallQuantity) * 100;
                 $cumulativePercentage += $percentage;
                 if ($cumulativePercentage <= 80) {
                     $abcCounts['A']++;
                     $abcQuantities['A'] += $pq->total_quantity;
                 } elseif ($cumulativePercentage <= 95) {
                     $abcCounts['B']++;
                     $abcQuantities['B'] += $pq->total_quantity;
                 } else {
                     $abcCounts['C']++;
                     $abcQuantities['C'] += $pq->total_quantity;
                 }
             }
         }
        $percentA = $totalOverallQuantity > 0 ? round(($abcQuantities['A'] / $totalOverallQuantity) * 100) : 0;
        $percentB = $totalOverallQuantity > 0 ? round(($abcQuantities['B'] / $totalOverallQuantity) * 100) : 0;
        $percentC = max(0, 100 - $percentA - $percentB);

         $abcAnalysis = [
            'series' => [
                 ['name' => 'A (Top 80%)', 'data' => [$percentA]],
                 ['name' => 'B (Next 15%)', 'data' => [$percentB]],
                 ['name' => 'C (Last 5%)', 'data' => [$percentC]],
            ]
         ];

        $availableCommittedData = (clone $basePalletItemQuery)->with('product:id,sku')
            ->select('product_id',
                DB::raw('SUM(quantity) as total_physical'),
                DB::raw('SUM(committed_quantity) as total_committed')
            )
            ->where('quantity', '>', 0)
            ->groupBy('product_id')
            ->orderByDesc(DB::raw('SUM(quantity)'))
            ->limit(10)
            ->get();

        $availableCommitted = [
            'names' => $availableCommittedData->pluck('product.sku')->toArray(),
            'available' => $availableCommittedData->map(fn($item) => max(0, $item->total_physical - $item->total_committed))->toArray(),
            'committed' => $availableCommittedData->pluck('total_committed')->toArray(),
        ];

        $stockByLocationTypeData = (clone $baseStockQuery)
            ->join('locations', 'pallets.location_id', '=', 'locations.id')
            ->select('locations.type', DB::raw('SUM(pallet_items.quantity) as total_quantity'))
            ->where('pallet_items.quantity', '>', 0)
            ->groupBy('locations.type')
            ->pluck('total_quantity', 'type');
        
        $translatedStockByType = [];
        $tempLocation = new Location();
        foreach ($stockByLocationTypeData as $type => $qty) {
            $tempLocation->type = $type;
            $translatedStockByType[$tempLocation->translated_type] = (int) $qty;
        }
        $stockByLocationType = ['data' => $translatedStockByType];

        $totalStorageLocations = (clone $baseLocationQuery)->count();
        $occupiedStorageLocations = (clone $baseLocationQuery)->has('pallets')->count();
        $locationUtilization = [$occupiedStorageLocations, max(0, $totalStorageLocations - $occupiedStorageLocations)];

        $topLocationsQtyData = (clone $baseStockQuery)
            ->join('locations', 'pallets.location_id', '=', 'locations.id')
            ->select('pallets.location_id', 'locations.code', DB::raw('SUM(pallet_items.quantity) as total_quantity'))
            ->where('pallet_items.quantity', '>', 0)
            ->groupBy('pallets.location_id', 'locations.code')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();
        $topLocationsQty = [
            'codes' => $topLocationsQtyData->pluck('code')->toArray(),
            'quantities' => $topLocationsQtyData->pluck('total_quantity')->toArray()
        ];

        $receivingTrendData = (clone $baseMovementQuery)->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(quantity) as total'))
            ->where('quantity', '>', 0)
            ->where('movement_type', 'like', '%RECEPCION%')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');
        $receivingTrendLabels = []; $receivingTrendValues = [];
        for ($i = 30; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $receivingTrendLabels[] = $date;
            $receivingTrendValues[] = $receivingTrendData->get($date, 0);
        }
        $receivingTrend = ['labels' => $receivingTrendLabels, 'data' => $receivingTrendValues];

        $pickingTrendData = (clone $baseMovementQuery)->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(ABS(quantity)) as total'))
            ->where('quantity', '<', 0)
            ->where('movement_type', 'like', '%SALIDA%')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');
        $pickingTrendLabels = []; $pickingTrendValues = [];
        for ($i = 30; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $pickingTrendLabels[] = $date;
            $pickingTrendValues[] = $pickingTrendData->get($date, 0);
        }
        $pickingTrend = ['labels' => $pickingTrendLabels, 'data' => $pickingTrendValues];

        $topProductsVolData = (clone $baseStockQuery)->with('product')
            ->select('pallet_items.product_id', DB::raw('SUM(pallet_items.quantity) as total_quantity'))
            ->where('pallet_items.quantity', '>', 0)
            ->groupBy('pallet_items.product_id')
            ->get()
            ->map(function ($item) {
                $item->total_volume = $item->total_quantity * ($item->product->volume ?? 0);
                return $item;
            })
            ->filter(fn($item) => $item->total_volume > 0)
            ->sortByDesc('total_volume')
            ->take(10);

        $topProductsVol = [
            'names' => $topProductsVolData->pluck('product.name')->toArray(),
            'volumes' => $topProductsVolData->pluck('total_volume')->map(fn($v) => round($v, 2))->toArray()
        ];

        $stockByBrandData = (clone $baseStockQuery)->join('products', 'pallet_items.product_id', '=', 'products.id')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->select('brands.name as brand_name', DB::raw('SUM(pallet_items.quantity) as total_quantity'))
            ->where('pallet_items.quantity', '>', 0)
            ->whereNotNull('products.brand_id')
            ->groupBy('brands.name')
            ->orderBy('total_quantity', 'desc')
            ->pluck('total_quantity', 'brand_name');

        $stockWithoutBrand = (clone $baseStockQuery)->join('products', 'pallet_items.product_id', '=', 'products.id')
             ->whereNull('products.brand_id')
             ->where('pallet_items.quantity', '>', 0)
             ->sum('pallet_items.quantity');
        if ($stockWithoutBrand > 0) {
            $stockByBrandData->put('Sin Marca', $stockWithoutBrand);
        }

        $stockByBrandData = $stockByBrandData->map(fn($qty) => (int) $qty);
        $stockByBrandSeries = $stockByBrandData->values()->toArray();
        $stockByBrandLabels = $stockByBrandData->keys()->toArray();

        $kpis = [
            'totalUnits' => (int) $totalUnits,
            'skusWithStock' => (int) $skusWithStock,
            'locationsUsed' => (int) $locationsUsed,
            'inventoryAccuracy' => round($inventoryAccuracy, 1),
            'agingData' => $agingData,
            'inboundTrend' => $inboundTrend,
            'outboundTrend' => $outboundTrend,
            'topProductsQty' => $topProductsQty,
            'topProductsFreq' => $topProductsFreq,
            'abcAnalysis' => $abcAnalysis,
            'availableCommitted' => $availableCommitted,
            'stockByLocationType' => $stockByLocationType,
            'locationUtilization' => $locationUtilization,
            'topLocationsQty' => $topLocationsQty,
            'receivingTrend' => $receivingTrend,
            'pickingTrend' => $pickingTrend,
            'topProductsVol' => $topProductsVol,
            'stockByBrandSeries' => $stockByBrandSeries,
            'stockByBrandLabels' => $stockByBrandLabels,
        ];

        return view('wms.reports.inventory', compact('kpis', 'warehouses', 'warehouseId', 'areas', 'areaId'));
    }

    public function showStockMovements(Request $request)
    {
        $warehouseId = $request->input('warehouse_id');
        $areaId = $request->input('area_id');
        $warehouses = Warehouse::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();

        $query = StockMovement::with([
            'user:id,name', 
            'product:id,sku,name', 
            'location:id,code,aisle,rack,shelf,bin,warehouse_id',
            'palletItem.pallet:id,lpn,purchase_order_id',
            'palletItem.pallet.purchaseOrder:id,po_number,pedimento_a4,area_id',
            'palletItem.pallet.purchaseOrder.area:id,name'
        ])->latest();

        if ($warehouseId) {
            $query->whereHas('location', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            });
        }

        if ($areaId) {
            $query->whereHas('palletItem.pallet.purchaseOrder', function($q) use ($areaId) {
                $q->where('area_id', $areaId);
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->filled('sku')) {
            $sku = $request->sku;
            $query->whereHas('product', fn($q) => $q->where('sku', 'like', "%{$sku}%"));
        }
        if ($request->filled('lpn')) {
            $lpn = $request->lpn;
            $query->whereHas('palletItem.pallet', fn($q) => $q->where('lpn', 'like', "%{$lpn}%"));
        }
        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        $movements = $query->paginate(50)->withQueryString();

        $movementTypes = StockMovement::select('movement_type')->distinct()->pluck('movement_type');

        return view('wms.reports.stock-movements', compact('movements', 'movementTypes', 'warehouses', 'warehouseId', 'areas', 'areaId'));
    }

    public function exportStockMovements(Request $request)
    {
        $fileName = 'reporte_movimientos_inventario_' . date('Y-m-d') . '.csv';
        $warehouseId = $request->input('warehouse_id');
        $areaId = $request->input('area_id');

        $callback = function() use ($request, $warehouseId, $areaId) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'Fecha', 'Hora', 'Usuario', 'Tipo Movimiento', 'SKU', 'Producto',
                'LPN', 'Ubicacion Completa', 'Ubicacion Codigo',
                'Cantidad', 'PO Origen', 'Area', 'Pedimento A4', 'ID Documento Fuente'
            ]);

            $query = StockMovement::with([
                'user:id,name',
                'product:id,sku,name',
                'location:id,code,aisle,rack,shelf,bin,warehouse_id',
                'palletItem.pallet:id,lpn,purchase_order_id',
                'palletItem.pallet.purchaseOrder:id,po_number,pedimento_a4,area_id',
                'palletItem.pallet.purchaseOrder.area:id,name'
            ])->latest();

            if ($warehouseId) {
                $query->whereHas('location', function ($q) use ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId);
                });
            }

            if ($areaId) {
                $query->whereHas('palletItem.pallet.purchaseOrder', function($q) use ($areaId) {
                    $q->where('area_id', $areaId);
                });
            }

            if ($request->filled('start_date')) { $query->whereDate('created_at', '>=', $request->start_date); }
            if ($request->filled('end_date')) { $query->whereDate('created_at', '<=', $request->end_date); }
            if ($request->filled('sku')) { $query->whereHas('product', fn($q) => $q->where('sku', 'like', "%{$request->sku}%")); }
            if ($request->filled('lpn')) { $query->whereHas('palletItem.pallet', fn($q) => $q->where('lpn', 'like', "%{$request->lpn}%")); }
            if ($request->filled('movement_type')) { $query->where('movement_type', $request->movement_type); }

            $query->chunk(500, function ($movements) use ($file) {
                foreach ($movements as $mov) {
                    $ubicacionCompleta = 'N/A';
                    if ($mov->location) {
                        $ubicacionCompleta = ($mov->location->aisle ?? '?') . '-' .
                                            ($mov->location->rack ?? '?') . '-' .
                                            ($mov->location->shelf ?? '?') . '-' .
                                            ($mov->location->bin ?? '?');
                    }

                    fputcsv($file, [
                        $mov->created_at->format('Y-m-d'),
                        $mov->created_at->format('H:i:s'),
                        $mov->user->name ?? 'Sistema',
                        $mov->movement_type,
                        $mov->product->sku ?? 'N/A',
                        $mov->product->name ?? 'N/A',
                        $mov->palletItem->pallet->lpn ?? 'N/A',
                        $ubicacionCompleta,
                        $mov->location->code ?? 'N/A',
                        $mov->quantity,
                        $mov->palletItem->pallet->purchaseOrder->po_number ?? 'N/A',
                        $mov->palletItem->pallet->purchaseOrder->area->name ?? 'N/A',
                        $mov->palletItem->pallet->purchaseOrder->pedimento_a4 ?? 'N/A',
                        $mov->source_id,
                    ]);
                }
            });

            fclose($file);
        };

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        return new StreamedResponse($callback, 200, $headers);
    }

    public function showAgingReport(Request $request)
    {
        $warehouseId = $request->input('warehouse_id');
        $areaId = $request->input('area_id');
        $warehouses = Warehouse::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();

        $query = PalletItem::where('quantity', '>', 0)
            ->with([
                'product:id,sku,name',
                'quality:id,name',
                'pallet.location:id,code,warehouse_id',
                'pallet.purchaseOrder:id,po_number,area_id',
                'pallet.purchaseOrder.area:id,name'
            ])
            ->join('pallets', 'pallet_items.pallet_id', '=', 'pallets.id')
            ->select('pallet_items.*', DB::raw('DATEDIFF(NOW(), pallets.created_at) as age_in_days'));

        if ($warehouseId) {
            $query->whereHas('pallet.location', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            });
        }

        if ($areaId) {
            $query->whereHas('pallet.purchaseOrder', function ($q) use ($areaId) {
                $q->where('area_id', $areaId);
            });
        }

        if ($request->filled('sku')) {
            $sku = $request->sku;
            $query->whereHas('product', fn($q) => $q->where('sku', 'like', "%{$sku}%"));
        }
        if ($request->filled('lpn')) {
            $lpn = $request->lpn;
            $query->whereHas('pallet', fn($q) => $q->where('lpn', 'like', "%{$lpn}%"));
        }
        if ($request->filled('age_bucket')) {
            switch ($request->age_bucket) {
                case '0-30':
                    $query->whereRaw('DATEDIFF(NOW(), pallets.created_at) <= 30');
                    break;
                case '31-60':
                    $query->whereRaw('DATEDIFF(NOW(), pallets.created_at) BETWEEN 31 AND 60');
                    break;
                case '61-90':
                    $query->whereRaw('DATEDIFF(NOW(), pallets.created_at) BETWEEN 61 AND 90');
                    break;
                case '90+':
                    $query->whereRaw('DATEDIFF(NOW(), pallets.created_at) > 90');
                    break;
            }
        }

        $agingItems = $query->orderBy('age_in_days', 'desc')
                            ->paginate(50)
                            ->withQueryString();

        return view('wms.reports.inventory-aging', compact('agingItems', 'warehouses', 'warehouseId', 'areas', 'areaId'));
    }

    public function exportAgingReport(Request $request)
    {
        $fileName = 'reporte_antiguedad_inventario_' . date('Y-m-d') . '.csv';
        $warehouseId = $request->input('warehouse_id');
        $areaId = $request->input('area_id');

        $callback = function() use ($request, $warehouseId, $areaId) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'LPN', 'SKU', 'Producto', 'Calidad', 'Cantidad',
                'Ubicacion', 'PO Origen', 'Area', 'Fecha Recepcion', 'Dias Antiguedad'
            ]);

            $query = PalletItem::where('quantity', '>', 0)
                ->with([
                    'product:id,sku,name',
                    'quality:id,name',
                    'pallet.location:id,code,warehouse_id',
                    'pallet.purchaseOrder:id,po_number,area_id',
                    'pallet.purchaseOrder.area:id,name'
                ])
                ->join('pallets', 'pallet_items.pallet_id', '=', 'pallets.id')
                ->select('pallet_items.*', DB::raw('DATEDIFF(NOW(), pallets.created_at) as age_in_days'));

            if ($warehouseId) {
                $query->whereHas('pallet.location', function ($q) use ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId);
                });
            }

            if ($areaId) {
                $query->whereHas('pallet.purchaseOrder', function ($q) use ($areaId) {
                    $q->where('area_id', $areaId);
                });
            }

            if ($request->filled('sku')) {
                $sku = $request->sku;
                $query->whereHas('product', fn($q) => $q->where('sku', 'like', "%{$sku}%"));
            }
            if ($request->filled('lpn')) {
                $lpn = $request->lpn;
                $query->whereHas('pallet', fn($q) => $q->where('lpn', 'like', "%{$lpn}%"));
            }
            if ($request->filled('age_bucket')) {
                 switch ($request->age_bucket) {
                    case '0-30': $query->whereRaw('DATEDIFF(NOW(), pallets.created_at) <= 30'); break;
                    case '31-60': $query->whereRaw('DATEDIFF(NOW(), pallets.created_at) BETWEEN 31 AND 60'); break;
                    case '61-90': $query->whereRaw('DATEDIFF(NOW(), pallets.created_at) BETWEEN 61 AND 90'); break;
                    case '90+': $query->whereRaw('DATEDIFF(NOW(), pallets.created_at) > 90'); break;
                }
            }

            $query->orderBy('age_in_days', 'desc')
                ->chunk(500, function ($items) use ($file) {
                    foreach ($items as $item) {
                        fputcsv($file, [
                            $item->pallet->lpn ?? 'N/A',
                            $item->product->sku ?? 'N/A',
                            $item->product->name ?? 'N/A',
                            $item->quality->name ?? 'N/A',
                            $item->quantity,
                            $item->pallet->location->code ?? 'N/A',
                            $item->pallet->purchaseOrder->po_number ?? 'N/A',
                            $item->pallet->purchaseOrder->area->name ?? 'N/A',
                            $item->pallet->created_at->format('Y-m-d'),
                            $item->age_in_days,
                        ]);
                    }
                });

            fclose($file);
        };

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        return new StreamedResponse($callback, 200, $headers);
    }
    
    public function showNonAvailableReport(Request $request)
    {
        $warehouseId = $request->input('warehouse_id');
        $areaId = $request->input('area_id');
        $warehouses = Warehouse::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();
        
        $availableQualityName = 'Disponible';

        $availableQuality = Quality::where('name', $availableQualityName)->first();
        $availableQualityId = $availableQuality ? $availableQuality->id : -1;

        $query = PalletItem::where('quantity', '>', 0)
            ->where('quality_id', '!=', $availableQualityId)
            ->with([
                'product:id,sku,name',
                'quality:id,name',
                'pallet.location:id,code,warehouse_id',
                'pallet.purchaseOrder:id,po_number,area_id',
                'pallet.purchaseOrder.area:id,name'
            ]);

        if ($warehouseId) {
            $query->whereHas('pallet.location', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            });
        }

        if ($areaId) {
            $query->whereHas('pallet.purchaseOrder', function ($q) use ($areaId) {
                $q->where('area_id', $areaId);
            });
        }

        if ($request->filled('sku')) {
            $sku = $request->sku;
            $query->whereHas('product', fn($q) => $q->where('sku', 'like', "%{$sku}%"));
        }
        if ($request->filled('lpn')) {
            $lpn = $request->lpn;
            $query->whereHas('pallet', fn($q) => $q->where('lpn', 'like', "%{$lpn}%"));
        }
        if ($request->filled('quality_id')) {
            $query->where('quality_id', $request->quality_id);
        }

        $nonAvailableItems = $query->orderBy('quality_id')
                                   ->latest('updated_at')
                                   ->paginate(50)
                                   ->withQueryString();

        $qualities = Quality::where('name', '!=', $availableQualityName)->orderBy('name')->get();

        return view('wms.reports.non-available-inventory', compact('nonAvailableItems', 'qualities', 'warehouses', 'warehouseId', 'areas', 'areaId'));
    }

    public function exportNonAvailableReport(Request $request)
    {
        $fileName = 'reporte_inventario_no_disponible_' . date('Y-m-d') . '.csv';
        $warehouseId = $request->input('warehouse_id');
        $areaId = $request->input('area_id');

        $callback = function() use ($request, $warehouseId, $areaId) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'LPN', 'SKU', 'Producto', 'Calidad', 'Cantidad',
                'Ubicacion', 'PO Origen', 'Area', 'Fecha Recepcion', 'Ultima Actualizacion'
            ]);

            $availableQualityName = 'Disponible';
            $availableQuality = Quality::where('name', $availableQualityName)->first();
            $availableQualityId = $availableQuality ? $availableQuality->id : -1;

            $query = PalletItem::where('quantity', '>', 0)
                ->where('quality_id', '!=', $availableQualityId)
                ->with([
                    'product:id,sku,name',
                    'quality:id,name',
                    'pallet.location:id,code,warehouse_id',
                    'pallet.purchaseOrder:id,po_number,area_id',
                    'pallet.purchaseOrder.area:id,name'
                ]);

            if ($warehouseId) {
                $query->whereHas('pallet.location', function ($q) use ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId);
                });
            }

            if ($areaId) {
                $query->whereHas('pallet.purchaseOrder', function ($q) use ($areaId) {
                    $q->where('area_id', $areaId);
                });
            }

            if ($request->filled('sku')) {
                $sku = $request->sku;
                $query->whereHas('product', fn($q) => $q->where('sku', 'like', "%{$sku}%"));
            }
            if ($request->filled('lpn')) {
                $lpn = $request->lpn;
                $query->whereHas('pallet', fn($q) => $q->where('lpn', 'like', "%{$lpn}%"));
            }
            if ($request->filled('quality_id')) {
                $query->where('quality_id', $request->quality_id);
            }

            $query->orderBy('quality_id')
                ->latest('updated_at')
                ->chunk(500, function ($items) use ($file) {
                    foreach ($items as $item) {
                        fputcsv($file, [
                            $item->pallet->lpn ?? 'N/A',
                            $item->product->sku ?? 'N/A',
                            $item->product->name ?? 'N/A',
                            $item->quality->name ?? 'N/A',
                            $item->quantity,
                            $item->pallet->location->code ?? 'N/A',
                            $item->pallet->purchaseOrder->po_number ?? 'N/A',
                            $item->pallet->purchaseOrder->area->name ?? 'N/A',
                            $item->pallet->created_at->format('Y-m-d'),
                            $item->updated_at->format('Y-m-d H:i'),
                        ]);
                    }
                });

            fclose($file);
        };

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        return new StreamedResponse($callback, 200, $headers);
    }

    public function showAbcAnalysis(Request $request)
    {
        $days = $request->input('days', 90);
        $startDate = Carbon::now()->subDays($days);
        
        $warehouseId = $request->input('warehouse_id');
        $areaId = $request->input('area_id');
        $warehouses = Warehouse::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();

        $analysisData = $this->getAbcAnalysisData($startDate, $warehouseId, $areaId);

        $matrix = [
            'AX' => $analysisData->where('volume_class', 'A')->where('freq_class', 'X')->count(),
            'AY' => $analysisData->where('volume_class', 'A')->where('freq_class', 'Y')->count(),
            'AZ' => $analysisData->where('volume_class', 'A')->where('freq_class', 'Z')->count(),
            'BX' => $analysisData->where('volume_class', 'B')->where('freq_class', 'X')->count(),
            'BY' => $analysisData->where('volume_class', 'B')->where('freq_class', 'Y')->count(),
            'BZ' => $analysisData->where('volume_class', 'B')->where('freq_class', 'Z')->count(),
            'CX' => $analysisData->where('volume_class', 'C')->where('freq_class', 'X')->count(),
            'CY' => $analysisData->where('volume_class', 'C')->where('freq_class', 'Y')->count(),
            'CZ' => $analysisData->where('volume_class', 'C')->where('freq_class', 'Z')->count(),
        ];

        return view('wms.reports.abc-analysis', [
            'analysisData' => $analysisData,
            'matrix' => $matrix,
            'days' => $days,
            'warehouses' => $warehouses,
            'warehouseId' => $warehouseId,
            'areas' => $areas,
            'areaId' => $areaId
        ]);
    }

    public function exportAbcAnalysis(Request $request)
    {
        $days = $request->input('days', 90);
        $startDate = Carbon::now()->subDays($days);
        $fileName = 'reporte_abc_xyz_' . date('Y-m-d') . '.csv';
        
        $warehouseId = $request->input('warehouse_id');
        $areaId = $request->input('area_id');
        
        $analysisData = $this->getAbcAnalysisData($startDate, $warehouseId, $areaId);

        $callback = function() use ($analysisData) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'SKU', 'Producto', 
                'Clase Volumen', 'Volumen Total (Uds)', '% Acum. Volumen',
                'Clase Frecuencia', 'Total Picks', '% Acum. Frecuencia',
                'Clase ABC-XYZ'
            ]);

            foreach ($analysisData as $item) {
                fputcsv($file, [
                    $item->sku,
                    $item->name,
                    $item->volume_class,
                    $item->total_volume,
                    round($item->volume_cum_perc * 100, 2) . '%',
                    $item->freq_class,
                    $item->total_frequency,
                    round($item->freq_cum_perc * 100, 2) . '%',
                    $item->abc_class
                ]);
            }
            fclose($file);
        };

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        return new StreamedResponse($callback, 200, $headers);
    }

    private function getAbcAnalysisData(Carbon $startDate, $warehouseId = null, $areaId = null)
    {
        $volumeQuery = Product::join('pallet_items', 'products.id', '=', 'pallet_items.product_id')
            ->join('pallets', 'pallet_items.pallet_id', '=', 'pallets.id')
            ->join('locations', 'pallets.location_id', '=', 'locations.id')
            ->select('products.id', 'products.sku', 'products.name', DB::raw('SUM(pallet_items.quantity) as total_volume'))
            ->where('pallet_items.quantity', '>', 0);
        
        if ($warehouseId) {
            $volumeQuery->where('locations.warehouse_id', $warehouseId);
        }

        if ($areaId) {
            $volumeQuery->join('purchase_orders', 'pallets.purchase_order_id', '=', 'purchase_orders.id')
                        ->where('purchase_orders.area_id', $areaId);
        }

        $volumeData = $volumeQuery->groupBy('products.id', 'products.sku', 'products.name')
            ->get()
            ->keyBy('id');

        $frequencyQuery = StockMovement::select('product_id', DB::raw('COUNT(id) as total_frequency'))
            ->where('quantity', '<', 0)
            ->whereIn('movement_type', ['SALIDA-PICKING', 'AJUSTE-MANUAL', 'SPLIT-OUT'])
            ->where('created_at', '>=', $startDate);

        if ($warehouseId) {
            $frequencyQuery->whereHas('location', fn($q) => $q->where('warehouse_id', $warehouseId));
        }

        if ($areaId) {
            $frequencyQuery->whereHas('palletItem.pallet.purchaseOrder', fn($q) => $q->where('area_id', $areaId));
        }

        $frequencyData = $frequencyQuery->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $combinedData = $volumeData->map(function ($item) use ($frequencyData) {
            $freq = $frequencyData->get($item->id);
            return (object)[
                'id' => $item->id,
                'sku' => $item->sku,
                'name' => $item->name,
                'total_volume' => (int) $item->total_volume,
                'total_frequency' => $freq ? (int) $freq->total_frequency : 0,
            ];
        });

        $totalVolume = $combinedData->sum('total_volume');
        $runningVolume = 0;
        $classifiedByVolume = $combinedData->sortByDesc('total_volume')->map(function ($item) use ($totalVolume, &$runningVolume) {
            $runningVolume += $item->total_volume;
            $cumPerc = ($totalVolume > 0) ? $runningVolume / $totalVolume : 0;
            $item->volume_cum_perc = $cumPerc;

            if ($cumPerc <= 0.80) $item->volume_class = 'A';
            elseif ($cumPerc <= 0.95) $item->volume_class = 'B';
            else $item->volume_class = 'C';
            
            return $item;
        });

        $totalFrequency = $combinedData->sum('total_frequency');
        $runningFreq = 0;
        $finalData = $classifiedByVolume->sortByDesc('total_frequency')->map(function ($item) use ($totalFrequency, &$runningFreq) {
            $runningFreq += $item->total_frequency;
            $cumPerc = ($totalFrequency > 0) ? $runningFreq / $totalFrequency : 0;
            $item->freq_cum_perc = $cumPerc;

            if ($cumPerc <= 0.80) $item->freq_class = 'X';
            elseif ($cumPerc <= 0.95) $item->freq_class = 'Y';
            else $item->freq_class = 'Z';
            
            $item->abc_class = $item->volume_class . $item->freq_class;
            return $item;
        });

        return $finalData->sortBy('abc_class');
    }

    public function showSlottingHeatmap(Request $request)
    {
        $days = $request->input('days', 90);
        $startDate = Carbon::now()->subDays($days);
        
        $warehouseId = $request->input('warehouse_id');
        $areaId = $request->input('area_id');
        $warehouses = Warehouse::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();
        
        $abcData = $this->getAbcAnalysisData($startDate, $warehouseId, $areaId)->keyBy('id');

        $locationPickFreqQuery = StockMovement::select('location_id', DB::raw('COUNT(id) as pick_frequency'))
            ->where('quantity', '<', 0)
            ->where('movement_type', 'SALIDA-PICKING')
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('location_id');
        
        if ($warehouseId) {
            $locationPickFreqQuery->whereHas('location', fn($q) => $q->where('warehouse_id', $warehouseId));
        }
        
        if ($areaId) {
            $locationPickFreqQuery->whereHas('palletItem.pallet.purchaseOrder', fn($q) => $q->where('area_id', $areaId));
        }

        $locationPickFreq = $locationPickFreqQuery->groupBy('location_id')->get()->keyBy('location_id');
            
        $maxFreq = $locationPickFreq->max('pick_frequency') ?: 1;

        $stockInLocationsQuery = PalletItem::where('quantity', '>', 0)
            ->with([
                'product:id,sku,name',
                'quality:id,name',
                'pallet:id,lpn,location_id,created_at'
            ])
            ->whereHas('pallet.location', fn($q) => $q->whereIn('type', ['storage', 'picking', 'receiving', 'shipping', 'quality_control']));
        
        if ($warehouseId) {
            $stockInLocationsQuery->whereHas('pallet.location', fn($q) => $q->where('warehouse_id', $warehouseId));
        }

        if ($areaId) {
            $stockInLocationsQuery->whereHas('pallet.purchaseOrder', fn($q) => $q->where('area_id', $areaId));
        }

        $stockInLocations = $stockInLocationsQuery->get()->groupBy('pallet.location_id');

        $locationsQuery = Location::whereIn('type', ['storage', 'picking', 'receiving', 'shipping', 'quality_control']);
        if ($warehouseId) {
            $locationsQuery->where('warehouse_id', $warehouseId);
        }
        $locations = $locationsQuery->orderBy('aisle')->orderBy('rack')->orderBy('shelf')->orderBy('bin')->get();

        $heatmapData = [];
        foreach ($locations as $loc) {
            $locStock = $stockInLocations->get($loc->id, collect());
            $locFreqData = $locationPickFreq->get($loc->id);
            $locFreq = $locFreqData ? $locFreqData->pick_frequency : 0;
            $dominantStock = $locStock->sortByDesc('quantity')->first();
            $productClass = null;
            $mismatchScore = 0;
            $mismatchMessage = 'Ubicación vacía o sin picks.';

            if ($dominantStock) {
                $productAbc = $abcData->get($dominantStock->product_id);
                $productClass = $productAbc ? $productAbc->abc_class : 'N/A';
                
                $isFastProduct = str_contains($productClass, 'A') || str_contains($productClass, 'X');
                $isSlowProduct = str_contains($productClass, 'C') || str_contains($productClass, 'Z');
                $isFastLocation = $locFreq > ($maxFreq * 0.5);

                if ($isFastProduct && $isFastLocation) {
                    $mismatchScore = 10;
                    $mismatchMessage = 'Ideal: Producto rápido en ubicación rápida.';
                } elseif ($isFastProduct && !$isFastLocation) {
                    $mismatchScore = -5;
                    $mismatchMessage = 'Error: Producto rápido en ubicación lenta.';
                } elseif ($isSlowProduct && $isFastLocation) {
                    $mismatchScore = -10;
                    $mismatchMessage = 'Error Crítico: Producto lento en ubicación de picking rápido.';
                } elseif ($isSlowProduct && !$isFastLocation) {
                    $mismatchScore = 5;
                    $mismatchMessage = 'Correcto: Producto lento en ubicación lenta.';
                } else {
                    $mismatchScore = 1;
                    $mismatchMessage = 'Producto de media rotación en ubicación media.';
                }
            }
            
            $heatmapData[] = (object)[
                'id' => $loc->id,
                'code' => $loc->code,
                'full_location' => "{$loc->aisle}-{$loc->rack}-{$loc->shelf}-{$loc->bin}",
                'aisle' => $loc->aisle,
                'pick_frequency' => $locFreq,
                'pick_intensity' => ($locFreq / $maxFreq) * 100,
                'product_class' => $productClass,
                'mismatch_score' => $mismatchScore,
                'mismatch_message' => $mismatchMessage,
                'stock_items' => $locStock
            ];
        }

        $groupedHeatmapData = collect($heatmapData)->groupBy('aisle');

        return view('wms.reports.slotting-heatmap', [
            'heatmapData' => $groupedHeatmapData,
            'days' => $days,
            'warehouses' => $warehouses,
            'warehouseId' => $warehouseId,
            'areas' => $areas,
            'areaId' => $areaId
        ]);
    }
}