<?php

namespace App\Http\Controllers;

use App\Models\Tms\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientViewController extends Controller
{
    public function index($invoice_numbers_str = null)
    {
        $invoicesData = [];

        if ($invoice_numbers_str) {
            $invoice_numbers = array_map('trim', explode(',', $invoice_numbers_str));
            $invoices = Invoice::whereIn('invoice_number', $invoice_numbers)
                ->with('shipment.route.events.media')
                ->get();

            if ($invoices->isEmpty()) {
                return redirect()->route('tracking.index')->with('error', 'No se encontraron facturas con los números proporcionados.');
            }

            foreach ($invoices as $invoice) {
                if (!$invoice->shipment || !$invoice->shipment->route) continue;

                $route = $invoice->shipment->route;
                $lastEvent = $route->events->sortByDesc('created_at')->first();
                
                // ===================== CAMBIO 1: LÓGICA DE EVIDENCIAS Y ESTATUS =====================
                
                // Buscamos el evento de entrega específico para ESTA factura
                $deliveryEvent = $route->events->first(function ($event) use ($invoice) {
                    return in_array($event->event_type, ['Entrega', 'No Entregado']) && 
                           isset($event->notes) && 
                           str_contains($event->notes, $invoice->invoice_number);
                });

                // Extraemos las evidencias solo de ese evento específico
                $evidence = [];
                if ($deliveryEvent && $deliveryEvent->media->isNotEmpty()) {
                    foreach ($deliveryEvent->media as $mediaItem) {
                        $evidence[] = Storage::disk('s3')->url($mediaItem->file_path);
                    }
                }
                
                // Determinamos el estatus a mostrar de forma más inteligente
                $displayShipmentStatus = $invoice->shipment->status; // Estatus por defecto
                if ($lastEvent) {
                    if ($deliveryEvent && $deliveryEvent->event_type === 'Entrega') {
                        $displayShipmentStatus = 'Entregado';
                    } elseif ($deliveryEvent && $deliveryEvent->event_type === 'No Entregado') {
                        $displayShipmentStatus = 'Incidencia en Entrega';
                    }
                    // Si no hay evento de entrega específico, se queda con el estatus del envío ('En transito', 'Asignado', etc.)
                }

                // Lógica de truncado de la ruta (ya estaba correcta)
                $fullPolyline = json_decode($route->polyline, true);
                $truncatedPolyline = $fullPolyline;
                if ($lastEvent && is_array($fullPolyline) && !empty($fullPolyline)) {
                    $targetPoint = ['lat' => $lastEvent->latitude, 'lng' => $lastEvent->longitude];
                    $truncatedPolyline = $this->_truncatePolylineToPoint($fullPolyline, $targetPoint);
                }
                
                // ===================== FIN DEL CAMBIO =====================

                $invoicesData[] = [
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_status' => $invoice->shipment->status === 'En transito' ? 'En tránsito' : $invoice->status,
                    'box_quantity' => $invoice->box_quantity,
                    'bottle_quantity' => $invoice->bottle_quantity,
                    'shipment_status' => $displayShipmentStatus, // <-- Usamos la nueva variable
                    'origin' => $invoice->shipment->origin,
                    'destination' => $invoice->shipment->destination_type,
                    'route_status' => $route->status,
                    'route_name' => $route->name,
                    'polyline' => $truncatedPolyline,
                    'last_event' => $lastEvent ? [
                        'type' => $lastEvent->event_type,
                        'latitude' => $lastEvent->latitude,
                        'longitude' => $lastEvent->longitude,
                        'timestamp' => $lastEvent->created_at->format('d/m/Y H:i'),
                    ] : null,
                    'evidence' => $evidence, // <-- Usamos las evidencias filtradas
                ];
            }
        }

        return view('tms.client-tracking', ['invoicesData' => $invoicesData]);
    }

    public function search(Request $request)
    {
        $request->validate(['invoice_number' => 'required|string|max:255']);
        return redirect()->route('tracking.index', ['invoice_number' => $request->invoice_number]);
    }

    private function _truncatePolylineToPoint(array $polyline, array $targetPoint)
    {
        $closestIndex = 0;
        $minDistance = PHP_INT_MAX;
        foreach ($polyline as $index => $point) {
            if (!isset($point['lat']) || !isset($point['lng'])) continue;
            $distance = pow($point['lat'] - $targetPoint['lat'], 2) + pow($point['lng'] - $targetPoint['lng'], 2);
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $closestIndex = $index;
            }
        }
        return array_slice($polyline, 0, $closestIndex + 1);
    }
}