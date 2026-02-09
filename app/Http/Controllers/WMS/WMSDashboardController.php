<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WMS\PurchaseOrder;
use App\Models\WMS\SalesOrder;
use App\Models\WMS\PhysicalCountTask;
use App\Models\Location;
use App\Models\WMS\PalletItem;
use App\Models\WMS\Quality;
use App\Models\Warehouse;
use App\Models\Area;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class WMSDashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->hasFfPermission('wms.dashboard')) {
            abort(403, 'No tienes permiso para ver el Dashboard WMS.');
        }

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
            $basePalletItemQuery->whereHas('pallet.purchaseOrder', fn($q) => $q->where('area_id', $areaId));
            $baseTaskQuery->whereHas('physicalCountSession.user', fn($q) => $q->where('area_id', $areaId));
        }

        $totalUnits = (clone $basePalletItemQuery)->sum('quantity');
        $skusWithStock = (clone $basePalletItemQuery)->distinct('product_id')->count('product_id');
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
            ['label' => 'Unidades (Dueño)', 'value' => $totalUnits, 'format' => 'number'],
            ['label' => 'SKUs Activos', 'value' => $skusWithStock, 'format' => 'number'],
            ['label' => 'Ubicaciones Ocupadas', 'value' => $locationUtilization, 'format' => 'percent'],
            ['label' => 'Precisión Conteo', 'value' => $inventoryAccuracy, 'format' => 'percent'],
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

        $recentPOs = (clone $basePOQuery)
            ->with(['user']) 
            ->withSum('lines', 'quantity_ordered')
            ->latest()
            ->limit(5)
            ->get();

        $recentSOs = (clone $baseSOQuery)
            ->with(['user'])
            ->withSum('lines', 'quantity_ordered')
            ->latest()
            ->limit(5)
            ->get();

        $discrepancyTasks = (clone $baseTaskQuery)->where('status', 'discrepancy')
            ->with('product', 'location')->limit(5)->get();
            
        $nonAvailableStock = (clone $basePalletItemQuery)
            ->whereHas('quality', function($q) {
                $q->where('is_available', false);
            })
            ->with('product:id,sku', 'quality:id,name', 'pallet:id,lpn')
            ->limit(5)->get();

        return view('wms.dashboard', compact(
            'kpis', 'recentPOs', 'recentSOs',
            'discrepancyTasks', 'nonAvailableStock', 'warehouses', 'warehouseId', 'areas', 'areaId',
            'poStats', 'soStats'
        ));
    }
}