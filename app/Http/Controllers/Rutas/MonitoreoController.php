<?php

namespace App\Http\Controllers\Rutas;


use App\Http\Controllers\Controller;
use App\Models\Guia;
use App\Models\Evento; // <-- AÑADE ESTA LÍNEA
use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Ruta;
use App\Services\EventRegistrationService;

class MonitoreoController extends Controller
{
    /**
     * Muestra la vista de monitoreo de rutas.
     */
    public function index()
    {
        $googleMapsApiKey = config('app.Maps_api_key');
        return view('rutas.monitoreo.index', compact('googleMapsApiKey'));
    }

    public function filter(Request $request)
    {
        $query = $this->buildFilteredQuery($request);
        $paginator = $query->withCount('facturas')->orderBy('updated_at', 'desc')->paginate(50);
        $guiasJson = $this->formatGuiasToJson($paginator->getCollection());

        return response()->json(['paginator' => $paginator, 'guiasJson' => $guiasJson]);
    }
    
    // --- INICIA CORRECCIÓN: Se añade la lógica completa para generar los datos de la tabla ---
    public function getReportData(Request $request)
    {
        $query = $this->buildFilteredQuery($request);
        $guias = $query->get();
        
        // 1. PROCESAR DATOS PARA LA TABLA
        $tableData = [];
        foreach ($guias as $guia) {
            foreach ($guia->facturas as $factura) {
                $tableData[] = [
                    // Datos de la Guía
                    'guia' => $guia->guia,
                    'operador' => $guia->operador,
                    'placas' => $guia->placas,
                    'ruta_nombre' => $guia->ruta?->nombre ?? 'N/A',
                    'estatus_r' => $guia->estatus,
                    'fecha_carga' => $guia->fecha_asignacion ? $guia->fecha_asignacion->format('d/m/Y') : 'N/A',
                    'hora_planeada' => $guia->hora_planeada ?? 'N/A',
                    'custodia' => $guia->custodia ?? 'N/A',
                    
                    // Datos de la Factura
                    'factura' => $factura->numero_factura,
                    'so' => $factura->so ?? 'N/A',
                    'destino' => $factura->destino,
                    'cajas' => $factura->cajas,
                    'botellas' => $factura->botellas,
                    'estatus_f' => $factura->estatus_entrega,
                    'entregada' => ($factura->estatus_entrega === 'Entregada' || $factura->estatus_entrega === 'No entregada') 
                                 ? $factura->updated_at->format('d/m/Y h:i A') 
                                 : 'N/A',
                ];
            }
        }
        
        // 2. PROCESAR DATOS PARA LOS GRÁFICOS
        $todasLasFacturas = $guias->pluck('facturas')->flatten();
        $guiasPorEstatus = $guias->countBy('estatus');
        $facturasPorEstatus = $todasLasFacturas->countBy('estatus_entrega');
        $facturasPorRegion = $guias->filter(fn($guia) => $guia->ruta)
                                   ->groupBy('ruta.region')
                                   ->map(fn($guiasEnRegion) => $guiasEnRegion->sum(fn($g) => $g->facturas->count()))
                                   ->sortDesc();

        return response()->json([
            'tableData' => $tableData, // Se incluye la tabla con datos
            'charts' => [
                'guiasPorEstatus' => ['labels' => $guiasPorEstatus->keys(), 'data' => $guiasPorEstatus->values()],
                'facturasPorEstatus' => ['labels' => $facturasPorEstatus->keys(), 'data' => $facturasPorEstatus->values()],
                'facturasPorRegion' => ['labels' => $facturasPorRegion->keys(), 'data' => $facturasPorRegion->values()],
            ]
        ]);
    }
    // --- TERMINA CORRECCIÓN ---

    public function exportReportCsv(Request $request)
    {
        $query = $this->buildFilteredQuery($request);
        $guias = $query->get();
        
        $fileName = "reporte_monitoreo_" . now()->format('Y-m-d_His') . ".csv";
        $headers = [ "Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=$fileName", "Pragma" => "no-cache", "Cache-Control" => "must-revalidate, post-check=0, pre-check=0", "Expires" => "0" ];

        $callback = function() use ($guias) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [ 'Guia', 'Operador', 'Ruta', 'Estatus Guia', 'Factura Afectada', 'Tipo Evento', 'Detalle Evento', 'Fecha Evento', 'Municipio', 'Latitud', 'Longitud', 'Nota' ]);

            foreach ($guias as $guia) {
                if ($guia->eventos->isEmpty()) { fputcsv($file, [$guia->guia, $guia->operador, $guia->ruta?->nombre ?? 'N/A', $guia->estatus, 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'Guía sin eventos registrados']); continue; }
                foreach ($guia->eventos as $evento) {
                    fputcsv($file, [
                        $guia->guia, $guia->operador, $guia->ruta?->nombre ?? 'N/A', $guia->estatus,
                        $evento->factura?->numero_factura ?? 'N/A', $evento->tipo, $evento->subtipo,
                        $evento->fecha_evento->format('Y-m-d H:i:s'), $evento->municipio ?? 'N/A',
                        $evento->latitud, $evento->longitud, $evento->nota
                    ]);
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    
    public function getAvailableRegions()
    {
        $regions = Ruta::select('region')->whereNotNull('region')->where('region', '!=', '')->distinct()->orderBy('region')->pluck('region');
        return response()->json($regions);
    }

    public function getAvailableStatuses()
    {
        $statuses = Guia::select('estatus')->whereNotNull('estatus')->where('estatus', '!=', '')->distinct()->orderBy('estatus')->pluck('estatus');
        return response()->json($statuses);
    }

    public function startRoute(Request $request, Guia $guia)
    {
        if ($guia->estatus !== 'Planeada') {
            return response()->json(['success' => false, 'message' => 'Esta ruta no puede ser iniciada.'], 409);
        }

        try {
            DB::beginTransaction();

            $guia->estatus = 'Camino a carga'; // O el estatus inicial que prefieras, ej: 'Camino a carga'
            $guia->fecha_inicio_ruta = now();
            $guia->save();

            // --- INICIA CORRECCIÓN: Se obtienen las coordenadas de la primera parada ---
            $firstStop = $guia->ruta?->paradas()->orderBy('secuencia')->first();

            Evento::create([
                'guia_id' => $guia->id,
                'tipo' => 'Sistema',
                'subtipo' => 'Inicio de Ruta',
                'latitud' => $firstStop?->latitud ?? 0, // Se usa la latitud de la primera parada
                'longitud' => $firstStop?->longitud ?? 0, // Se usa la longitud de la primera parada
                'municipio' => $firstStop?->nombre_lugar ?? 'N/A', // Usamos el nombre del lugar como referencia
                'fecha_evento' => now(),
                'nota' => 'Ruta iniciada desde el panel de monitoreo.'
            ]);
            // --- TERMINA CORRECCIÓN ---

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Ruta iniciada exitosamente.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al iniciar ruta desde monitoreo: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Ocurrió un error en el servidor.'], 500);
        }
    }

    public function storeEvent(Request $request, Guia $guia, \App\Services\EventRegistrationService $eventService)
    {
        $validatedData = $request->validate([
            'tipo' => 'required|in:Notificacion,Incidencias,Entrega,Sistema',
            'subtipo' => 'required|string|max:255',
            'nota' => 'nullable|string',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'municipio' => 'nullable|string',
            'factura_ids' => 'nullable|array',
            'factura_ids.*' => 'exists:facturas,id',
            'evidencia' => 'nullable|array',
            'evidencia.*' => 'required_if:subtipo,Entregada,true|required_if:subtipo,No entregada,true|file|max:20480',
            'fecha_evento' => 'nullable|date',
        ]);

        try {
            // Se llama al mismo servicio para manejar la lógica
            $eventService->handle($guia, $validatedData, $request);
        } catch (\Exception $e) {
            Log::error("Error al guardar evento desde monitoreo: " . $e->getMessage());
            // Devuelve una respuesta JSON para AJAX
            return response()->json(['success' => false, 'message' => 'Ocurrió un error al guardar el evento.'], 500);
        }

        // Devuelve una respuesta JSON para AJAX
        return response()->json(['success' => true, 'message' => 'Evento registrado exitosamente.']);
    }

    private function buildFilteredQuery(Request $request)
    {
        $query = Guia::query()
            ->with(['ruta', 'eventos' => fn($q) => $q->orderBy('fecha_evento', 'desc'), 'facturas'])
            ->whereNotNull('ruta_id');

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('guia', 'like', $searchTerm)->orWhere('operador', 'like', $searchTerm)->orWhereHas('ruta', fn($rutaQuery) => $rutaQuery->where('nombre', 'like', $searchTerm));
            });
        }
        
        if ($request->filled('estatus')) { 
            $query->where('estatus', $request->estatus); 
        } else if (!$request->filled('report_statuses')) { 
            $query->whereNotNull('fecha_inicio_ruta')->where('estatus', '!=', 'Completada'); 
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
             $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }
        
        if ($request->filled('report_start_date') && $request->filled('report_end_date')) {
             $query->whereBetween('fecha_asignacion', [$request->report_start_date, $request->report_end_date]);
        }

        if ($request->filled('report_statuses')) {
            $statuses = is_array($request->report_statuses) ? $request->report_statuses : explode(',', $request->report_statuses);
            if (!empty($statuses[0])) {
                $query->whereIn('estatus', $statuses);
            }
        }
        
        if ($request->filled('region')) { $query->whereHas('ruta', fn($q) => $q->where('region', $request->region)); }

        return $query;
    }

    private function formatGuiasToJson($guias)
    {
        return $guias->keyBy('id')->map(function ($guia) {
            return [
                'id' => $guia->id, 'guia' => $guia->guia, 'operador' => $guia->operador,
                'placas' => $guia->placas, 'ruta_nombre' => $guia->ruta->nombre ?? 'N/A',
                'facturas' => $guia->facturas, 'estatus' => $guia->estatus,
                'fecha_inicio_ruta' => $guia->fecha_inicio_ruta?->format('d/m/Y H:i A'),
                'fecha_fin_ruta' => $guia->fecha_fin_ruta?->format('d/m/Y H:i A'),
                'eventos' => $guia->eventos->map(function ($evento) {
                    return [
                        'id' => $evento->id, 'lat' => (float)$evento->latitud, 'lng' => (float)$evento->longitud,
                        'tipo' => $evento->tipo, 'subtipo' => $evento->subtipo, 'nota' => $evento->nota,
                        'url_evidencia' => $evento->url_evidencia,
                        'fecha_evento' => $evento->fecha_evento->format('d/m/Y H:i A'),
                        'factura_id' => $evento->factura_id,
                    ];
                }),
                'paradas' => $guia->ruta ? $guia->ruta->paradas->map(function ($parada) {
                    return ['lat' => (float)$parada->latitud, 'lng' => (float)$parada->longitud, 'nombre_lugar' => $parada->nombre_lugar];
                }) : [],
            ];
        });
    }
}