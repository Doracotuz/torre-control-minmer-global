<?php

namespace App\Http\Controllers\Rutas;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use App\Models\CsOrder;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function index(Request $request)
    {
        $results = collect();
        $notFoundNumbers = [];
        $searchQuery = $request->input('facturas');

        if ($searchQuery) {
            $numerosFactura = array_map('trim', explode(',', $searchQuery));
        $foundFacturas = Factura::whereIn('numero_factura', $numerosFactura)
            ->with(['eventos', 'csPlanning.order.updater'])
            ->get();
        
        $foundFacturas->each(function ($item) { $item->source = 'factura'; });

        $foundNumerosInFacturas = $foundFacturas->pluck('numero_factura')->toArray();
        $missingNumeros = array_diff($numerosFactura, $foundNumerosInFacturas);
        
        $foundOrders = collect();
        if (!empty($missingNumeros)) {
            $foundOrders = CsOrder::whereIn('invoice_number', $missingNumeros)
                                  ->with('updater')
                                  ->get();
            $foundOrders->each(function ($item) { $item->source = 'order'; });
        }

        $results = $foundFacturas->concat($foundOrders);

        if ($results->isNotEmpty()) {
            $allFoundNumbers = $results->map(function ($item) {
                return $item->source === 'factura' ? $item->numero_factura : $item->invoice_number;
            })->all();

            $notFoundNumbers = array_diff($numerosFactura, $allFoundNumbers);
        } else {
            $notFoundNumbers = $numerosFactura;
        }

    }

    return view('rutas.tracking.index', [
        'results' => $results,
        'notFoundNumbers' => $notFoundNumbers,
        'searchQuery' => $searchQuery,
        'googleMapsApiKey' => config('app.Maps_api_key')
    ]);
}

}