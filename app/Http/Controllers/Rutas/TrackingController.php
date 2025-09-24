<?php

namespace App\Http\Controllers\Rutas;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function index(Request $request)
    {
        $facturas = collect();
        $searchQuery = $request->input('facturas');

        if ($searchQuery) {
            $numerosFactura = array_map('trim', explode(',', $searchQuery));

            $facturas = Factura::whereIn('numero_factura', $numerosFactura)
                ->with([
                    'eventos' => function ($query) {
                        $query->where('tipo', 'Entrega')->orderBy('fecha_evento', 'desc');
                    },
                    'csPlanning.order.creator'
                ])
                ->get();
        }

        return view('rutas.tracking.index', [
            'facturas' => $facturas,
            'searchQuery' => $searchQuery,
            'googleMapsApiKey' => config('app.Maps_api_key')
        ]);
    }
}