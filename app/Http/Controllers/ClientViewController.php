<?php

namespace App\Http\Controllers;

use App\Models\Tms\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientViewController extends Controller
{
    /**
     * Muestra la vista de seguimiento para una o varias facturas.
     */
    public function index($invoice_numbers_str = null)
    {
        $invoicesData = [];

        if ($invoice_numbers_str) {
            $invoice_numbers = array_map('trim', explode(',', $invoice_numbers_str));

            // ========================================================== //
            // INICIO DEL CAMBIO: Consulta simplificada para evitar el error
            // ========================================================== //
            $invoices = Invoice::whereIn('invoice_number', $invoice_numbers)
                ->with('shipment.route.events.media') // Cargamos todo: ruta, eventos y sus fotos
                ->get();
            // ========================================================== //
            // FIN DEL CAMBIO
            // ========================================================== //

            if ($invoices->isEmpty()) {
                return redirect()->route('tracking.index')->with('error', 'No se encontraron facturas con los números proporcionados.');
            }

            foreach ($invoices as $invoice) {
                if (!$invoice->shipment || !$invoice->shipment->route) {
                    continue;
                }

                $route = $invoice->shipment->route;
                // Ordenamos los eventos en PHP para encontrar el más reciente
                $sortedEvents = $route->events->sortByDesc('created_at');
                $lastEvent = $sortedEvents->first();
                
                // Extraer las evidencias de entrega (esta lógica no cambia)
                $evidence = [];
                foreach ($route->events as $event) {
                    if (in_array($event->event_type, ['Entrega', 'No Entregado']) && $event->media->isNotEmpty()) {
                        foreach ($event->media as $mediaItem) {
                            $evidence[] = Storage::disk('s3')->url($mediaItem->file_path);
                        }
                    }
                }

                $invoicesData[] = [
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_status' => $invoice->status,
                    'box_quantity' => $invoice->box_quantity,
                    'bottle_quantity' => $invoice->bottle_quantity,
                    'shipment_status' => $invoice->shipment->status,
                    'origin' => $invoice->shipment->origin,
                    'destination' => $invoice->shipment->destination_type,
                    'route_status' => $route->status,
                    'route_name' => $route->name,
                    'polyline' => json_decode($route->polyline),
                    'last_event' => $lastEvent ? [
                        'type' => $lastEvent->event_type,
                        'latitude' => $lastEvent->latitude,
                        'longitude' => $lastEvent->longitude,
                        'timestamp' => $lastEvent->created_at->format('d/m/Y H:i'),
                    ] : null,
                    'evidence' => $evidence,
                ];
            }
        }

        return view('tms.client-tracking', ['invoicesData' => $invoicesData]);
    }

    /**
     * Procesa la búsqueda y redirige.
     */
    public function search(Request $request)
    {
        $request->validate(['invoice_number' => 'required|string|max:255']);
        return redirect()->route('tracking.index', ['invoice_number' => $request->invoice_number]);
    }
}