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
            // Convertir la cadena de facturas separadas por coma en un array
            $numerosFactura = array_map('trim', explode(',', $searchQuery));

            // Buscar las facturas y cargar solo los eventos de tipo "Entrega"
            $facturas = Factura::whereIn('numero_factura', $numerosFactura)
                ->with(['eventos' => function ($query) {
                    $query->where('tipo', 'Entrega')->orderBy('fecha_evento', 'desc');
                }])
                ->get();
        }

        return view('rutas.tracking.index', [
            'facturas' => $facturas,
            'searchQuery' => $searchQuery,
            'googleMapsApiKey' => config('app.Maps_api_key')
        ]);
    }
}