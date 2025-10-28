<?php

namespace App\Http\Controllers\Rutas;

use App\Http\Controllers\Controller;
use App\Models\Ruta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RutaController extends Controller
{
public function index(Request $request)
{
    $query = Ruta::query()->with('paradas')->withCount('paradas');

    if ($request->filled('search')) {
        $query->where('nombre', 'like', '%' . $request->search . '%');
    }
    if ($request->filled('tipo_ruta')) {
        $query->where('tipo_ruta', $request->tipo_ruta);
    }
    if ($request->filled('region')) {
        $query->where('region', 'like', '%' . $request->region . '%');
    }

    $rutas = $query->orderBy('created_at', 'desc')->paginate(10);

    $rutasJson = $rutas->mapWithKeys(function ($ruta) {
        return [$ruta->id => $ruta->paradas->map(function ($parada) {
            return ['lat' => (float)$parada->latitud, 'lng' => (float)$parada->longitud];
        })];
    })->toJson();

    $googleMapsApiKey = config('app.Maps_api_key');

    return view('rutas.plantillas.index', compact('rutas', 'rutasJson', 'googleMapsApiKey'));
}

    public function create()
    {
        $googleMapsApiKey = config('app.Maps_api_key');
        return view('rutas.plantillas.create', compact('googleMapsApiKey'));
    }

    public function store(Request $request)
    {
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

            $ruta = Ruta::create([
                'nombre' => $validatedData['nombre'],
                'region' => $validatedData['region'],
                'tipo_ruta' => $validatedData['tipo_ruta'],
                'distancia_total_km' => $validatedData['distancia_total_km'],
                'user_id' => Auth::id(),
                'area_id' => Auth::user()->area_id,
            ]);

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
                             ->with('success', "La ruta '{$ruta->nombre}' ha sido creada exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Error al crear la ruta: " . $e->getMessage());

            return redirect()->back()
                             ->with('error', 'Ocurrió un error al crear la ruta. Por favor, inténtalo de nuevo.')
                             ->withInput();
        }
    }

    public function show(Ruta $ruta)
    {
    }

    public function edit(Ruta $ruta)
    {
        $ruta->load('paradas');
        $googleMapsApiKey = config('app.Maps_api_key');

        $initialParadas = $ruta->paradas->map(function($parada) {
            return [
                'lat' => (float) $parada->latitud,
                'lng' => (float) $parada->longitud,
                'nombre' => $parada->nombre_lugar,
            ];
        });

        return view('rutas.plantillas.edit', compact('ruta', 'googleMapsApiKey', 'initialParadas'));
    }

    public function update(Request $request, Ruta $ruta)
    {
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

            $ruta->update([
                'nombre' => $validatedData['nombre'],
                'region' => $validatedData['region'],
                'tipo_ruta' => $validatedData['tipo_ruta'],
                'distancia_total_km' => $validatedData['distancia_total_km'],
            ]);

            $ruta->paradas()->delete();

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

            $newRuta = $ruta->replicate();

            $newRuta->nombre = $request->input('new_name');
            $newRuta->save();

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
        $query = Ruta::query()->with('paradas');

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

            fputcsv($file, [
                'ID Ruta', 'Nombre Ruta', 'Region', 'Tipo', 'Distancia Total (Km)',
                'Secuencia Parada', 'Nombre Parada', 'Latitud', 'Longitud'
            ]);

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
                    fputcsv($file, [
                        $ruta->id, $ruta->nombre, $ruta->region, $ruta->tipo_ruta, $ruta->distancia_total_km,
                        'N/A', 'N/A', 'N/A', 'N/A'
                    ]);
                }
            }
            fclose($file);
        };

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

    public function filter(Request $request)
    {
        $query = Ruta::query()->with('paradas')->withCount('paradas');

        if ($request->filled('search')) {
            $query->where('nombre', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('tipo_ruta')) {
            $query->where('tipo_ruta', $request->tipo_ruta);
        }
        if ($request->filled('region')) {
            $query->where('region', 'like', '%' . $request->region . '%');
        }

        $rutas = $query->orderBy('created_at', 'desc')->paginate(10);

        $rutasJson = $rutas->mapWithKeys(function ($ruta) {
            return [$ruta->id => $ruta->paradas->map(function ($parada) {
                return ['lat' => (float)$parada->latitud, 'lng' => (float)$parada->longitud];
            })];
        })->toJson();

        $tableView = view('rutas.plantillas.partials.table', compact('rutas'))->render();

        return response()->json([
            'tableView' => $tableView,
            'rutasJson' => $rutasJson,
        ]);
    }
}