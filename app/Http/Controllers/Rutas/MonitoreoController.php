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

class MonitoreoController extends Controller
{
    /**
     * Muestra la vista de monitoreo de rutas.
     */
    public function index(Request $request)
    {
        $query = Guia::query()
            ->with(['ruta.paradas', 'eventos', 'facturas']);

        // Búsqueda
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('guia', 'like', $searchTerm)
                  ->orWhere('operador', 'like', $searchTerm)
                  ->orWhereHas('ruta', function ($rutaQuery) use ($searchTerm) {
                      $rutaQuery->where('nombre', 'like', $searchTerm);
                  });
            });
        }

        // --- LÓGICA DE FILTRADO CORREGIDA ---
        $estatusFilter = $request->input('estatus');
        if ($estatusFilter) {
            // Si el filtro tiene un valor (ej. 'Planeada'), lo usamos.
            $query->where('estatus', $estatusFilter);
        } elseif ($estatusFilter === null) {
            // Si el parámetro 'estatus' no existe en la URL (primera carga), usamos el default.
            $query->where('estatus', 'En Transito');
        }
        // Si estatus es "" (opción "Todos"), no se aplica ningún filtro de estatus.
        
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $guias = $query->withCount('facturas')->orderBy('updated_at', 'desc')->paginate(50);

        $guiasJson = $guias->keyBy('id')->map(function ($guia) {
            return [
                'id' => $guia->id,
                'guia' => $guia->guia,
                'operador' => $guia->operador,
                'placas' => $guia->placas,
                'ruta_nombre' => $guia->ruta->nombre ?? 'N/A',
                'facturas' => $guia->facturas,
                'estatus' => $guia->estatus, // Añadimos el estatus para el modal
                'fecha_inicio_ruta' => $guia->fecha_inicio_ruta?->format('d/m/Y H:i A'), // <-- AÑADIR
                'fecha_fin_ruta' => $guia->fecha_fin_ruta?->format('d/m/Y H:i A'),
                'eventos' => $guia->eventos->map(function ($evento) {
                    return [
                        'lat' => (float)$evento->latitud,
                        'lng' => (float)$evento->longitud,
                        'tipo' => $evento->tipo,
                        'subtipo' => $evento->subtipo,
                        'nota' => $evento->nota,
                        'url_evidencia' => $evento->url_evidencia,
                        'fecha_evento' => $evento->fecha_evento->format('d/m/Y H:i A'), // <-- FECHA AÑADIDA
                        'factura_id' => $evento->factura_id,
                    ];
                }),
                'paradas' => $guia->ruta ? $guia->ruta->paradas->map(function ($parada) {
                    return ['lat' => (float)$parada->latitud, 'lng' => (float)$parada->longitud ];
                }) : [],
            ];
        })->toJson();

        $googleMapsApiKey = config('app.Maps_api_key');

        return view('rutas.monitoreo.index', compact('guias', 'googleMapsApiKey', 'guiasJson'));
    }

    public function storeEvent(Request $request, Guia $guia)
    {
        $validatedData = $request->validate([
            'tipo' => 'required|in:Entrega,Notificacion,Incidencias',
            'subtipo' => 'required|string|max:255',
            'nota' => 'nullable|string',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'factura_id' => 'nullable|required_if:tipo,Entrega|exists:facturas,id',
            // --- VALIDACIÓN MEJORADA PARA MÚLTIPLES ARCHIVOS ---
            'evidencia' => 'nullable|array', // Ahora puede ser un array (o no venir)
            'evidencia.*' => 'file|max:51200', // max 50MB por archivo
        ]);

        // Validación de cantidad de fotos por tipo de evento
        if ($request->hasFile('evidencia')) {
            $fileCount = count($validatedData['evidencia']);
            if ($validatedData['tipo'] === 'Entrega' && $fileCount > 10) {
                return back()->with('error', 'Solo se permiten hasta 10 fotos para eventos de entrega.');
            }
            if ($validatedData['tipo'] === 'Notificacion' && $fileCount > 1) {
                return back()->with('error', 'Solo se permite 1 foto para eventos de notificación.');
            }
        }

        try {
            $paths = [];
            if ($request->hasFile('evidencia')) {
                $directory = $validatedData['tipo'] === 'Entrega' ? 'tms_evidencias' : 'tms_events';
                
                $facturaNumero = 'evento';
                if ($validatedData['tipo'] === 'Entrega') {
                    $factura = Factura::find($validatedData['factura_id']);
                    $facturaNumero = $factura->numero_factura;
                }

                foreach ($request->file('evidencia') as $index => $file) {
                    $extension = $file->getClientOriginalExtension();
                    $suffix = ($index > 0) ? '-' . ($index + 1) : '';
                    $fileName = "{$facturaNumero}{$suffix}.{$extension}";
                    
                    $paths[] = $file->storeAs($directory, $fileName, 's3');
                }
            }

            Evento::create([
                'guia_id' => $guia->id,
                'factura_id' => $validatedData['factura_id'] ?? null,
                'tipo' => $validatedData['tipo'],
                'subtipo' => $validatedData['subtipo'],
                'nota' => $validatedData['nota'] ?? $validatedData['subtipo'],
                'latitud' => $validatedData['latitud'],
                'longitud' => $validatedData['longitud'],
                'url_evidencia' => $paths, // Guardamos el array de rutas
                'fecha_evento' => now(),
            ]);

            if ($validatedData['tipo'] === 'Entrega') {
                $factura = Factura::find($validatedData['factura_id']);
                $factura->estatus_entrega = ($validatedData['subtipo'] === 'Factura Entregada') ? 'Entregada' : 'No Entregada';
                $factura->save();

                $guia->load('facturas');
                $conteoPendientes = $guia->facturas()->where('estatus_entrega', 'Pendiente')->count();

                if ($conteoPendientes === 0) {
                    $guia->estatus = 'Completada';
                    $guia->save();
                }
            }
            if ($guia->estatus == 'Planeada') {
                $guia->estatus = 'En Transito';
                $guia->save();
            }

        } catch (\Exception $e) {
            Log::error("Error al guardar evento: " . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al guardar el evento.');
        }

        return redirect()->route('rutas.monitoreo.index')->with('success', 'Evento registrado exitosamente.');
    }

    public function filter(Request $request)
    {
        $query = Guia::query()->with(['ruta.paradas', 'eventos', 'facturas']);

        // Búsqueda (sin cambios)
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('guia', 'like', $searchTerm)
                  ->orWhere('operador', 'like', $searchTerm)
                  ->orWhereHas('ruta', function ($rutaQuery) use ($searchTerm) {
                      $rutaQuery->where('nombre', 'like', $searchTerm);
                  });
            });
        }

        // --- LÓGICA DE FILTRADO CORREGIDA PARA AJAX ---
        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $guias = $query->withCount('facturas')->orderBy('updated_at', 'desc')->get();
        
        $paginator = $query->withCount('facturas')->orderBy('updated_at', 'desc')->paginate(20);

        $guiasJson = $guias->keyBy('id')->map(function ($guia) {
            return [
                'id' => $guia->id,
                'guia' => $guia->guia,
                'operador' => $guia->operador,
                'placas' => $guia->placas,
                'ruta_nombre' => $guia->ruta->nombre ?? 'N/A',
                'facturas' => $guia->facturas,
                'estatus' => $guia->estatus,
                'fecha_inicio_ruta' => $guia->fecha_inicio_ruta?->format('d/m/Y H:i A'),
                'fecha_fin_ruta' => $guia->fecha_fin_ruta?->format('d/m/Y H:i A'),
                'eventos' => $guia->eventos->map(function ($evento) {
                    return [
                        'lat' => (float)$evento->latitud, 'lng' => (float)$evento->longitud,
                        'tipo' => $evento->tipo, 'subtipo' => $evento->subtipo,
                        'nota' => $evento->nota, 'url_evidencia' => $evento->url_evidencia,
                        'fecha_evento' => $evento->fecha_evento->format('d/m/Y H:i A'),
                        'factura_id' => $evento->factura_id,
                    ];
                }),
                'paradas' => $guia->ruta ? $guia->ruta->paradas->map(function ($parada) {
                    return ['lat' => (float)$parada->latitud, 'lng' => (float)$parada->longitud, 'nombre_lugar' => $parada->nombre_lugar];
                }) : [],
            ];
        });

        // Devolvemos los datos en formato JSON
        return response()->json([
            'paginator' => $paginator,
            'guiasJson' => $guiasJson,
        ]);
    }

    public function getReportData(Request $request)
    {
        // Se reutiliza la misma lógica de consulta que en el método filter, pero sin paginación
        $query = Guia::query()->with(['facturas', 'eventos', 'ruta']);

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('guia', 'like', $searchTerm)
                  ->orWhere('operador', 'like', 'searchTerm');
            });
        }
        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('region')) {
            $query->whereHas('ruta', function ($q) use ($request) {
                $q->where('region', $request->region);
            });
        }

        // Obtenemos todos los resultados que coinciden con los filtros
        $guias = $query->get();
        
        // 1. PROCESAR DATOS PARA LA TABLA
        $tableData = [];
        foreach ($guias as $guia) {
            $llegadaCargaEvent = $guia->eventos->firstWhere('subtipo', 'Llegada a carga');
            $finCargaEvent = $guia->eventos->firstWhere('subtipo', 'Fin de carga');

            foreach ($guia->facturas as $factura) {

                $entregaEvent = $guia->eventos
                    ->where('tipo', 'Entrega')
                    ->where('factura_id', $factura->id)
                    ->sortBy('fecha_evento')
                    ->first();                
                $tableData[] = [
                    'fecha_carga' => $guia->fecha_asignacion ? \Carbon\Carbon::parse($guia->fecha_asignacion)->format('d/m/Y') : 'N/A',
                    'hora_planeada' => $guia->hora_planeada ?? 'N/A',
                    'hora_arribo' => $llegadaCargaEvent ? $llegadaCargaEvent->fecha_evento->format('d/m/Y h:i A') : 'N/A',
                    'inicio_ruta' => $guia->fecha_inicio_ruta ? $guia->fecha_inicio_ruta->format('d/m/Y h:i A') : 'N/A',
                    'operador' => $guia->operador,
                    'destino' => $factura->destino,
                    'factura' => $factura->numero_factura,
                    'estatus_f' => $entregaEvent ? $entregaEvent->subtipo : $factura->estatus_entrega,
                    'estatus_r' => $guia->estatus,
                    'entregada' => $entregaEvent && $entregaEvent->subtipo == 'Factura Entregada' ? $entregaEvent->fecha_evento->format('d/m/Y h:i A') : 'N/A',
                    'custodia' => $guia->custodia ?? 'N/A',
                ];
            }
        }

        // --- INICIA CORRECCIÓN ---
        // 2. PROCESAR DATOS PARA LOS GRÁFICOS DE FORMA SEGURA
        $todasLasFacturas = $guias->pluck('facturas')->flatten();

        // Gráfico 1: Guías por estatus
        $guiasPorEstatus = $guias->countBy('estatus');
        // Gráfico 2: Facturas por estatus de entrega
        $facturasPorEstatus = $todasLasFacturas->countBy('estatus_entrega');
        // Gráfico 3: Eventos por tipo
        $facturasPorRegion = $guias->filter(fn($guia) => $guia->ruta)
                                   ->groupBy('ruta.region')
                                   ->map(fn($guiasEnRegion) => $guiasEnRegion->sum(fn($g) => $g->facturas->count()))
                                   ->sortDesc();


        // Devolvemos todos los datos procesados en formato JSON
        return response()->json([
            'tableData' => $tableData,
            'charts' => [
                'guiasPorEstatus' => [
                    'labels' => $guiasPorEstatus->keys(),
                    'data' => $guiasPorEstatus->values(),
                ],
                'facturasPorEstatus' => [
                    'labels' => $facturasPorEstatus->keys(),
                    'data' => $facturasPorEstatus->values(),
                ],
                'facturasPorRegion' => [ // Nuevo dato para el gráfico
                    'labels' => $facturasPorRegion->keys(),
                    'data' => $facturasPorRegion->values(),
                ],
            ]
        ]);
    }


        public function startRoute(Request $request, Guia $guia)
    {
        // 1. Validar que la ruta esté en el estado correcto
        if ($guia->estatus !== 'Planeada') {
            return response()->json([
                'success' => false, 
                'message' => 'Esta ruta no puede ser iniciada porque no está en estatus "Planeada".'
            ], 409); // 409 Conflict
        }

        try {
            DB::beginTransaction();

            // 2. Actualizar el estado de la guía
            $guia->estatus = 'En Transito';
            $guia->fecha_inicio_ruta = now();
            $guia->save();

            // 3. Crear el evento de "Inicio de Ruta"
            // Se usan las coordenadas de la primera parada como ubicación del evento
            $firstStop = $guia->ruta ? $guia->ruta->paradas()->orderBy('secuencia')->first() : null;

            Evento::create([
                'guia_id' => $guia->id,
                'tipo' => 'Sistema',
                'subtipo' => 'Inicio de Ruta',
                'latitud' => $firstStop ? $firstStop->latitud : 0,
                'longitud' => $firstStop ? $firstStop->longitud : 0,
                'fecha_evento' => now(),
                'nota' => 'Ruta iniciada desde el panel de monitoreo.'
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Ruta iniciada exitosamente.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al iniciar ruta desde monitoreo: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Ocurrió un error en el servidor.'], 500);
        }
    }

    public function getAvailableRegions()
    {
        $regions = Ruta::query()
            ->select('region')
            ->whereNotNull('region')
            ->where('region', '!=', '')
            ->distinct()
            ->orderBy('region')
            ->pluck('region');
            
        return response()->json($regions);
    }

}