<?php

namespace App\Http\Controllers\Rutas;

use App\Http\Controllers\Controller;
use App\Models\Ruta;
use App\Models\Guia;
use App\Models\Factura;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RutasDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Determinar el rango de fechas. Default: último mes.
        $endDate = $request->input('end_date', Carbon::today()->format('Y-m-d'));
        $startDate = $request->input('start_date', Carbon::today()->subMonth()->format('Y-m-d'));

        // Convertir a objetos Carbon para las consultas
        $startCarbon = Carbon::parse($startDate)->startOfDay();
        $endCarbon = Carbon::parse($endDate)->endOfDay();

        // Aplicamos el filtro de fecha a cada consulta
        $rutasPorTipo = Ruta::query()->whereBetween('created_at', [$startCarbon, $endCarbon])->select('tipo_ruta', DB::raw('count(*) as total'))->groupBy('tipo_ruta')->pluck('total', 'tipo_ruta');
        $chart1Data = ['labels' => $rutasPorTipo->keys(), 'data' => $rutasPorTipo->values()];

        $guiasPorEstatus = Guia::query()->whereBetween('created_at', [$startCarbon, $endCarbon])->select('estatus', DB::raw('count(*) as total'))->groupBy('estatus')->pluck('total', 'estatus');
        $chart2Data = ['labels' => $guiasPorEstatus->keys(), 'data' => $guiasPorEstatus->values()];
        
        $rutasPorRegion = Ruta::query()->whereBetween('created_at', [$startCarbon, $endCarbon])->select('region', DB::raw('count(*) as total'))->groupBy('region')->orderBy('total', 'desc')->pluck('total', 'region');
        $chart3Data = ['labels' => $rutasPorRegion->keys(), 'data' => $rutasPorRegion->values()];

        $eventosPorTipo = Evento::query()->whereBetween('created_at', [$startCarbon, $endCarbon])->select('tipo', DB::raw('count(*) as total'))->groupBy('tipo')->pluck('total', 'tipo');
        $chart4Data = ['labels' => $eventosPorTipo->keys(), 'data' => $eventosPorTipo->values()];

        $topOperadores = Guia::query()->whereBetween('created_at', [$startCarbon, $endCarbon])->select('operador', DB::raw('count(*) as total'))->groupBy('operador')->orderBy('total', 'desc')->limit(5)->pluck('total', 'operador');
        $chart5Data = ['labels' => $topOperadores->keys(), 'data' => $topOperadores->values()];

        $estatusEntregas = Factura::query()->whereBetween('created_at', [$startCarbon, $endCarbon])->whereIn('estatus_entrega', ['Entregada', 'No Entregada'])->select('estatus_entrega', DB::raw('count(*) as total'))->groupBy('estatus_entrega')->pluck('total', 'estatus_entrega');
        $chart6Data = ['labels' => $estatusEntregas->keys(), 'data' => $estatusEntregas->values()];

        // Para el gráfico de actividad, filtramos por la fecha de actualización de la guía
        $guiasCompletadasDiarias = Guia::query()->where('estatus', 'Completada')->whereBetween('updated_at', [$startCarbon, $endCarbon])->select(DB::raw('DATE(updated_at) as fecha'), DB::raw('count(*) as total'))->groupBy('fecha')->orderBy('fecha')->pluck('total', 'fecha');
        $fechas = collect();
        $period = Carbon::parse($startDate)->toPeriod($endDate);
        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $fechas->put($formattedDate, $guiasCompletadasDiarias->get($formattedDate, 0));
        }
        $chart7Data = ['labels' => $fechas->keys()->map(function($date) { return Carbon::parse($date)->format('d M'); }), 'data' => $fechas->values()];

        return view('rutas.dashboard', compact(
            'chart1Data', 'chart2Data', 'chart3Data', 'chart4Data', 
            'chart5Data', 'chart6Data', 'chart7Data',
            'startDate', 'endDate' // Pasamos las fechas a la vista
        ));
    }

    /**
     * Exporta un resumen de guías a CSV según el rango de fechas.
     */
    public function exportCsv(Request $request)
    {
        $endDate = $request->input('end_date', Carbon::today()->format('Y-m-d'));
        $startDate = $request->input('start_date', Carbon::today()->subMonth()->format('Y-m-d'));

        $startCarbon = Carbon::parse($startDate)->startOfDay();
        $endCarbon = Carbon::parse($endDate)->endOfDay();

        $response = new StreamedResponse(function() use ($startCarbon, $endCarbon) {
            $handle = fopen('php://output', 'w');
            
            // Encabezados del CSV
            fputcsv($handle, [
                'Guia', 'Estatus', 'Operador', 'Placas', 'Ruta Asignada', 'Pedimento',
                'Fecha Creacion', 'Fecha Inicio', 'Fecha Fin', 'Total Facturas', 'Total Eventos'
            ]);

            // Obtenemos los datos con eager loading para optimizar
            $guias = Guia::with(['ruta', 'facturas', 'eventos'])
                ->whereBetween('created_at', [$startCarbon, $endCarbon])
                ->get();

            foreach ($guias as $guia) {
                fputcsv($handle, [
                    $guia->guia,
                    $guia->estatus,
                    $guia->operador,
                    $guia->placas,
                    $guia->ruta ? $guia->ruta->nombre : 'N/A', // Nombre de la ruta o N/A
                    $guia->pedimento ?? 'N/A',
                    $guia->created_at->format('Y-m-d H:i:s'),
                    $guia->fecha_inicio_ruta ? $guia->fecha_inicio_ruta->format('Y-m-d H:i:s') : 'N/A',
                    $guia->fecha_fin_ruta ? $guia->fecha_fin_ruta->format('Y-m-d H:i:s') : 'N/A',
                    $guia->facturas->count(), // Conteo de facturas
                    $guia->eventos->count()  // Conteo de eventos
                ]);
            }

            fclose($handle);
        });

        $fileName = "export_rutas_{$startDate}_a_{$endDate}.csv";
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename=\"$fileName\"");

        return $response;
    }
}