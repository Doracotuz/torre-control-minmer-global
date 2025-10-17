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
        // Calculamos estadísticas para el dashboard
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
                // 1. Generamos un código aleatorio y seguro de 10 caracteres
                $randomCode = strtoupper(bin2hex(random_bytes(5)));
                $lpn = 'LPN' . $randomCode;

                // 2. Verificamos en la base de datos si este LPN ya existe
                $exists = PregeneratedLpn::where('lpn', $lpn)->exists();

            } while ($exists); // 3. Si existe, repetimos el ciclo hasta encontrar uno único

            // 4. Guardamos el LPN, que ahora tenemos la certeza de que es único
            PregeneratedLpn::create(['lpn' => $lpn]);
            $generatedCount++;
        }

        return back()->with('success', $generatedCount . ' nuevos LPNs únicos han sido generados.');
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

    // --- NUEVO MÉTODO PARA REIMPRIMIR ---
    public function reprintPdf(Request $request)
    {
        $validated = $request->validate([
            'lpns' => 'required|string',
            'quantity' => 'required|integer|min:1|max:50', // Límite de 50 copias por LPN
        ]);

        // 1. Convertimos el string separado por comas en un array limpio
        $lpnArray = array_filter(array_map('trim', explode(',', $validated['lpns'])));

        if (empty($lpnArray)) {
            return back()->with('error', 'No se ingresaron LPNs válidos.');
        }

        // 2. Buscamos todos los LPNs solicitados en la base de datos
        $foundLpns = PregeneratedLpn::whereIn('lpn', $lpnArray)->get();

        // 3. Verificamos que todos los LPNs solicitados se hayan encontrado
        if ($foundLpns->count() !== count($lpnArray)) {
            $notFound = array_diff($lpnArray, $foundLpns->pluck('lpn')->toArray());
            return back()->with('error', 'No se encontraron los siguientes LPNs: ' . implode(', ', $notFound));
        }

        // 4. Creamos la colección final, duplicando las etiquetas según la cantidad solicitada
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

        // 1. Leer el archivo CSV y obtener una lista limpia de LPNs
        $file = $request->file('lpn_file');
        $csvData = array_map('str_getcsv', file($file->getRealPath()));
        $lpnList = collect($csvData)->flatten()->map('trim')->filter()->unique()->values()->all();

        if (empty($lpnList)) {
            return back()->with('error', 'El archivo CSV está vacío o no contiene LPNs válidos.');
        }

        // --- INICIO DE LA NUEVA LÓGICA ---

        // 2. Buscar cuáles de los LPNs del archivo ya existen en la base de datos
        $existingLpns = PregeneratedLpn::whereIn('lpn', $lpnList)->pluck('lpn')->all();

        // 3. Determinar cuáles LPNs son nuevos (los que están en el archivo pero no en la BD)
        $lpnsToCreate = array_diff($lpnList, $existingLpns);

        // 4. Si hay LPNs nuevos, crearlos todos en una sola consulta para mayor eficiencia
        if (!empty($lpnsToCreate)) {
            $dataToInsert = [];
            foreach ($lpnsToCreate as $lpn) {
                // Preparamos los datos para la inserción masiva
                $dataToInsert[] = ['lpn' => $lpn, 'created_at' => now(), 'updated_at' => now()];
            }
            PregeneratedLpn::insert($dataToInsert);
        }
        
        // --- FIN DE LA NUEVA LÓGICA ---

        // 5. Ahora que todos los LPNs están garantizados en la BD, los obtenemos todos
        $allLpns = PregeneratedLpn::whereIn('lpn', $lpnList)->get();

        // 6. Creamos la colección final para imprimir, duplicando según la cantidad de copias
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

        // La plantilla solo necesita una columna
        $columns = ['lpn'];

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            // Añadimos el BOM para compatibilidad con acentos en Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }    

}