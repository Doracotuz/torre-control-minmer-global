<?php

namespace App\Http\Controllers\Rutas;

use App\Http\Controllers\Controller;
use App\Models\Guia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class AsignacionController extends Controller
{
    /**
     * Muestra la lista de guías para asignación.
     */
    public function index(Request $request)
    {
        $query = Guia::query()->with('ruta', 'facturas');

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('guia', 'like', $searchTerm)
                  ->orWhere('operador', 'like', $searchTerm)
                  ->orWhere('placas', 'like', $searchTerm);
            });
        }

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        if ($request->filled('origen')) {
            $query->where('origen', 'like', '%' . $request->origen . '%');
        }

        $guias = $query->withCount('facturas')->orderBy('created_at', 'desc')->paginate(20);

        return view('rutas.asignaciones.index', compact('guias'));
    }

    /**
     * Descarga la plantilla CSV.
     */
    public function downloadTemplate()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=plantilla_guias.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'guia',             // 0
                'fecha_asignacion', // 1
                'hora_planeada',    // 2
                'origen',           // 3
                'destino',          // 4
                'hora_cita',        // 5
                'factura',          // 6
                'botellas',         // 7
                'cajas',            // 8
                'custodia',         // 9
                'operador',         // 10
                'placas',           // 11
                'pedimento'         // 12
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
        $request->validate(['csv_file' => 'required|mimes:csv,txt']);

        $path = $request->file('csv_file')->getRealPath();
        
        $fileContent = file_get_contents($path);
        $utf8Content = mb_convert_encoding($fileContent, 'UTF-8', mb_detect_encoding($fileContent, 'UTF-8, ISO-8859-1', true));
        
        $file = fopen("php://memory", 'r+');
        fwrite($file, $utf8Content);
        rewind($file);

        fgetcsv($file); // Omitir la cabecera

        $guiasData = [];
        while (($row = fgetcsv($file, 1000, ",")) !== FALSE) {
            if (count(array_filter($row)) == 0) continue;
            $guiaNum = trim($row[0]);
            if (empty($guiaNum)) continue;

            if (!isset($guiasData[$guiaNum])) {
                $guiasData[$guiaNum] = [
                    'fecha_asignacion' => trim($row[1]),
                    'hora_planeada'    => trim($row[2]),
                    'origen'           => substr(trim($row[3]), 0, 3),
                    'custodia'         => trim($row[9]),
                    'operador'         => trim($row[10]),
                    'placas'           => trim($row[11]),
                    'pedimento'        => trim($row[12]),
                    'facturas'         => []
                ];
            }
            $guiasData[$guiaNum]['facturas'][] = [
                'destino'          => trim($row[4]),
                'hora_cita'        => trim($row[5]),
                'numero_factura'   => trim($row[6]),
                'botellas'         => (int)trim($row[7]),
                'cajas'            => (int)trim($row[8]),
            ];
        }
        fclose($file);
        
        DB::beginTransaction();
        try {
            foreach ($guiasData as $guiaNum => $data) {
                if (Guia::where('guia', $guiaNum)->exists()) continue;

                $fechaAsignacion = null;
                if (!empty($data['fecha_asignacion'])) {
                    try {
                        $fechaAsignacion = Carbon::createFromFormat('d/m/Y', $data['fecha_asignacion'])->format('Y-m-d');
                    } catch (\Exception $e) {
                        Log::warning("Formato de fecha inválido para guía {$guiaNum}: " . $data['fecha_asignacion']);
                        $fechaAsignacion = null;
                    }
                }

                $guia = Guia::create([
                    'guia'             => $guiaNum,
                    'fecha_asignacion' => $fechaAsignacion,
                    'hora_planeada'    => $data['hora_planeada'],
                    'origen'           => $data['origen'],
                    'custodia'         => $data['custodia'],
                    'operador'         => $data['operador'],
                    'placas'           => $data['placas'],
                    'pedimento'        => $data['pedimento'],
                    'estatus'          => 'En Espera',
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

    /**
     * Muestra el formulario para crear una guía manualmente.
     */
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
            'custodia' => 'nullable|string|max:255',
            'hora_planeada' => 'nullable|string|max:255',
            'fecha_asignacion' => 'nullable|date',
            'origen' => 'required|string|max:3',
            'facturas' => 'required|array|min:1',
            'facturas.*.numero_factura' => 'required|string|max:255',
            'facturas.*.destino' => 'required|string|max:255',
            'facturas.*.cajas' => 'required|integer|min:0',
            'facturas.*.botellas' => 'required|integer|min:0',
            'facturas.*.hora_cita' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $guia = Guia::create($validatedData);
            $guia->facturas()->createMany($validatedData['facturas']);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al crear guía manual: " . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al guardar la guía.');
        }

        return redirect()->route('rutas.asignaciones.index')->with('success', 'Guía ' . $guia->guia . ' creada exitosamente.');
    }

    /**
     * Obtiene los datos de una guía para editar.
     */
    public function edit(Guia $guia)
    {
        $guia->load('facturas');
        return response()->json($guia);
    }

    /**
     * Actualiza una guía existente.
     */
    public function update(Request $request, Guia $guia)
    {
        $validatedData = $request->validate([
            'operador' => 'required|string|max:255',
            'placas' => 'required|string|max:255',
            'pedimento' => 'nullable|string|max:255',
            'custodia' => 'nullable|string|max:255',
            'hora_planeada' => 'nullable|string|max:255',
            'fecha_asignacion' => 'nullable|date',
            'origen' => 'required|string|max:3',
            'facturas' => 'required|array|min:1',
            'facturas.*.id' => 'nullable|integer',
            'facturas.*.numero_factura' => 'required|string|max:255',
            'facturas.*.destino' => 'required|string|max:255',
            'facturas.*.cajas' => 'required|integer|min:0',
            'facturas.*.botellas' => 'required|integer|min:0',
            'facturas.*.hora_cita' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $guia->update($validatedData);

            $facturasIdsActuales = [];
            foreach ($validatedData['facturas'] as $facturaData) {
                if (isset($facturaData['id'])) {
                    $factura = $guia->facturas()->find($facturaData['id']);
                    if ($factura) {
                        $factura->update($facturaData);
                        $facturasIdsActuales[] = $factura->id;
                    }
                } else {
                    $nuevaFactura = $guia->facturas()->create($facturaData);
                    $facturasIdsActuales[] = $nuevaFactura->id;
                }
            }
            $guia->facturas()->whereNotIn('id', $facturasIdsActuales)->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al actualizar guía: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Ocurrió un error al actualizar.'], 500);
        }

        return response()->json(['success' => true, 'message' => 'Guía actualizada exitosamente.']);
    }

    /**
     * Exporta la vista actual a un archivo CSV.
     */
    public function exportCsv(Request $request)
    {
        $query = Guia::query()->with('facturas');
        
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('guia', 'like', $searchTerm)
                  ->orWhere('operador', 'like', $searchTerm)
                  ->orWhere('placas', 'like', $searchTerm);
            });
        }
        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }
        if ($request->filled('origen')) {
            $query->where('origen', 'like', '%' . $request->origen . '%');
        }

        $guias = $query->get();
        
        $fileName = "export_asignaciones_" . date('Y-m-d') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($guias) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Guia', 'Operador', 'Placas', 'Pedimento', 'Custodia', 'Hora Planeada', 'Fecha Asignacion', 'Origen', 'Estatus',
                '# Factura', 'Destino', 'Cajas', 'Botellas', 'Hora Cita'
            ]);

            foreach ($guias as $guia) {
                if ($guia->facturas->count() > 0) {
                    foreach($guia->facturas as $factura) {
                        fputcsv($file, [
                            $guia->guia, $guia->operador, $guia->placas, $guia->pedimento, $guia->custodia, $guia->hora_planeada, $guia->fecha_asignacion, $guia->origen, $guia->estatus,
                            $factura->numero_factura, $factura->destino, $factura->cajas, $factura->botellas, $factura->hora_cita
                        ]);
                    }
                } else {
                    fputcsv($file, [
                        $guia->guia, $guia->operador, $guia->placas,
                        $guia->pedimento ?? 'N/A', $guia->custodia ?? 'N/A', $guia->hora_planeada ?? 'N/A',
                        $guia->fecha_asignacion ? $guia->fecha_asignacion->format('d/m/Y') : 'N/A',
                        $guia->origen ?? 'N/A', $guia->estatus,
                        'N/A', 'N/A', 'N/A', 'N/A', 'N/A'
                    ]);
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Asigna una plantilla de ruta a una guía.
     */
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
