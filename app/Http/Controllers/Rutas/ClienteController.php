<?php

namespace App\Http\Controllers\Rutas;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClienteController extends Controller
{
    public function index()
    {
        return view('public.cliente.index');
    }

    public function search(Request $request)
    {
        $request->validate([
            'facturas' => 'required|string',
        ]);

        $facturaNumbers = array_map('trim', explode(',', $request->input('facturas')));

        $results = [];
        foreach ($facturaNumbers as $num) {
            $facturas = Factura::where('numero_factura', $num)
                               ->with(['guia' => function($query) {
                                   $query->select('id', 'guia', 'estatus');
                               }, 'guia.eventos' => function($query) {
                                   $query->where('tipo', 'Entrega')->orderBy('fecha_evento', 'desc');
                               }])
                               ->get();

            if ($facturas->isEmpty()) {
                $results[] = [
                    'numero_factura' => $num,
                    'status' => 'No encontrada',
                    'message' => 'La factura no fue encontrada o no hay información de seguimiento disponible.',
                ];
                continue;
            }

            foreach ($facturas as $factura) {
                $latestDeliveryEvent = $factura->guia->eventos->first();
                
                $mapUrl = null;
                if ($latestDeliveryEvent && $latestDeliveryEvent->latitud && $latestDeliveryEvent->longitud) {
                    $mapUrl = "https://maps.googleapis.com/maps/api/staticmap?" .
                              "center={$latestDeliveryEvent->latitud},{$latestDeliveryEvent->longitud}" .
                              "&zoom=14&size=400x200&markers=color:orange%7C{$latestDeliveryEvent->latitud},{$latestDeliveryEvent->longitud}" .
                              "&key=" . config('app.Maps_api_key');
                }

                $results[] = [
                    'numero_factura' => $factura->numero_factura,
                    'guia' => $factura->guia->guia,
                    'estatus_guia' => $factura->guia->estatus,
                    'estatus_factura' => $factura->estatus_entrega,
                    'ultimo_evento_entrega' => $latestDeliveryEvent ? [
                        'subtipo' => $latestDeliveryEvent->subtipo,
                        'nota' => $latestDeliveryEvent->nota,
                        'fecha_evento' => $latestDeliveryEvent->fecha_evento->format('d/m/Y H:i A'),
                        'url_evidencia' => $latestDeliveryEvent->url_evidencia ? Storage::disk('s3')->url($latestDeliveryEvent->url_evidencia) : null,
                        'latitud' => $latestDeliveryEvent->latitud,
                        'longitud' => $latestDeliveryEvent->longitud,
                    ] : null,
                    'map_url' => $mapUrl,
                ];
            }
        }

        return response()->json($results);
    }
}