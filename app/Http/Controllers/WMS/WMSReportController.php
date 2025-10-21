<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\WMS\InventoryStock;

class WMSReportController extends Controller
{
    public function index()
    {
        return view('wms.reports.index');
    }

    public function inventoryDashboard()
    {
        // --- KPIs Principales (sin cambios) ---
        $totalUnits = InventoryStock::sum('quantity');
        $skusWithStock = InventoryStock::where('quantity', '>', 0)->distinct('product_id')->count();
        $locationsUsed = InventoryStock::where('quantity', '>', 0)->distinct('location_id')->count();

        // --- Gráfico: Top 10 Productos con más Stock (sin cambios) ---
        $topProducts = InventoryStock::with('product')
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();

        // --- CORRECCIÓN: Gráfico de Antigüedad del Inventario (Aging) ---
        $agingDataRaw = [
            '0-30 días' => InventoryStock::where('created_at', '>=', now()->subDays(30))->sum('quantity'),
            '31-60 días' => InventoryStock::whereBetween('created_at', [now()->subDays(60), now()->subDays(31)])->sum('quantity'),
            '61-90 días' => InventoryStock::whereBetween('created_at', [now()->subDays(90), now()->subDays(61)])->sum('quantity'),
            '90+ días' => InventoryStock::where('created_at', '<', now()->subDays(90))->sum('quantity'),
        ];

        // Aseguramos que todos los valores sean numéricos
        $agingData = array_map(function($value) {
            return (int) $value; // Convierte cada valor a un entero
        }, $agingDataRaw);
        // --- FIN DE LA CORRECCIÓN ---

        return view('wms.reports.inventory', compact(
            'totalUnits', 'skusWithStock', 'locationsUsed', 'topProducts', 'agingData'
        ));
    }

}
