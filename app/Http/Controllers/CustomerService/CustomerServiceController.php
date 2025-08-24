<?php

namespace App\Http\Controllers\CustomerService;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CsOrder;
use App\Models\CsPlanning;
use App\Models\CsOrderEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerServiceController extends Controller
{
    public function index()
    {
        // --- DATOS PARA KPIs ---
        $pedidosPendientes = CsOrder::where('status', 'Pendiente')->count();
        $enPlanificacion = CsPlanning::whereIn('status', ['En Espera', 'Programada'])->count();
        $pedidosCompletadosMes = CsOrder::where('status', 'Terminado')
                                ->where('delivery_date', '>=', Carbon::now()->subMonth())
                                ->count();

        // --- DATOS PARA GRÁFICAS ---
        // 1. Gráfica de Órdenes por Canal
        $ordenesPorCanal = CsOrder::select('channel', DB::raw('count(*) as total'))
                                ->groupBy('channel')
                                ->orderBy('total', 'desc')
                                ->get();
        
        // 2. Gráfica de Top 10 Clientes
        $topClientes = CsOrder::select('customer_name', DB::raw('count(*) as total'))
                                ->groupBy('customer_name')
                                ->orderBy('total', 'desc')
                                ->limit(10)
                                ->get();

        // Formateamos los datos para ApexCharts
        $chartData = [
            'ordenesPorCanal' => [
                'labels' => $ordenesPorCanal->pluck('channel'),
                'series' => $ordenesPorCanal->pluck('total'),
            ],
            'topClientes' => [
                'labels' => $topClientes->pluck('customer_name'),
                'series' => $topClientes->pluck('total'),
            ],
        ];

        // --- DATOS PARA LÍNEA DE TIEMPO ---
        $actividadReciente = CsOrderEvent::with('user', 'order')
                                ->orderBy('created_at', 'desc')
                                ->limit(5)
                                ->get();

        // Pasamos todos los datos a la vista
        return view('customer-service.index', compact(
            'pedidosPendientes',
            'enPlanificacion',
            'pedidosCompletadosMes',
            'actividadReciente',
            'chartData'
        ));
    }
}
