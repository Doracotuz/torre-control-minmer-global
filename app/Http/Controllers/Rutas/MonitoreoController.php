<?php

namespace App\Http\Controllers\Rutas;


use App\Http\Controllers\Controller;
use App\Models\Guia;
use App\Models\Evento; // <-- AÑADE ESTA LÍNEA
use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

}