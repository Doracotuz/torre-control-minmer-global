<?php

namespace App\Http\Controllers;

use App\Models\Tms\Invoice;
use App\Models\Tms\RouteEvent;
use App\Models\Tms\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class OperatorViewController extends Controller
{
    /**
     * Muestra la vista inicial para que el operador ingrese la guía.
     */
    public function index($guide_number = null)
    {
        $shipments = null;
        $routeStatus = null;

        if ($guide_number) {
            $shipments = Shipment::where('guide_number', $guide_number)
                ->with('invoices', 'route')
                ->get();

            if ($shipments->isEmpty()) {
                // Si la guía no se encuentra, redirige al buscador con un error.
                return redirect()->route('operator.index')->with('error', 'No se encontraron embarques con esa guía.');
            }

            $routeStatus = $shipments->first()->route->status ?? 'No Asignada';
        }

        return view('tms.operator-view', [
            'shipments' => $shipments,
            'guide_number' => $guide_number,
            'routeStatus' => $routeStatus
        ]);
    }

    /**
     * Busca los embarques asociados a una guía y devuelve la vista de detalles.
     */
    public function findRoute(Request $request)
    {
        $request->validate(['guide_number' => 'required|string']);

        // Redirige a la ruta GET con el número de guía como parámetro.
        return redirect()->route('operator.index', ['guide_number' => $request->guide_number]);
    }

    /**
     * Marca una ruta como "En transito".
     */
    public function startRoute(Request $request)
    {
        $request->validate([
            'guide_number' => 'required|string|exists:tms_shipments,guide_number',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $shipments = Shipment::where('guide_number', $request->guide_number)->get();
        if ($shipments->isEmpty() || !$shipments->first()->route) {
            return back()->with('error', 'Esta guía no está asignada a una ruta.');
        }

        $route = $shipments->first()->route;

        DB::beginTransaction();
        try {
            $route->update(['status' => 'En transito']);
            Shipment::where('guide_number', $request->guide_number)->update(['status' => 'Transito']);

            RouteEvent::create([
                'route_id' => $route->id,
                'event_type' => 'Inicio de Ruta',
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            DB::commit();
            
            return back()->with('success', 'La ruta ha iniciado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al iniciar ruta: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al iniciar la ruta.');
        }
    }

    /**
     * Actualiza el estado de una factura y guarda las fotos.
     */
    public function updateInvoiceStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_id' => 'required|exists:tms_invoices,id',
            'status' => 'required|in:Entregado,No entregado',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photos.*' => 'image|mimes:jpeg,png,jpg|max:5120',
            'photos_gallery.*' => 'image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $files = $request->file('photos') ?? $request->file('photos_gallery');

        $invoice = Invoice::with('shipment.route')->findOrFail($request->invoice_id);
        $route = $invoice->shipment->route;

        DB::beginTransaction();
        try {
            // 1. Se actualiza el estado de la factura individual
            $invoice->update(['status' => $request->status]);

            // 2. Se crea el evento de entrega/no entrega
            $event = RouteEvent::create([
                'route_id' => $route->id,
                'event_type' => $request->status == 'Entregado' ? 'Entrega' : 'No Entregado',
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'notes' => 'Factura: ' . $invoice->invoice_number,
            ]);

            // 3. Se guardan las fotos de evidencia
            if ($files) {
                foreach ($files as $photo) {
                    $path = $photo->store('tms_evidencias', 's3');
                    $event->media()->create(['file_path' => $path]);
                }
            }

            // ==================================================================
            // INICIO DE LA NUEVA LÓGICA: Actualizar estado del Shipment
            // ==================================================================
            
            // 4. Se revisa si aún quedan facturas pendientes para ESTE embarque en particular.
            $shipment = $invoice->shipment;
            $pendingInvoicesForShipment = Invoice::where('shipment_id', $shipment->id)
                                                 ->where('status', 'Pendiente')
                                                 ->exists();

            // Si ya no hay facturas pendientes, el embarque se marca como Entregado.
            if (!$pendingInvoicesForShipment) {
                $shipment->update(['status' => 'Entregado']);
            }
            
            // ==================================================================
            // FIN DE LA NUEVA LÓGICA
            // ==================================================================

            // 5. Lógica existente: se revisa si la RUTA completa ha terminado
            $allInvoicesOnRouteCompleted = !Invoice::whereIn('shipment_id', $route->shipments->pluck('id'))
                                                   ->where('status', 'Pendiente')
                                                   ->exists();

            if ($allInvoicesOnRouteCompleted) {
                $route->update(['status' => 'Completada']);
            }            

            DB::commit();

            $message = 'Estado de la factura actualizado.';
            if ($allInvoicesOnRouteCompleted) {
                $message .= ' ¡Ruta completada!';
            } elseif (!$pendingInvoicesForShipment) {
                $message .= ' ¡Embarque completado!';
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar factura: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al actualizar la factura.');
        }
    }
    
    /**
     * Registra un evento en la ruta (pensión, altercado, etc.).
     */
    public function registerEvent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guide_number' => 'required|string|exists:tms_shipments,guide_number',
            'event_type' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            // Valida ambos posibles campos de foto
            'photo_camera' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'photo_gallery' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // Determina cuál de los dos inputs de archivo se usó
        $file = $request->file('photo_camera') ?? $request->file('photo_gallery');

        $shipment = Shipment::where('guide_number', $request->guide_number)->first();
        if (!$shipment || !$shipment->route) {
            return back()->with('error', 'Esta guía no está asignada a una ruta.');
        }

        DB::beginTransaction();
        try {
            $event = RouteEvent::create([
                'route_id' => $shipment->route_id,
                'event_type' => $request->event_type,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            if ($file) {
                // ========================================================== //
                // INICIO DEL CAMBIO: Guarda en la carpeta 'tms_events'
                // ========================================================== //
                $path = $file->store('tms_events', 's3');
                // ========================================================== //
                // FIN DEL CAMBIO
                // ========================================================== //
                $event->media()->create(['file_path' => $path]);
            }

            DB::commit();
            return back()->with('success', 'Evento registrado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar evento: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al registrar el evento.');
        }
    }
}