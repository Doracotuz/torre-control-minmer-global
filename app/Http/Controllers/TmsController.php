<?php

namespace App\Http\Controllers;

use App\Models\Tms\Route;
use App\Models\Tms\Stop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TmsController extends Controller
{
    /**
     * Muestra el dashboard principal del TMS.
     */
    public function index()
    {
        return view('tms.index');
    }

    /**
     * Muestra la vista para ver las rutas en el mapa.
     */
    public function viewRoutes()
    {
        // Lógica para obtener rutas y pasarlas a la vista
        return view('tms.view-routes');
    }

    /**
     * Muestra el formulario para crear una nueva ruta.
     */
    public function createRoute()
    {
        // Lógica para la vista de creación de rutas
        return view('tms.create-route');
    }

        public function storeRoute(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'polyline' => 'required|string',
            'distance' => 'required|numeric',
            'duration' => 'required|numeric',
            'stops' => 'required|array|min:2',
            'stops.*.name' => 'required|string',
            'stops.*.lat' => 'required|numeric',
            'stops.*.lng' => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            // 1. Crear la Ruta
            $route = Route::create([
                'name' => $request->name,
                'polyline' => $request->polyline,
                'total_distance_km' => $request->distance,
                'total_duration_min' => $request->duration,
                'status' => 'Planeada',
            ]);

            // 2. Crear las Paradas (Stops)
            foreach ($request->stops as $index => $stopData) {
                Stop::create([
                    'route_id' => $route->id,
                    'name' => $stopData['name'],
                    'latitude' => $stopData['lat'],
                    'longitude' => $stopData['lng'],
                    'order' => $index + 1,
                ]);
            }

            DB::commit();

            // Respuesta exitosa
            return response()->json([
                'success' => true,
                'message' => 'Ruta creada exitosamente.',
                'route_id' => $route->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear la ruta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al guardar la ruta. Por favor, inténtelo de nuevo.'
            ], 500);
        }
    }

    /**
     * Muestra la vista para asignar embarques a las rutas.
     */
    public function assignRoutes()
    {
        // Lógica para la vista de asignación
        return view('tms.assign-routes');
    }
}