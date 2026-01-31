<?php
namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\WMS\PregeneratedLpn;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class WMSLpnController extends Controller
{
    public function index()
    {
        $totalLpns = PregeneratedLpn::count();
        $usedLpns = PregeneratedLpn::where('is_used', true)->count();
        $unusedLpns = $totalLpns - $usedLpns;

        return view('wms.lpns.index', compact('totalLpns', 'usedLpns', 'unusedLpns'));
    }

    public function generate(Request $request)
    {
        $request->validate(['quantity' => 'required|integer|min:1|max:100']);

        $generatedCount = 0;
        for ($i = 0; $i < $request->quantity; $i++) {

            do {
                $randomCode = strtoupper(bin2hex(random_bytes(5)));
                $lpn = 'LPN' . $randomCode;

                $exists = PregeneratedLpn::where('lpn', $lpn)->exists();

            } while ($exists);

            PregeneratedLpn::create(['lpn' => $lpn]);
            $generatedCount++;
        }

        return back()
            ->with('success', $generatedCount . ' nuevos LPNs únicos han sido generados.')
            ->with('new_batch_qty', $generatedCount);
    }

    public function printPdf(Request $request)
    {
        $request->validate(['quantity' => 'required|integer|min:1|max:100']);
        $lpns = PregeneratedLpn::where('is_used', false)->latest()->limit($request->quantity)->get();

        if ($lpns->isEmpty()) {
            return back()->with('error', 'No hay LPNs disponibles para imprimir.');
        }

        $pdf = Pdf::loadView('wms.lpns.pdf', compact('lpns'));
        return $pdf->stream('lpns-nuevos.pdf');
    }

    public function reprintPdf(Request $request)
    {
        $validated = $request->validate([
            'lpns' => 'required|string',
            'quantity' => 'required|integer|min:1|max:50',
        ]);

        $lpnArray = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $validated['lpns'])));

        if (empty($lpnArray)) {
            return back()->with('error', 'No se ingresaron LPNs válidos.');
        }

        $foundLpns = PregeneratedLpn::whereIn('lpn', $lpnArray)->get();

        if ($foundLpns->count() !== count($lpnArray)) {
            $notFound = array_diff($lpnArray, $foundLpns->pluck('lpn')->toArray());
            return back()->with('error', 'No se encontraron los siguientes LPNs: ' . implode(', ', $notFound));
        }

        $lpnsToPrint = collect();
        for ($i = 0; $i < $validated['quantity']; $i++) {
            foreach ($foundLpns as $lpn) {
                $lpnsToPrint->push($lpn);
            }
        }

        $pdf = Pdf::loadView('wms.lpns.pdf', ['lpns' => $lpnsToPrint]);
        return $pdf->stream('lpn-reimpresion.pdf');
    }

    public function printFromCsv(Request $request)
    {
        $validated = $request->validate([
            'lpn_file' => 'required|file|mimes:csv,txt',
            'quantity' => 'required|integer|min:1|max:50',
        ]);

        $file = $request->file('lpn_file');
        $csvData = array_map('str_getcsv', file($file->getRealPath()));
        $lpnList = collect($csvData)->flatten()->map('trim')->filter()->unique()->values()->all();

        if (empty($lpnList)) {
            return back()->with('error', 'El archivo CSV está vacío o no contiene LPNs válidos.');
        }

        $existingLpns = PregeneratedLpn::whereIn('lpn', $lpnList)->pluck('lpn')->all();

        $lpnsToCreate = array_diff($lpnList, $existingLpns);

        if (!empty($lpnsToCreate)) {
            $dataToInsert = [];
            foreach ($lpnsToCreate as $lpn) {
                $dataToInsert[] = ['lpn' => $lpn, 'created_at' => now(), 'updated_at' => now()];
            }
            PregeneratedLpn::insert($dataToInsert);
        }
        
        $allLpns = PregeneratedLpn::whereIn('lpn', $lpnList)->get();

        $lpnsToPrint = collect();
        for ($i = 0; $i < $validated['quantity']; $i++) {
            foreach ($allLpns as $lpn) {
                $lpnsToPrint->push($lpn);
            }
        }

        $pdf = Pdf::loadView('wms.lpns.pdf', ['lpns' => $lpnsToPrint]);
        return $pdf->stream('lpns-desde-csv.pdf');
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=plantilla_lpns.csv',
        ];

        $columns = ['lpn'];

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }    

    public function exportInventory()
    {
        $fileName = 'inventario_total_lpns_' . date('Y-m-d_H-i') . '.csv';
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['ID', 'LPN', 'ESTADO', 'FECHA CREACION', 'ULTIMA ACTUALIZACION']);

            PregeneratedLpn::orderBy('id')->chunk(5000, function($lpns) use ($file) {
                foreach ($lpns as $lpn) {
                    fputcsv($file, [
                        $lpn->id,
                        $lpn->lpn,
                        $lpn->is_used ? 'EN USO' : 'DISPONIBLE',
                        $lpn->created_at->format('Y-m-d H:i:s'),
                        $lpn->updated_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}