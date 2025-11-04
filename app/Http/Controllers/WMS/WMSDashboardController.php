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
use Illuminate\Support\Facades\DB;

class WMSDashboardController extends Controller
{
    public function index()
    {
        $totalUnits = InventoryStock::sum('quantity');
        $skusWithStock = InventoryStock::where('quantity', '>', 0)->distinct('product_id')->count();
        $totalStorageLocations = Location::where('type', 'storage')->count();
        $occupiedStorageLocations = Location::where('type', 'storage')->has('pallets')->count();
        $locationUtilization = ($totalStorageLocations > 0) ? ($occupiedStorageLocations / $totalStorageLocations) * 100 : 0;
        
        $totalTasks = PhysicalCountTask::whereIn('status', ['resolved', 'discrepancy'])->count();
        $resolvedTasks = PhysicalCountTask::where('status', 'resolved')->count();
        $inventoryAccuracy = ($totalTasks > 0) ? ($resolvedTasks / $totalTasks) * 100 : 0;

        $kpis = [
            ['label' => 'Unidades Totales', 'value' => $totalUnits, 'format' => 'number', 'icon' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4'],
            ['label' => 'SKUs Únicos', 'value' => $skusWithStock, 'format' => 'number', 'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
            ['label' => 'Utilización de Ubic.', 'value' => $locationUtilization, 'format' => 'percent', 'icon' => 'M3 21v-4.5m0 0A1.5 1.5 0 014.5 15h15a1.5 1.5 0 011.5 1.5M21 16.5v4.5m0 0a1.5 1.5 0 01-1.5 1.5h-15a1.5 1.5 0 01-1.5-1.5m1.5-1.5H12m0 0v-1.5m0 1.5H9m3-1.5H6m0 0v-1.5m0 1.5H3m18-1.5v-1.5m0 1.5h-3m3-1.5h-6m0 0v-1.5m0 1.5h-3m0 0v-1.5m0 1.5h-3m-3-1.5H6'],
            ['label' => 'Precisión de Inv.', 'value' => $inventoryAccuracy, 'format' => 'percent', 'icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
        ];

        $receivingPOs = PurchaseOrder::where('status', 'Receiving')
            ->with('latestArrival') //
            ->latest('updated_at')
            ->limit(10)
            ->get();
        $pendingPOs = PurchaseOrder::where('status', 'Pending')
            ->orderBy('expected_date', 'asc')
            ->limit(10)
            ->get();

        $pickingSOs = SalesOrder::where('status', 'Picking')
            ->with('pickList')
            ->latest('updated_at')
            ->limit(10)
            ->get();
        $pendingSOs = SalesOrder::where('status', 'Pending')
            ->orderBy('order_date', 'asc')
            ->limit(10)
            ->get();

        $discrepancyTasks = PhysicalCountTask::where('status', 'discrepancy')
            ->with('product', 'location')
            ->limit(5)
            ->get();
            
        $availableQuality = Quality::where('name', 'Disponible')->first();
        $availableQualityId = $availableQuality ? $availableQuality->id : -1;
        
        $nonAvailableStock = PalletItem::where('quantity', '>', 0)
            ->where('quality_id', '!=', $availableQualityId)
            ->with('product:id,sku', 'quality:id,name', 'pallet:id,lpn')
            ->limit(5)
            ->get();

        return view('wms.dashboard', compact(
            'kpis',
            'receivingPOs',
            'pendingPOs',
            'pickingSOs',
            'pendingSOs',
            'discrepancyTasks',
            'nonAvailableStock'
        ));
    }
}