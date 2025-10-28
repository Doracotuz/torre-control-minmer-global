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
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Log;

class WMSReportController extends Controller
{
    public function index()
    {
        return view('wms.reports.index');
    }

    public function inventoryDashboard()
    {
        $totalUnits = InventoryStock::sum('quantity');
        $skusWithStock = InventoryStock::where('quantity', '>', 0)->distinct('product_id')->count();
        $locationsUsed = InventoryStock::where('quantity', '>', 0)->distinct('location_id')->count();

        $totalTasks = PhysicalCountTask::whereIn('status', ['resolved', 'discrepancy'])->count();
        $resolvedTasks = PhysicalCountTask::where('status', 'resolved')->count();
        $inventoryAccuracy = ($totalTasks > 0) ? ($resolvedTasks / $totalTasks) * 100 : 0;

        $agingData = [
            '0-30 días' => PalletItem::whereHas('pallet', fn($q) => $q->where('created_at', '>=', now()->subDays(30)))->sum('quantity'),
            '31-60 días' => PalletItem::whereHas('pallet', fn($q) => $q->whereBetween('created_at', [now()->subDays(60), now()->subDays(31)]))->sum('quantity'),
            '61-90 días' => PalletItem::whereHas('pallet', fn($q) => $q->whereBetween('created_at', [now()->subDays(90), now()->subDays(61)]))->sum('quantity'),
            '90+ días' => PalletItem::whereHas('pallet', fn($q) => $q->where('created_at', '<', now()->subDays(90)))->sum('quantity'),
        ];
        $agingData = array_map(fn($v) => (int) $v, $agingData);

        $inboundData = StockMovement::select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('SUM(quantity) as total'))
            ->where('quantity', '>', 0)
            ->where('movement_type', 'like', '%RECEPCION%')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $outboundData = StockMovement::select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('SUM(ABS(quantity)) as total'))
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

        $topProductsQtyData = InventoryStock::with('product:id,name')
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->where('quantity', '>', 0)
            ->groupBy('product_id')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();
        $topProductsQty = [
            'names' => $topProductsQtyData->pluck('product.name')->toArray(),
            'quantities' => $topProductsQtyData->pluck('total_quantity')->toArray()
        ];

         $topProductsFreqData = StockMovement::with('product:id,name')
            ->select('product_id', DB::raw('COUNT(*) as movement_count'))
            ->groupBy('product_id')
            ->orderBy('movement_count', 'desc')
            ->limit(10)
            ->get();
         $topProductsFreq = [
            'names' => $topProductsFreqData->pluck('product.name')->toArray(),
            'frequencies' => $topProductsFreqData->pluck('movement_count')->toArray()
         ];

         $productQuantities = InventoryStock::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->where('quantity', '>', 0)
            ->groupBy('product_id')
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

        $availableCommittedData = PalletItem::with('product:id,sku')
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
            'available' => $availableCommittedData->map(fn($item) => max(0, $item->total_physical - $item->total_committed))->toArray(), // Asegurar no negativos
            'committed' => $availableCommittedData->pluck('total_committed')->toArray(),
        ];

        $stockByLocationTypeData = InventoryStock::join('locations', 'inventory_stocks.location_id', '=', 'locations.id')
            ->select('locations.type', DB::raw('SUM(inventory_stocks.quantity) as total_quantity'))
            ->where('inventory_stocks.quantity', '>', 0)
            ->groupBy('locations.type')
            ->pluck('total_quantity', 'type');
        $translatedStockByType = [];
        $tempLocation = new Location();
        foreach ($stockByLocationTypeData as $type => $qty) {
            $tempLocation->type = $type;
            $translatedStockByType[$tempLocation->translated_type] = (int) $qty;
        }
        $stockByLocationType = ['data' => $translatedStockByType];

        $totalStorageLocations = Location::where('type', 'storage')->count();
        $occupiedStorageLocations = Location::where('type', 'storage')->has('pallets')->count();
        $locationUtilization = [$occupiedStorageLocations, max(0, $totalStorageLocations - $occupiedStorageLocations)];

        $topLocationsQtyData = InventoryStock::with('location:id,code')
            ->select('location_id', DB::raw('SUM(quantity) as total_quantity'))
            ->where('quantity', '>', 0)
            ->groupBy('location_id')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();
        $topLocationsQty = [
            'codes' => $topLocationsQtyData->pluck('location.code')->toArray(),
            'quantities' => $topLocationsQtyData->pluck('total_quantity')->toArray()
        ];

        $receivingTrendData = StockMovement::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(quantity) as total'))
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

        $pickingTrendData = StockMovement::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(ABS(quantity)) as total'))
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

        $topProductsVolData = InventoryStock::with('product')
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->where('quantity', '>', 0)
            ->groupBy('product_id')
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

        $stockByBrandData = InventoryStock::join('products', 'inventory_stocks.product_id', '=', 'products.id')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->select('brands.name as brand_name', DB::raw('SUM(inventory_stocks.quantity) as total_quantity'))
            ->where('inventory_stocks.quantity', '>', 0)
            ->whereNotNull('products.brand_id')
            ->groupBy('brands.name')
            ->orderBy('total_quantity', 'desc')
            ->pluck('total_quantity', 'brand_name');

        $stockWithoutBrand = InventoryStock::join('products', 'inventory_stocks.product_id', '=', 'products.id')
             ->whereNull('products.brand_id')
             ->where('inventory_stocks.quantity', '>', 0)
             ->sum('inventory_stocks.quantity');
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

        return view('wms.reports.inventory', compact('kpis'));
    }

    public function showStockMovements(Request $request)
    {
        $query = StockMovement::with([
            'user:id,name', 
            'product:id,sku,name', 
            'location:id,code,aisle,rack,shelf,bin',
            'palletItem.pallet:id,lpn,purchase_order_id',
            'palletItem.pallet.purchaseOrder:id,po_number,pedimento_a4'
        ])->latest();

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

        return view('wms.reports.stock-movements', compact('movements', 'movementTypes'));
    }

    public function exportStockMovements(Request $request)
        {
            $fileName = 'reporte_movimientos_inventario_' . date('Y-m-d') . '.csv';

            $callback = function() use ($request) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

                fputcsv($file, [
                    'Fecha', 'Hora', 'Usuario', 'Tipo Movimiento', 'SKU', 'Producto',
                    'LPN', 'Ubicacion Completa', 'Ubicacion Codigo',
                    'Cantidad', 'PO Origen', 'Pedimento A4', 'ID Documento Fuente'
                ]);

                $query = StockMovement::with([
                    'user:id,name',
                    'product:id,sku,name',
                    'location:id,code,aisle,rack,shelf,bin',
                    'palletItem.pallet:id,lpn,purchase_order_id',
                    'palletItem.pallet.purchaseOrder:id,po_number,pedimento_a4'
                ])->latest();

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

}
