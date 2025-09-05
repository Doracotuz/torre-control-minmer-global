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
        // --- DATOS PARA KPIs (Sin cambios) ---
        $pedidosPendientes = CsOrder::where('status', 'Pendiente')->count();
        $enPlanificacion = CsPlanning::whereIn('status', ['En Espera', 'Programada'])->count();
        $pedidosCompletadosMes = CsOrder::where('status', 'Terminado')
                                ->where('delivery_date', '>=', Carbon::now()->subMonth())
                                ->count();

        // --- DATOS PARA GRÁFICAS (Sin cambios) ---
        $ordenesPorCanal = CsOrder::select('channel', DB::raw('count(*) as total'))->groupBy('channel')->orderBy('total', 'desc')->get();
        $topClientes = CsOrder::select('customer_name', DB::raw('count(*) as total'))->groupBy('customer_name')->orderBy('total', 'desc')->limit(10)->get();
        
        $chartData = [
            'ordenesPorCanal' => ['labels' => $ordenesPorCanal->pluck('channel'), 'series' => $ordenesPorCanal->pluck('total')],
            'topClientes' => ['labels' => $topClientes->pluck('customer_name'), 'series' => $topClientes->pluck('total')],
        ];

        // --- INICIA LÓGICA MEJORADA PARA LÍNEA DE TIEMPO ---
        $eventosDePedidos = CsOrderEvent::with('user', 'order')
                                ->latest()->limit(5)->get()
                                ->map(function ($event) {
                                    $event->type = 'order'; // Añadimos un tipo para identificarlo en la vista
                                    return $event;
                                });

        $eventosDePlanning = \App\Models\CsPlanningEvent::with('user', 'planning')
                                ->whereDoesntHave('planning.order') // Solo eventos de planning manuales
                                ->latest()->limit(5)->get()
                                ->map(function ($event) {
                                    $event->type = 'planning'; // Añadimos un tipo
                                    return $event;
                                });

        // Unimos las dos colecciones, las ordenamos por fecha y tomamos los 5 más recientes
        $actividadReciente = $eventosDePedidos->concat($eventosDePlanning)
                                    ->sortByDesc('created_at')
                                    ->take(5);
        // --- TERMINA LÓGICA MEJORADA ---


        return view('customer-service.index', compact(
            'pedidosPendientes',
            'enPlanificacion',
            'pedidosCompletadosMes',
            'actividadReciente',
            'chartData'
        ));
    }
}
