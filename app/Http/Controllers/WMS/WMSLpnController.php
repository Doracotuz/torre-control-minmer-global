<?php
namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\WMS\PregeneratedLpn;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class WMSLpnController extends Controller
{
    public function index()
    {
        $unusedLpns = PregeneratedLpn::where('is_used', false)->count();
        return view('wms.lpns.index', compact('unusedLpns'));
    }

    public function generate(Request $request)
    {
        $request->validate(['quantity' => 'required|integer|min:1|max:100']);

        for ($i = 0; $i < $request->quantity; $i++) {
            PregeneratedLpn::create([
                'lpn' => 'LPN-' . strtoupper(uniqid())
            ]);
        }

        return back()->with('success', $request->quantity . ' nuevos LPNs han sido generados.');
    }

    public function printPdf(Request $request)
    {
        $request->validate(['quantity' => 'required|integer|min:1|max:100']);

        $lpns = PregeneratedLpn::where('is_used', false)
                            ->latest()
                            ->limit($request->quantity)
                            ->get();

        if ($lpns->isEmpty()) {
            return back()->with('error', 'No hay LPNs sin usar para imprimir.');
        }

        // Ya no necesitamos la lógica para cargar el logo.
        // Pasamos directamente los LPNs a la vista.
        $pdf = Pdf::loadView('wms.lpns.pdf', compact('lpns'));

        // Establecemos el tamaño de página personalizado a 70mm x 50mm.

        return $pdf->stream('lpn-etiquetas.pdf');
    }
}