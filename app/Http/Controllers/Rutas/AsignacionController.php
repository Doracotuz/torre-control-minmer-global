<?php

namespace App\Http\Controllers\Rutas;

use App\Http\Controllers\Controller;
use App\Models\Guia; // Importamos el modelo Guia
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AsignacionController extends Controller
{
    /**
     * Muestra la lista de guías para asignación.
     */
public function index(Request $request)
{
    // CAMBIO: Usamos with() para cargar las relaciones completas de ruta y facturas
    $query = Guia::query()->with('ruta', 'facturas'); 

    // Búsqueda (sin cambios)
    if ($request->filled('search')) {
        $searchTerm = '%' . $request->search . '%';
        $query->where(function($q) use ($searchTerm) {
            $q->where('guia', 'like', $searchTerm)
              ->orWhere('operador', 'like', $searchTerm)
              ->orWhere('placas', 'like', $searchTerm);
        });
    }

    // Filtro por estatus (sin cambios)
    if ($request->filled('estatus')) {
        $query->where('estatus', $request->estatus);
    }

    // Usamos withCount para obtener el número de facturas para la columna de la tabla
    $guias = $query->withCount('facturas')->orderBy('created_at', 'desc')->paginate(20);

    return view('rutas.asignaciones.index', compact('guias'));
}

    public function downloadTemplate()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=plantilla_guias.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            // Columnas de la plantilla
            fputcsv($file, [
                'guia', 'operador', 'placas', 'pedimento', 'custodia',
                'factura', 'destino', 'cajas', 'botellas'
            ]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Importa guías y facturas desde un archivo CSV.
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt'
        ]);

        $path = $request->file('csv_file')->getRealPath();

        // --- MEJORA: CONVERSIÓN AUTOMÁTICA A UTF-8 ---
        $fileContent = file_get_contents($path);
        // Detecta la codificación y la convierte a UTF-8 si es necesario
        $utf8Content = mb_convert_encoding($fileContent, 'UTF-8', mb_detect_encoding($fileContent, 'UTF-8, ISO-8859-1', true));
        
        // Usamos un stream en memoria en lugar de leer el archivo directamente
        $file = fopen("php://memory", 'r+');
        fwrite($file, $utf8Content);
        rewind($file);
        // --- FIN DE LA MEJORA ---

        fgetcsv($file); // Omitir la cabecera

        $guiasData = [];
        while (($row = fgetcsv($file, 1000, ",")) !== FALSE) {
            // Asegurarse de que la fila no esté completamente vacía
            if (count(array_filter($row)) == 0) {
                continue;
            }
            $guiaNum = trim($row[0]);
            if (empty($guiaNum)) continue; // Saltar filas sin número de guía

            if (!isset($guiasData[$guiaNum])) {
                $guiasData[$guiaNum] = [
                    'operador' => trim($row[1]),
                    'placas' => trim($row[2]),
                    'pedimento' => trim($row[3]),
                    'facturas' => []
                ];
            }
            $guiasData[$guiaNum]['facturas'][] = [
                'numero_factura' => trim($row[5]),
                'destino' => trim($row[6]),
                'cajas' => (int)trim($row[7]),
                'botellas' => (int)trim($row[8]),
            ];
        }
        fclose($file);
        
        // El resto de la lógica para guardar en la BD se queda igual
        DB::beginTransaction();
        try {
            foreach ($guiasData as $guiaNum => $data) {
                if (Guia::where('guia', $guiaNum)->exists()) {
                    continue;
                }
                $guia = Guia::create([
                    'guia' => $guiaNum,
                    'operador' => $data['operador'],
                    'placas' => $data['placas'],
                    'pedimento' => $data['pedimento'],
                    'estatus' => 'En Espera',
                ]);
                $guia->facturas()->createMany($data['facturas']);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error en importación CSV: " . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error. Revisa el formato y los datos del archivo.');
        }

        return redirect()->route('rutas.asignaciones.index')->with('success', 'Archivo importado exitosamente.');
    }

    public function create()
    {
        return view('rutas.asignaciones.create');
    }

    /**
     * Guarda una nueva guía y sus facturas en la base de datos.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'guia' => 'required|string|max:255|unique:guias,guia',
            'operador' => 'required|string|max:255',
            'placas' => 'required|string|max:255',
            'pedimento' => 'nullable|string|max:255',
            'facturas' => 'required|array|min:1',
            'facturas.*.numero_factura' => 'required|string|max:255',
            'facturas.*.destino' => 'required|string|max:255',
            'facturas.*.cajas' => 'required|integer|min:0',
            'facturas.*.botellas' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $guia = Guia::create([
                'guia' => $validatedData['guia'],
                'operador' => $validatedData['operador'],
                'placas' => $validatedData['placas'],
                'pedimento' => $validatedData['pedimento'],
                'estatus' => 'En Espera',
            ]);

            $guia->facturas()->createMany($validatedData['facturas']);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al crear guía manual: " . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al guardar la guía.');
        }

        return redirect()->route('rutas.asignaciones.index')->with('success', 'Guía ' . $guia->guia . ' creada exitosamente.');
    }

    public function assignRoute(Request $request, Guia $guia)
    {
        $request->validate([
            'ruta_id' => 'required|exists:rutas,id',
        ]);

        $guia->ruta_id = $request->input('ruta_id');
        $guia->estatus = 'Planeada';
        $guia->save();

        return redirect()->route('rutas.asignaciones.index')
                         ->with('success', 'Ruta asignada a la Guía ' . $guia->guia . ' exitosamente.');
    }
}