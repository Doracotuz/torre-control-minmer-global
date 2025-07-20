<?php

namespace App\Http\Controllers;

use App\Models\Tms\Invoice;
use App\Models\Tms\Route;
use App\Models\Tms\Shipment;
use App\Models\Tms\Stop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;
use App\Models\Tms\RouteEvent;

class TmsController extends Controller
{
    /**
     * Muestra el dashboard principal del TMS.
     */
    public function index(Request $request)
    {
        // Validar y establecer el rango de fechas
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        $stats = [
            'routes_in_transit' => Route::where('status', 'En transito')->count(),
            'shipments_to_assign' => Shipment::where('status', 'Por asignar')->count(),
            'routes_completed' => Route::where('status', 'Completada')->whereBetween('updated_at', [$startDate, $endDate])->count(),
            'total_shipments' => Shipment::whereBetween('created_at', [$startDate, $endDate])->count(),
            'incidents' => RouteEvent::where('event_type', 'Altercado')->whereBetween('created_at', [$startDate, $endDate])->count(),
        ];

        // Gráfico 1: Rutas por Estatus
        $routesByStatusChart = Route::select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status');

        // Gráfico 2: Rendimiento por Operador
        $operatorPerformanceChart = Shipment::where('status', 'Entregado')->whereBetween('updated_at', [$startDate, $endDate])
            ->whereNotNull('operator')->select('operator', DB::raw('count(distinct guide_number) as total'))
            ->groupBy('operator')->orderBy('total', 'desc')->limit(7)->pluck('total', 'operator');

        // Gráfico 3: Entregas por Origen
        $deliveriesByOriginChart = Shipment::whereIn('status', ['Entregado', 'Transito'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('origin', DB::raw('count(*) as total'))->groupBy('origin')->pluck('total', 'origin');

        // Gráfico 4: Actividad de Rutas (últimos 30 días)
        $activityRange = Carbon::now()->subDays(29);
        $createdRoutesActivity = Route::where('created_at', '>=', $activityRange)
            ->groupBy('date')->orderBy('date', 'ASC')
            ->get([DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count')]);
        
        $completedRoutesActivity = Route::where('status', 'Completada')->where('updated_at', '>=', $activityRange)
            ->groupBy('date')->orderBy('date', 'ASC')
            ->get([DB::raw('DATE(updated_at) as date'), DB::raw('COUNT(*) as count')]);

        // Gráfico 5: Tipos de Incidencias
        $incidentTypesChart = RouteEvent::where('event_type', '!=', 'Inicio de Ruta')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('event_type', DB::raw('count(*) as total'))->groupBy('event_type')->pluck('total', 'event_type');
            
        // Gráfico 6: Entregas por Destino
        $deliveriesByDestinationChart = Shipment::whereIn('status', ['Entregado', 'Transito'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('destination_type', DB::raw('count(*) as total'))
            ->groupBy('destination_type')->pluck('total', 'destination_type');
            
        // ==================================================================
        // INICIO DE LA MEJORA: Cálculo para el nuevo gráfico
        // ==================================================================
        // Gráfico 7: Volumen por Tipo de Embarque
        $shipmentTypesChart = Shipment::whereBetween('created_at', [$startDate, $endDate])
            ->select('type', DB::raw('count(*) as total'))
            ->groupBy('type')->pluck('total', 'type');
        // ==================================================================
        // FIN DE LA MEJORA
        // ==================================================================

        return view('tms.index', [
            'stats' => $stats,
            'routesByStatusChart' => $routesByStatusChart,
            'operatorPerformanceChart' => $operatorPerformanceChart,
            'deliveriesByOriginChart' => $deliveriesByOriginChart,
            'createdRoutesActivity' => $createdRoutesActivity,
            'completedRoutesActivity' => $completedRoutesActivity,
            'incidentTypesChart' => $incidentTypesChart,
            'deliveriesByDestinationChart' => $deliveriesByDestinationChart,
            'shipmentTypesChart' => $shipmentTypesChart, // <-- Pasar datos a la vista
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }


    /**
     * Muestra la vista para ver las rutas en el mapa.
     */

    public function viewRoutes()
    {
        // ==================================================================
        // INICIO DE LA MEJORA: Cargar los eventos y sus fotos (media)
        // ==================================================================
        $routes = Route::with(['stops', 'shipments.invoices', 'events.media'])
            ->whereNotNull('polyline')
            ->latest()
            ->get();

        $routesData = $routes->map(function ($route) {
            return [
                'id' => $route->id,
                'name' => $route->name,
                'status' => $route->status,
                'total_distance_km' => $route->total_distance_km,
                'total_duration_min' => $route->total_duration_min,
                'polyline' => json_decode($route->polyline),
                'stops' => $route->stops->map(function ($stop) {
                    return ['name' => $stop->name, 'latitude' => $stop->latitude, 'longitude' => $stop->longitude, 'order' => $stop->order];
                }),
                'shipments' => $route->shipments->map(function ($shipment) {
                    return ['guide_number' => $shipment->guide_number, 'operator' => $shipment->operator, 'license_plate' => $shipment->license_plate];
                }),
                'events' => $route->events->map(function ($event) {
                    return [
                        'type' => $event->event_type,
                        'latitude' => $event->latitude,
                        'longitude' => $event->longitude,
                        'notes' => $event->notes,
                        'timestamp' => $event->created_at->format('d/m/Y H:i'),
                        'photos' => $event->media->map(function ($media) {
                            // Genera la URL pública para la foto
                            return Storage::url($media->file_path);
                        })
                    ];
                })
            ];
        });

        return view('tms.view-routes', [
            'routesData' => $routesData->toJson()
        ]);
        // ==================================================================
        // FIN DE LA MEJORA
        // ==================================================================
    }

    /**
     * Muestra el formulario para crear una nueva ruta.
     */
    public function createRoute()
    {
        // Lógica para la vista de creación de rutas
        return view('tms.create-route', [
            'mapboxApiKey' => config('app.mapbox_api_key', env('MAPBOX_API_KEY'))
        ]);
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
    public function assignRoutes(Request $request)
    {
        // Obtener todos los embarques con sus facturas
        $shipments = Shipment::with('invoices')->latest()->get();
        
        // Obtener las rutas que están 'Planeada' o 'Asignada' para poder asignarles embarques
        $routes = Route::whereIn('status', ['Planeada', 'Asignada'])->get();

        // Si se pasa un route_id en la URL, lo preseleccionamos
        $selectedRouteId = $request->get('route_id');

        return view('tms.assign-routes', compact('shipments', 'routes', 'selectedRouteId'));
    }

    public function importShipments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $path = $request->file('csv_file')->getRealPath();
        $file = fopen($path, "r");

        DB::beginTransaction();
        try {
            $header = true;
            $lastShipment = null;

            while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {
                if ($header) {
                    $header = false;
                    continue;
                }

                // Asumiendo un formato de CSV específico. Ajustar si es necesario.
                // Ejemplo: tipo,guia,so,factura,cajas,botellas,destino_tipo,origen,pedimento,destino_dir,operador,placas
                $type = $data[0] ?? 'Entrega';
                $guide_number = $data[1] ?? null;

                if (!$guide_number) continue;

                if (!$lastShipment || $lastShipment->guide_number !== $guide_number) {
                    $lastShipment = Shipment::create([
                        'type' => $type,
                        'guide_number' => $guide_number,
                        'so_number' => $data[2] ?? null,
                        'origin' => $data[6] ?? null,            // <-- Corregido: Origen ahora es la columna 7
                        'destination_type' => $data[7] ?? null, // <-- Corregido: Destino ahora es la columna 8
                        'pedimento' => $data[8] ?? null,
                        'destination_address' => $data[9] ?? null, // <-- Añadido: Se agrega el campo que faltaba
                        'operator' => $data[10] ?? null,
                        'license_plate' => $data[11] ?? null,
                        'status' => 'Por asignar',
                    ]);
                }
                
                Invoice::create([
                    'shipment_id' => $lastShipment->id,
                    'invoice_number' => $data[3] ?? 'N/A',
                    'box_quantity' => $data[4] ?? 0,
                    'bottle_quantity' => $data[5] ?? 0,
                    'status' => 'Pendiente',
                ]);
            }

            DB::commit();
            return back()->with('success', 'Archivo CSV importado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al importar CSV: " . $e->getMessage());
            return back()->with('error', 'Ocurrió un error durante la importación. Por favor, revisa el formato del archivo. Error: ' . $e->getMessage());
        } finally {
            fclose($file);
        }
    }

    public function storeShipment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:Entrega,Importacion',
            'guide_number' => 'required|string|max:255',
            'so_number' => 'nullable|string|max:255',
            'origin' => 'required|string|max:255',
            'destination_type' => 'nullable|string|max:255',
            'pedimento' => 'nullable|string|max:255',
            'operator' => 'nullable|string|max:255',
            'license_plate' => 'nullable|string|max:255',
            'invoices' => 'required|array|min:1',
            'invoices.*.invoice_number' => 'required|string|max:255',
            'invoices.*.box_quantity' => 'nullable|integer|min:0',
            'invoices.*.bottle_quantity' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $shipment = Shipment::create([
                'type' => $request->type,
                'guide_number' => $request->guide_number,
                'so_number' => $request->so_number,
                'origin' => $request->origin,
                'destination_type' => $request->destination_type,
                'pedimento' => $request->pedimento,
                'operator' => $request->operator,
                'license_plate' => $request->license_plate,
                'status' => 'Por asignar',
            ]);

            foreach ($request->invoices as $invoiceData) {
                Invoice::create([
                    'shipment_id' => $shipment->id,
                    'invoice_number' => $invoiceData['invoice_number'],
                    'box_quantity' => $invoiceData['box_quantity'] ?? 0,
                    'bottle_quantity' => $invoiceData['bottle_quantity'] ?? 0,
                    'status' => 'Pendiente',
                ]);
            }

            DB::commit();
            return back()->with('success', 'Embarque registrado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al registrar embarque manual: " . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al registrar el embarque.');
        }
    }

    public function assignShipmentsToRoute(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'route_id' => 'required|exists:tms_routes,id',
            'shipment_ids' => 'required|array|min:1',
            'shipment_ids.*' => 'exists:tms_shipments,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Datos inválidos.'], 422);
        }

        DB::beginTransaction();
        try {
            $route = Route::find($request->route_id);

            // Asignar la ruta a los embarques
            Shipment::whereIn('id', $request->shipment_ids)
                ->where('status', 'Por asignar')
                ->update([
                    'route_id' => $route->id,
                    'status' => 'Transito' // El status del embarque ahora cambia a 'Transito'
                ]);

            // Actualizar el estado de la ruta a "Asignada"
            $route->update(['status' => 'Asignada']);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Embarques asignados correctamente a la ruta.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al asignar embarques: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Ocurrió un error en el servidor.'], 500);
        }
    }

    public function updateShipment(Request $request, Shipment $shipment)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:Entrega,Importacion',
            'guide_number' => 'required|string|max:255',
            'so_number' => 'nullable|string|max:255',
            'origin' => 'required|string|max:255',
            'destination_type' => 'nullable|string|max:255',
            'pedimento' => 'nullable|string|max:255',
            'operator' => 'nullable|string|max:255',
            'license_plate' => 'nullable|string|max:255',
            'invoices' => 'required|array|min:1',
            'invoices.*.invoice_number' => 'required|string|max:255',
            'invoices.*.box_quantity' => 'nullable|integer|min:0',
            'invoices.*.bottle_quantity' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            // Si la validación falla, redirige de vuelta con los errores y un flag para reabrir el modal.
            return back()->withErrors($validator)->withInput()->with('edit_id', $shipment->id);
        }

        DB::beginTransaction();
        try {
            // 1. Actualiza los datos del embarque principal
            $shipment->update($request->except(['_token', '_method', 'invoices']));

            // 2. Sincroniza las facturas: elimina las antiguas y crea las nuevas
            $shipment->invoices()->delete();
            foreach ($request->invoices as $invoiceData) {
                Invoice::create([
                    'shipment_id' => $shipment->id,
                    'invoice_number' => $invoiceData['invoice_number'],
                    'box_quantity' => $invoiceData['box_quantity'] ?? 0,
                    'bottle_quantity' => $invoiceData['bottle_quantity'] ?? 0,
                    'status' => 'Pendiente', // Se resetea el estado de las facturas al editar
                ]);
            }

            DB::commit();
            return back()->with('success', 'Embarque actualizado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al actualizar embarque: " . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al actualizar el embarque.');
        }
    }
    
    public function destroyRoute(Route $route)
    {
        // Medida de seguridad: no permitir eliminar rutas que están o estuvieron en tránsito.
        if (in_array($route->status, ['En transito', 'Completada'])) {
            return back()->with('error', 'No se puede eliminar una ruta que ya ha iniciado o ha sido completada.');
        }

        DB::beginTransaction();
        try {
            // Desvincular embarques de la ruta antes de eliminarla
            $route->shipments()->update(['route_id' => null, 'status' => 'Por asignar']);
            $route->delete();
            DB::commit();
            return back()->with('success', 'Ruta eliminada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al eliminar ruta: " . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al eliminar la ruta.');
        }
    }

    public function exportShipments()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=reporte_embarques.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $shipments = Shipment::with('invoices', 'route')->get();

        $callback = function() use ($shipments) {
            $file = fopen('php://output', 'w');

            // Añadir la fila de cabecera al CSV
            $header_row = [
                'Tipo Embarque',
                'Guia',
                'SO',
                'Pedimento',
                'Origen',
                'Destino',
                'Operador',
                'Placas',
                'Estatus Embarque',
                'Ruta Asignada',
                'Factura',
                'Cajas',
                'Botellas',
                'Estatus Factura'
            ];
            fputcsv($file, $header_row);

            // Añadir los datos de cada factura
            foreach ($shipments as $shipment) {
                if ($shipment->invoices->count() > 0) {
                    foreach ($shipment->invoices as $invoice) {
                        $row = [
                            $shipment->type,
                            $shipment->guide_number,
                            $shipment->so_number,
                            $shipment->pedimento,
                            $shipment->origin,
                            $shipment->destination_type,
                            $shipment->operator,
                            $shipment->license_plate,
                            $shipment->status,
                            $shipment->route->name ?? 'N/A',
                            $invoice->invoice_number,
                            $invoice->box_quantity,
                            $invoice->bottle_quantity,
                            $invoice->status,
                        ];
                        fputcsv($file, $row);
                    }
                } else {
                    // Si un embarque no tiene facturas, se añade una fila con los datos del embarque
                    $row = [
                        $shipment->type,
                        $shipment->guide_number,
                        $shipment->so_number,
                        $shipment->pedimento,
                        $shipment->origin,
                        $shipment->destination_type,
                        $shipment->operator,
                        $shipment->license_plate,
                        $shipment->status,
                        $shipment->route->name ?? 'N/A',
                        'N/A',
                        '0',
                        '0',
                        'N/A',
                    ];
                    fputcsv($file, $row);
                }
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function destroyShipment(Shipment $shipment)
    {
        // Solo se pueden eliminar embarques que no han sido asignados
        if ($shipment->status !== 'Por asignar') {
            return back()->with('error', 'Solo se pueden eliminar embarques con estatus "Por asignar".');
        }
        
        // Elimina las facturas asociadas y luego el embarque
        $shipment->invoices()->delete();
        $shipment->delete();

        return back()->with('success', 'Embarque eliminado exitosamente.');
    }


}