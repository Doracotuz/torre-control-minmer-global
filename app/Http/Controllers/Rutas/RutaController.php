<?php

namespace App\Http\Controllers\Rutas;

use App\Http\Controllers\Controller;
use App\Models\Ruta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; 

class RutaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    // Usamos with('paradas') para cargar las paradas de cada ruta eficientemente
    $query = Ruta::query()->with('paradas')->withCount('paradas');

    // Búsqueda y filtros (sin cambios)
    if ($request->filled('search')) {
        $query->where('nombre', 'like', '%' . $request->search . '%');
    }
    if ($request->filled('tipo_ruta')) {
        $query->where('tipo_ruta', $request->tipo_ruta);
    }
    if ($request->filled('region')) {
        $query->where('region', 'like', '%' . $request->region . '%');
    }

    $rutas = $query->orderBy('created_at', 'desc')->paginate(10); // Bajar a 10 para mejor visualización

    // Preparamos los datos de las rutas de la página actual para pasarlos a JavaScript
    $rutasJson = $rutas->mapWithKeys(function ($ruta) {
        return [$ruta->id => $ruta->paradas->map(function ($parada) {
            return ['lat' => (float)$parada->latitud, 'lng' => (float)$parada->longitud];
        })];
    })->toJson();

    $googleMapsApiKey = config('app.Maps_api_key');

    return view('rutas.plantillas.index', compact('rutas', 'rutasJson', 'googleMapsApiKey'));
}

    /**
     * Show the form for creating a new resource.
     * (Lo haremos en el siguiente paso)
     */
    public function create()
    {
        // Pasamos la clave de API de Google Maps a la vista de forma segura
        $googleMapsApiKey = config('app.Maps_api_key');
        return view('rutas.plantillas.create', compact('googleMapsApiKey'));
    }

    /**
     * Store a newly created resource in storage.
     * (Lo haremos en el siguiente paso)
     */
    public function store(Request $request)
    {
        // 1. Validación de los datos
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'tipo_ruta' => 'required|in:Entrega,Traslado,Importacion',
            'distancia_total_km' => 'required|numeric|min:0',
            'paradas' => 'required|array|min:2',
            'paradas.*.nombre_lugar' => 'required|string|max:255',
            'paradas.*.latitud' => 'required|numeric',
            'paradas.*.longitud' => 'required|numeric',
        ]);

        try {
            // 2. Iniciar una transacción
            DB::beginTransaction();

            // 3. Crear la Ruta principal
            $ruta = Ruta::create([
                'nombre' => $validatedData['nombre'],
                'region' => $validatedData['region'],
                'tipo_ruta' => $validatedData['tipo_ruta'],
                'distancia_total_km' => $validatedData['distancia_total_km'],
                'user_id' => Auth::id(),
                'area_id' => Auth::user()->area_id, // Asigna el area del admin
            ]);

            // 4. Crear las Paradas asociadas
            foreach ($validatedData['paradas'] as $index => $paradaData) {
                $ruta->paradas()->create([
                    'secuencia' => $index + 1, // La secuencia es el orden en el array
                    'nombre_lugar' => $paradaData['nombre_lugar'],
                    'latitud' => $paradaData['latitud'],
                    'longitud' => $paradaData['longitud'],
                    // La distancia entre paradas se podría calcular aquí si fuera necesario
                ]);
            }

            // 5. Si todo salió bien, confirmar la transacción
            DB::commit();

            return redirect()->route('rutas.plantillas.index')
                             ->with('success', "La ruta '{$ruta->nombre}' ha sido creada exitosamente.");

        } catch (\Exception $e) {
            // 6. Si algo falla, revertir todos los cambios
            DB::rollBack();

            // Opcional: Registrar el error para depuración
            Log::error("Error al crear la ruta: " . $e->getMessage());

            return redirect()->back()
                             ->with('error', 'Ocurrió un error al crear la ruta. Por favor, inténtalo de nuevo.')
                             ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Ruta $ruta)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     * (Lo haremos más adelante)
     */
    public function edit(Ruta $ruta)
    {
        $ruta->load('paradas');
        $googleMapsApiKey = config('app.Maps_api_key');

        // Preparamos el array de paradas aquí en el controlador
        $initialParadas = $ruta->paradas->map(function($parada) {
            return [
                'lat' => (float) $parada->latitud,
                'lng' => (float) $parada->longitud,
                'nombre' => $parada->nombre_lugar,
            ];
        });

        // Pasamos la nueva variable '$initialParadas' a la vista
        return view('rutas.plantillas.edit', compact('ruta', 'googleMapsApiKey', 'initialParadas'));
    }

    /**
     * Update the specified resource in storage.
     * (Lo haremos más adelante)
     */
    public function update(Request $request, Ruta $ruta)
    {
        // 1. Validación (similar a store)
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'tipo_ruta' => 'required|in:Entrega,Traslado,Importacion',
            'distancia_total_km' => 'required|numeric|min:0',
            'paradas' => 'required|array|min:2',
            'paradas.*.nombre_lugar' => 'required|string|max:255',
            'paradas.*.latitud' => 'required|numeric',
            'paradas.*.longitud' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            // 2. Actualizar los datos de la ruta principal
            $ruta->update([
                'nombre' => $validatedData['nombre'],
                'region' => $validatedData['region'],
                'tipo_ruta' => $validatedData['tipo_ruta'],
                'distancia_total_km' => $validatedData['distancia_total_km'],
            ]);

            // 3. Borrar todas las paradas anteriores
            $ruta->paradas()->delete();

            // 4. Crear las nuevas paradas
            foreach ($validatedData['paradas'] as $index => $paradaData) {
                $ruta->paradas()->create([
                    'secuencia' => $index + 1,
                    'nombre_lugar' => $paradaData['nombre_lugar'],
                    'latitud' => $paradaData['latitud'],
                    'longitud' => $paradaData['longitud'],
                ]);
            }

            DB::commit();

            return redirect()->route('rutas.plantillas.index')
                            ->with('success', "La ruta '{$ruta->nombre}' ha sido actualizada exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al actualizar la ruta: " . $e->getMessage());
            return redirect()->back()
                            ->with('error', 'Ocurrió un error al actualizar la ruta.')
                            ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ruta $ruta)
    {
        $ruta->delete();

        return redirect()->route('rutas.plantillas.index')
                         ->with('success', 'Ruta eliminada correctamente.');
    }

    public function duplicate(Request $request, Ruta $ruta)
    {
        $request->validate(['new_name' => 'required|string|max:255']);

        try {
            DB::beginTransaction();

            // 1. Replica el modelo de la ruta
            $newRuta = $ruta->replicate();

            // 2. Asigna el nuevo nombre y lo guarda
            $newRuta->nombre = $request->input('new_name');
            $newRuta->save();

            // 3. Replica cada parada y la asocia con la nueva ruta
            foreach ($ruta->paradas as $parada) {
                $newParada = $parada->replicate();
                $newParada->ruta_id = $newRuta->id;
                $newParada->save();
            }

            DB::commit();

            return redirect()->route('rutas.plantillas.index')
                             ->with('success', "La ruta '{$ruta->nombre}' ha sido duplicada como '{$newRuta->nombre}'.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al duplicar la ruta: " . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al duplicar la ruta.');
        }
    }

    public function exportCsv(Request $request)
    {
        // 1. Reutilizamos la misma lógica de consulta que en el método index()
        $query = Ruta::query()->with('paradas'); // Eager load paradas

        if ($request->filled('search')) {
            $query->where('nombre', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('tipo_ruta')) {
            $query->where('tipo_ruta', $request->tipo_ruta);
        }
        if ($request->filled('region')) {
            $query->where('region', 'like', '%' . $request->region . '%');
        }

        $rutas = $query->orderBy('created_at', 'desc')->get();
        
        $fileName = "export_rutas_" . date('Y-m-d_H-i-s') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($rutas) {
            $file = fopen('php://output', 'w');

            // 2. Definimos los encabezados del CSV
            fputcsv($file, [
                'ID Ruta', 'Nombre Ruta', 'Region', 'Tipo', 'Distancia Total (Km)',
                'Secuencia Parada', 'Nombre Parada', 'Latitud', 'Longitud'
            ]);

            // 3. Iteramos sobre los resultados y escribimos en el archivo
            foreach ($rutas as $ruta) {
                if ($ruta->paradas->count() > 0) {
                    foreach($ruta->paradas as $parada) {
                        fputcsv($file, [
                            $ruta->id,
                            $ruta->nombre,
                            $ruta->region,
                            $ruta->tipo_ruta,
                            $ruta->distancia_total_km,
                            $parada->secuencia,
                            $parada->nombre_lugar,
                            $parada->latitud,
                            $parada->longitud
                        ]);
                    }
                } else {
                    // Si una ruta no tiene paradas, la exportamos igualmente
                    fputcsv($file, [
                        $ruta->id, $ruta->nombre, $ruta->region, $ruta->tipo_ruta, $ruta->distancia_total_km,
                        'N/A', 'N/A', 'N/A', 'N/A'
                    ]);
                }
            }
            fclose($file);
        };

        // 4. Retornamos la respuesta para iniciar la descarga
        return response()->stream($callback, 200, $headers);
    }

    public function search(Request $request)
    {
        $query = Ruta::query();
        if ($request->filled('search')) {
            $query->where('nombre', 'like', '%' . $request->search . '%');
        }
        $rutas = $query->select('id', 'nombre', 'region', 'distancia_total_km')
                       ->orderBy('nombre')
                       ->get();
        return response()->json($rutas);
    }

}