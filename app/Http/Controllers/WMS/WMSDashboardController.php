<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WMS\PurchaseOrder;
use App\Models\WMS\SalesOrder;
use App\Models\WMS\InventoryStock;
use App\Models\WMS\PhysicalCountTask;
use App\Models\Location;
use App\Models\WMS\PalletItem;
use App\Models\WMS\Quality;
use App\Models\Warehouse;
use App\Models\Area;
use Illuminate\Support\Facades\DB;

class WMSDashboardController extends Controller
{
    public function index(Request $request)
    {
        $warehouseId = $request->input('warehouse_id');
        $areaId = $request->input('area_id');

        $warehouses = Warehouse::orderBy('name')->get();
        $areas = \App\Models\Area::orderBy('name')->get();

        $basePOQuery = PurchaseOrder::query();
        $baseSOQuery = SalesOrder::query();
        $baseTaskQuery = PhysicalCountTask::query();
        $basePalletItemQuery = PalletItem::query()->where('quantity', '>', 0);
        $baseLocationQuery = Location::query();

        if ($warehouseId) {
            $basePOQuery->where('warehouse_id', $warehouseId);
            $baseSOQuery->where('warehouse_id', $warehouseId);
            $baseLocationQuery->where('warehouse_id', $warehouseId);
            $baseTaskQuery->whereHas('location', fn($q) => $q->where('warehouse_id', $warehouseId));
            $basePalletItemQuery->whereHas('pallet.location', fn($q) => $q->where('warehouse_id', $warehouseId));
        }

        if ($areaId) {
            $basePOQuery->where('area_id', $areaId);
            $baseSOQuery->where('area_id', $areaId);
            $basePalletItemQuery->whereHas('pallet.purchaseOrder', fn($q) => 
                $q->where('area_id', $areaId)
            );
            $baseTaskQuery->whereHas('physicalCountSession.user', fn($q) => $q->where('area_id', $areaId));
        }

        $totalUnits = (clone $basePalletItemQuery)->sum('quantity');
        
        $skusWithStock = (clone $basePalletItemQuery)
            ->distinct('product_id')
            ->count('product_id');

        $totalStorageLocations = (clone $baseLocationQuery)->count();
        
        if ($areaId) {
            $occupiedStorageLocations = Location::whereHas('pallets.purchaseOrder', function($q) use ($areaId) {
                    $q->where('area_id', $areaId);
                })
                ->whereHas('pallets.items', fn($q) => $q->where('quantity', '>', 0))
                ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
                ->count();
        } else {
            $occupiedStorageLocations = (clone $baseLocationQuery)
                ->whereHas('pallets', function($q) {
                    $q->whereHas('items', fn($i) => $i->where('quantity', '>', 0));
                })
                ->count();
        }
        
        $locationUtilization = ($totalStorageLocations > 0) ? ($occupiedStorageLocations / $totalStorageLocations) * 100 : 0;

        $totalTasks = (clone $baseTaskQuery)->whereIn('status', ['resolved', 'discrepancy'])->count();
        $resolvedTasks = (clone $baseTaskQuery)->where('status', 'resolved')->count();
        
        $inventoryAccuracy = ($totalTasks > 0) ? ($resolvedTasks / $totalTasks) * 100 : 100;

        $kpis = [
            ['label' => 'Unidades (Dueño)', 'value' => $totalUnits, 'format' => 'number', 'icon' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4'],
            ['label' => 'SKUs Activos', 'value' => $skusWithStock, 'format' => 'number', 'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
            ['label' => 'Ubicaciones Ocupadas', 'value' => $locationUtilization, 'format' => 'percent', 'icon' => 'M3 21v-4.5m0 0A1.5 1.5 0 014.5 15h15a1.5 1.5 0 011.5 1.5M21 16.5v4.5m0 0a1.5 1.5 0 01-1.5 1.5h-15a1.5 1.5 0 01-1.5-1.5m1.5-1.5H12m0 0v-1.5m0 1.5H9m3-1.5H6m0 0v-1.5m0 1.5H3m18-1.5v-1.5m0 1.5h-3m3-1.5h-6m0 0v-1.5m0 1.5h-3m0 0v-1.5m0 1.5h-3m-3-1.5H6'],
            ['label' => 'Precisión Conteo', 'value' => $inventoryAccuracy, 'format' => 'percent', 'icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
        ];

        $poStats = [
            'pending' => (clone $basePOQuery)->where('status', 'Pending')->count(),
            'receiving' => (clone $basePOQuery)->where('status', 'Receiving')->count(),
            'completed_today' => (clone $basePOQuery)->where('status', 'Completed')->whereDate('updated_at', now())->count(),
        ];

        $soStats = [
            'pending' => (clone $baseSOQuery)->where('status', 'Pending')->count(),
            'picking' => (clone $baseSOQuery)->where('status', 'Picking')->count(),
            'completed_today' => (clone $baseSOQuery)->whereIn('status', ['Shipped', 'Completed', 'Dispatched'])->whereDate('updated_at', now())->count(),
        ];

        $receivingPOs = (clone $basePOQuery)->where('status', 'Receiving')
            ->with('latestArrival')->latest('updated_at')->limit(10)->get();
        
        $pendingPOs = (clone $basePOQuery)->where('status', 'Pending')
            ->orderBy('expected_date', 'asc')->limit(10)->get();

        $pickingSOs = (clone $baseSOQuery)->where('status', 'Picking')
            ->with('pickList')->latest('updated_at')->limit(10)->get();
        
        $pendingSOs = (clone $baseSOQuery)->where('status', 'Pending')
            ->orderBy('order_date', 'asc')->limit(10)->get();

        $discrepancyTasks = (clone $baseTaskQuery)->where('status', 'discrepancy')
            ->with('product', 'location')->limit(5)->get();
            
        $availableQuality = Quality::where('name', 'Disponible')->first();
        $availableQualityId = $availableQuality ? $availableQuality->id : -1;
        
        $nonAvailableStock = (clone $basePalletItemQuery)
            ->where('quality_id', '!=', $availableQualityId)
            ->with('product:id,sku', 'quality:id,name', 'pallet:id,lpn')
            ->limit(5)->get();

        return view('wms.dashboard', compact(
            'kpis', 'receivingPOs', 'pendingPOs', 'pickingSOs', 'pendingSOs',
            'discrepancyTasks', 'nonAvailableStock', 'warehouses', 'warehouseId', 'areas', 'areaId',
            'poStats', 'soStats'
        ));
    }
}