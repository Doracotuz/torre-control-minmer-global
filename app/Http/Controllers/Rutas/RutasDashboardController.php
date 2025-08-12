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
use App\Models\ManiobraEvento;

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
            
            // --- INICIA CAMBIO: Nuevas columnas para el reporte de eventos ---
            fputcsv($handle, [
                'Guia', 'Estatus Guia', 'Operador', 'Placas', 'Ruta Asignada', 'Pedimento',
                'Fecha Creacion Guia', 'Fecha Evento', 'Tipo Evento', 'Detalle Evento', 
                'Nota Evento', 'Latitud', 'Longitud', 'Municipio'
            ]);

            $guias = Guia::with(['ruta', 'eventos'])
                ->whereBetween('created_at', [$startCarbon, $endCarbon])
                ->get();

            foreach ($guias as $guia) {
                // Si una guía no tiene eventos, se exporta una línea con la info de la guía
                if ($guia->eventos->isEmpty()) {
                     fputcsv($handle, [
                        $guia->guia, $guia->estatus, $guia->operador, $guia->placas,
                        $guia->ruta->nombre ?? 'N/A', $guia->pedimento ?? 'N/A',
                        $guia->created_at->format('Y-m-d H:i:s'), 'N/A', 'N/A', 'N/A',
                        'N/A', 'N/A', 'N/A', 'N/A'
                    ]);
                } else {
                    // Se itera sobre cada evento y se crea una fila por cada uno
                    foreach($guia->eventos as $evento) {
                         fputcsv($handle, [
                            $guia->guia, $guia->estatus, $guia->operador, $guia->placas,
                            $guia->ruta->nombre ?? 'N/A', $guia->pedimento ?? 'N/A',
                            $guia->created_at->format('Y-m-d H:i:s'),
                            $evento->fecha_evento->format('Y-m-d H:i:s'),
                            $evento->tipo,
                            $evento->subtipo,
                            $evento->nota,
                            $evento->latitud,
                            $evento->longitud,
                            $evento->municipio ?? 'N/A'
                        ]);
                    }
                }
            }

            fclose($handle);
        });

        $fileName = "export_rutas_{$startDate}_a_{$endDate}.csv";
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename=\"$fileName\"");

        return $response;
    }

    public function exportTiemposReport(Request $request)
    {
        $endDate = $request->input('end_date', \Carbon\Carbon::today()->format('Y-m-d'));
        $startDate = $request->input('start_date', \Carbon\Carbon::today()->subMonth()->format('Y-m-d'));
        $startCarbon = \Carbon\Carbon::parse($startDate)->startOfDay();
        $endCarbon = \Carbon\Carbon::parse($endDate)->endOfDay();

        $guias = Guia::with(['facturas', 'eventos.factura', 'maniobraEventos'])
            ->whereBetween('created_at', [$startCarbon, $endCarbon])
            ->get();
            
        $guias->load('facturas.evidenciasManiobra');

        $csvData = [];
        $csvData[] = [
            'Fecha', 'Hora', 'No. Empleado', 'Coordenadas', 'Municipio', 
            'Evento', 'Guia', 'Facturas', 'Destino', 'Operador'
        ];

        foreach ($guias as $guia) {
            $facturasConcatenadas = $guia->facturas->pluck('numero_factura')->join(' | ');
            $destinosConcatenados = $guia->facturas->pluck('destino')->unique()->join(' | ');

            // 1. Eventos del Operador
            foreach ($guia->eventos as $evento) {
                $facturaEspecifica = $evento->factura;
                if ($evento->tipo === 'Entrega' && $facturaEspecifica) {
                    $facturasParaMostrar = $facturaEspecifica->numero_factura;
                    $destinoParaMostrar = $facturaEspecifica->destino;
                } else {
                    $facturasParaMostrar = $facturasConcatenadas;
                    $destinoParaMostrar = $destinosConcatenados;
                }
                $csvData[] = [
                    $evento->fecha_evento->format('d/m/Y'),
                    $evento->fecha_evento->format('H:i:s'),
                    'N/A (Operador)',
                    "{$evento->latitud}, {$evento->longitud}",
                    $evento->municipio ?? 'N/A',
                    $evento->subtipo,
                    $guia->guia,
                    $facturasParaMostrar,
                    $destinoParaMostrar,
                    $guia->operador,
                ];
            }
            
            // 2. Eventos generales del Maniobrista
            foreach ($guia->maniobraEventos as $evento) {
                if ($evento->evento_tipo === 'Flujo Completado') continue;
                $csvData[] = [
                    $evento->created_at->format('d/m/Y'),
                    $evento->created_at->format('H:i:s'),
                    $evento->numero_empleado,
                    "{$evento->latitud}, {$evento->longitud}",
                    $evento->municipio ?? 'N/A',
                    $evento->evento_tipo,
                    $guia->guia,
                    $facturasConcatenadas,
                    $destinosConcatenados,
                    $guia->operador,
                ];
            }

            // 3. Eventos de evidencia de maniobra
            foreach ($guia->facturas as $factura) {
                if ($factura->evidenciasManiobra->isNotEmpty()) {

                    // --- INICIA CORRECCIÓN: Se reemplaza el método inexistente ---
                    $evidenciaConUbicacion = $factura->evidenciasManiobra->whereNotNull('latitud')->first();
                    // --- TERMINA CORRECCIÓN ---

                    $evidenciaParaReporte = $evidenciaConUbicacion ?? $factura->evidenciasManiobra->first();
                    if ($evidenciaParaReporte) {
                        $csvData[] = [
                            $evidenciaParaReporte->created_at->format('d/m/Y'),
                            $evidenciaParaReporte->created_at->format('H:i:s'),
                            $evidenciaParaReporte->numero_empleado,
                            ($evidenciaParaReporte->latitud && $evidenciaParaReporte->longitud) ? "{$evidenciaParaReporte->latitud}, {$evidenciaParaReporte->longitud}" : 'N/A',
                            $evidenciaParaReporte->municipio ?? 'N/A',
                            'Evidencia de Entrega (Maniobra)',
                            $guia->guia,
                            $factura->numero_factura,
                            $factura->destino,
                            $guia->operador,
                        ];
                    }
                }
            }
        }

        $stream = fopen('php://memory', 'w');
        foreach ($csvData as $row) {
            fputcsv($stream, $row);
        }
        rewind($stream);
        $csvContent = stream_get_contents($stream);
        fclose($stream);

        $fileName = "reporte_tiempos_" . date('Y-m-d') . ".csv";
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
        ];

        return response($csvContent, 200, $headers);
    }
}