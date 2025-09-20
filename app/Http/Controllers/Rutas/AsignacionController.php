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
            // El filtro de origen ahora usa el valor del select
            $query->where('origen', $request->origen);
        }

        $guias = $query->withCount('facturas')->orderBy('created_at', 'desc')->paginate(20);

        // --- INICIA CAMBIO: Obtener orígenes para el dropdown ---
        $origenes = Guia::select('origen')->whereNotNull('origen')->where('origen', '!=', '')->distinct()->orderBy('origen')->pluck('origen');
        // --- TERMINA CAMBIO ---

        return view('rutas.asignaciones.index', compact('guias', 'origenes')); // <-- Se pasa la variable $origenes
    }

    /**
     * Descarga la plantilla CSV.
     */
    public function downloadTemplate()
    {
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=plantilla_guias.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            // --- INICIA CAMBIO: Nuevas columnas en la plantilla CSV ---
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
                'pedimento',        // 12
                'so',               // 13 <-- AÑADIDO
                'fecha_entrega'     // 14 <-- AÑADIDO (Formato YYYY-MM-DD o DD/MM/YYYY)
            ]);
            // --- TERMINA CAMBIO ---
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function importCsv(Request $request)
    {
        $request->validate(['csv_file' => 'required|mimes:csv,txt']);
        $path = $request->file('csv_file')->getRealPath();
        $fileContent = file_get_contents($path);
        $utf8Content = mb_convert_encoding($fileContent, 'UTF-8', mb_detect_encoding($fileContent, 'UTF-8, ISO-8859-1', true));
        $file = fopen("php://memory", 'r+');
        fwrite($file, $utf8Content);
        rewind($file);
        fgetcsv($file); // Omitir cabecera

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
            
            // --- INICIA CAMBIO: Leer nuevos campos del CSV ---
            $fechaEntrega = null;
            if (!empty(trim($row[14]))) {
                try {
                    // Intenta con formato 'd/m/Y' primero, luego 'Y-m-d'
                    $fechaEntrega = Carbon::createFromFormat('d/m/Y', trim($row[14]))->format('Y-m-d');
                } catch (\Exception $e) {
                    try {
                        $fechaEntrega = Carbon::parse(trim($row[14]))->format('Y-m-d');
                    } catch (\Exception $e) {
                        Log::warning("Formato de fecha de entrega inválido: ". $row[14]);
                        $fechaEntrega = null;
                    }
                }
            }

            $guiasData[$guiaNum]['facturas'][] = [
                'destino'          => trim($row[4]),
                'hora_cita'        => trim($row[5]),
                'numero_factura'   => trim($row[6]),
                'botellas'         => (int)trim($row[7]),
                'cajas'            => (int)trim($row[8]),
                'so'               => !empty(trim($row[13])) ? trim($row[13]) : null,
                'fecha_entrega'    => $fechaEntrega,
            ];
            // --- TERMINA CAMBIO ---
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
                    } catch (\Exception $e) { $fechaAsignacion = null; }
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

    public function create(Request $request)
    {
        $facturasData = [];
        $planningRecords = collect();
        $guiaData = [
            'custodia' => $request->query('custodia'),
            'hora_planeada' => $request->query('hora_planeada'),
            'origen' => $request->query('origen'),
            'fecha_asignacion' => $request->query('fecha_asignacion'),
            'telefono' => '', 
        ];

        if ($request->has('planning_ids')) {
            $planningRecords = \App\Models\CsPlanning::find($request->query('planning_ids'));

            $firstPhone = $planningRecords->firstWhere('telefono');
            if ($firstPhone) {
                $guiaData['telefono'] = $firstPhone->telefono;
            }            
            
            foreach ($planningRecords as $planning) {
                $facturasData[] = [
                    'cs_planning_id' => $planning->id,
                    'numero_factura' => $planning->factura ?? $planning->so_number,
                    'destino' => $planning->razon_social,
                    'cajas' => $planning->cajas,
                    'botellas' => $planning->pzs,
                    'hora_cita' => $planning->hora_cita,
                    'so' => $planning->so_number,
                    'fecha_entrega' => $planning->fecha_entrega?->format('Y-m-d'),
                ];
            }
        }
        
        // ✅ INICIA CAMBIO: Añadimos la lógica para procesar las observaciones
        $observacionesConSO = $planningRecords
            ->filter(fn($p) => !empty($p->observaciones))
            ->map(fn($p) => ['so' => $p->so_number ?? 'Manual', 'observacion' => $p->observaciones])
            ->values();
        // ⏹️ TERMINA CAMBIO
        
        $planning_ids = json_encode($request->query('planning_ids', []));

        // Añadimos la nueva variable a los datos que se envían a la vista
        return view('rutas.asignaciones.create', compact('facturasData', 'guiaData', 'planning_ids', 'planningRecords', 'observacionesConSO'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'guia' => 'required|string',
            'operador' => 'required|string',
            'placas' => 'required|string',
            'fecha_asignacion' => 'required|date',
            'planning_ids' => 'required|array|min:1',
            'planning_ids.*' => 'exists:cs_plannings,id',
            'telefono' => 'nullable|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            $guia = Guia::where('guia', $validatedData['guia'])->first();
            if ($guia) {
                $guia->update([
                    'operador' => $validatedData['operador'],
                    'placas' => $validatedData['placas'],
                    'fecha_asignacion' => $validatedData['fecha_asignacion'],
                    'telefono' => $validatedData['telefono'] ?? null,
                    'estatus' => 'En Espera',
                ]);
            } else {
                $guia = Guia::create([
                    'guia' => $validatedData['guia'],
                    'operador' => $validatedData['operador'],
                    'placas' => $validatedData['placas'],
                    'fecha_asignacion' => $validatedData['fecha_asignacion'],
                    'estatus' => 'En Espera',
                    'telefono' => $validatedData['telefono'] ?? null,
                ]);
            }

            $planningRecords = \App\Models\CsPlanning::find($validatedData['planning_ids']);
            $processedCount = 0;
            $skippedIds = [];

            foreach ($planningRecords as $planning) {
                // VERIFICACIÓN CLAVE: Si ya tiene una guía asignada, se salta el registro.
                if ($planning->guia_id !== null) {
                    $skippedIds[] = $planning->id;
                    continue;
                }

                // Lógica para crear o actualizar facturas
                $facturaExistente = $guia->facturas()->where('cs_planning_id', $planning->id)->first();
                if (!$facturaExistente) {
                    $guia->facturas()->create([
                        'cs_planning_id' => $planning->id,
                        'numero_factura' => $planning->factura ?? $planning->so_number,
                        'destino' => $planning->razon_social,
                        'cajas' => $planning->cajas,
                        'botellas' => $planning->pzs,
                        'hora_cita' => $planning->hora_cita,
                        'so' => $planning->so_number,
                        'fecha_entrega' => $planning->fecha_entrega,
                    ]);
                } else {
                    $facturaExistente->update([
                        'numero_factura' => $planning->factura ?? $planning->so_number,
                        'destino' => $planning->razon_social,
                        'cajas' => $planning->cajas,
                        'botellas' => $planning->pzs,
                        'hora_cita' => $planning->hora_cita,
                        'so' => $planning->so_number,
                        'fecha_entrega' => $planning->fecha_entrega,
                    ]);
                }
                
                // Actualizar la planificación
                $planning->update([
                    'guia_id' => $guia->id,
                    'status' => 'Asignado en Guía',
                ]);
                $processedCount++;
            }

            DB::commit();

            $message = "Guía {$guia->guia} procesada exitosamente con {$processedCount} órdenes.";
            if (!empty($skippedIds)) {
                $message .= " Se omitieron " . count($skippedIds) . " órdenes que ya estaban asignadas.";
            }
            
            return redirect()->route('rutas.asignaciones.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al crear/actualizar la guía: " . $e->getMessage());
            return redirect()->route('customer-service.planning.index')->with('error', 'Ocurrió un error al procesar la guía.');
        }
    }

    public function edit(Guia $guia, Request $request)
    {
        $guia->load('facturas', 'plannings');

        // ✅ INICIA CAMBIO: Nueva lógica para obtener observaciones con SO
        $observacionesConSO = $guia->plannings
            ->filter(fn($p) => !empty($p->observaciones)) // Filtramos solo los que tienen observaciones
            ->map(fn($p) => ['so' => $p->so_number ?? 'Manual', 'observacion' => $p->observaciones]) // Creamos el array estructurado
            ->values(); // Obtenemos un array limpio

        $capacidad = $guia->plannings->firstWhere('capacidad')['capacidad'] ?? 'No definida';
            

        if ($request->ajax() || $request->wantsJson()) {
            // ✅ INICIA CAMBIO: Añadimos el cálculo y la propiedad que faltaba
            $guia->total_maniobras = $guia->plannings->sum('maniobras');
            $guia->observaciones_con_so = $observacionesConSO;
            $guia->capacidad = $capacidad;

            return response()->json($guia);
            // ⏹️ TERMINA CAMBIO
        }

        $totalManiobras = $guia->plannings->sum('maniobras');
        
        // Pasamos el nuevo array a la vista de página completa
        return view('rutas.asignaciones.edit', compact('guia', 'totalManiobras', 'observacionesConSO', 'capacidad'));
    }

    public function update(Request $request, Guia $guia)
    {
        $validatedData = $request->validate([
            'operador' => 'required|string|max:255',
            'placas' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:255',
            'pedimento' => 'nullable|string|max:255',
            'custodia' => 'nullable|string|max:255',
            'hora_planeada' => 'nullable|string|max:255',
            'fecha_asignacion' => 'nullable|date',
            'origen' => 'required|string|max:3',
            'transporte' => 'nullable|string|max:255',
            'facturas' => 'required|array|min:1',
            'facturas.*.id' => 'nullable|integer',
            'facturas.*.cs_planning_id' => 'nullable|integer|exists:cs_plannings,id',
            'facturas.*.numero_factura' => 'required|string|max:255',
            'facturas.*.destino' => 'required|string|max:255',
            'facturas.*.cajas' => 'required|integer|min:0',
            'facturas.*.botellas' => 'required|integer|min:0',
            'facturas.*.hora_cita' => 'nullable|string|max:255',
            'facturas.*.so' => 'nullable|string|max:255',
            'facturas.*.fecha_entrega' => 'nullable|date_format:Y-m-d',
        ]);

        DB::beginTransaction();
        try {
            // Mapeo de nombres de campos para la descripción del evento
            $fieldNames = [
                'operador' => 'Operador',
                'placas' => 'Placas',
                'telefono' => 'Teléfono',
                'pedimento' => 'Pedimento',
                'custodia' => 'Custodia',
                'hora_planeada' => 'Hora Planeada',
                'fecha_asignacion' => 'Fecha Asignación',
                'origen' => 'Origen',
                'transporte' => 'Transporte'
            ];

            // 1. Detectamos los cambios ANTES de actualizar la guía
            $descriptionParts = [];
            foreach ($fieldNames as $field => $friendlyName) {
                // Comparamos el valor actual del modelo con el valor que llega del formulario
                if (isset($validatedData[$field]) && $guia->$field != $validatedData[$field]) {
                    $oldValue = $guia->$field ?? 'vacío';
                    $newValue = $validatedData[$field] ?? 'vacío';
                    $descriptionParts[] = "{$friendlyName} cambió de '{$oldValue}' a '{$newValue}'";
                }
            }
            $changesDescription = implode('. ', $descriptionParts);

            // 2. Ahora sí, actualizamos la guía
            $guia->update($request->only(array_keys($fieldNames)));

            // 3. Procesamos las facturas (añadir, editar)
            $facturasIdsActuales = [];
            foreach ($validatedData['facturas'] as $facturaData) {
                if (!empty($facturaData['id'])) {
                    $factura = $guia->facturas()->find($facturaData['id']);
                    if ($factura) {
                        $factura->update($facturaData);
                        $facturasIdsActuales[] = $factura->id;
                    }
                } else {
                    $nuevaFactura = $guia->facturas()->create($facturaData);
                    $facturasIdsActuales[] = $nuevaFactura->id;
                }

                if (isset($facturaData['cs_planning_id']) && $facturaData['cs_planning_id']) {
                    $planningRecord = \App\Models\CsPlanning::find($facturaData['cs_planning_id']);
                    if ($planningRecord) {
                        $planningRecord->update([
                            'factura' => $facturaData['numero_factura'], 'cajas' => $facturaData['cajas'],
                            'pzs' => $facturaData['botellas'], 'hora_cita' => $facturaData['hora_cita'],
                            'so_number' => $facturaData['so'], 'fecha_entrega' => $facturaData['fecha_entrega'],
                        ]);
                    }
                }
            }

            // 4. Desasignamos las órdenes de las facturas que fueron eliminadas
            $facturasParaEliminar = $guia->facturas()->whereNotIn('id', $facturasIdsActuales)->get();
            if ($facturasParaEliminar->isNotEmpty()) {
                $planningIdsParaDesasignar = $facturasParaEliminar->whereNotNull('cs_planning_id')->pluck('cs_planning_id');
                if ($planningIdsParaDesasignar->isNotEmpty()) {
                    \App\Models\CsPlanning::whereIn('id', $planningIdsParaDesasignar)->update(['guia_id' => null, 'status' => 'En Espera', 'operador' => 'Pendiente', 'placas' => 'Pendiente', 'telefono' => 'Pendiente', 'transporte' => null]);
                }
                $guia->facturas()->whereNotIn('id', $facturasIdsActuales)->delete();
            }

            // 5. Sincronizamos los datos de la guía a todas las órdenes (plannings) asociadas
            $planningIds = $guia->facturas()->whereNotNull('cs_planning_id')->pluck('cs_planning_id');
            if ($planningIds->isNotEmpty()) {
                \App\Models\CsPlanning::whereIn('id', $planningIds)->update([
                    'operador' => $validatedData['operador'],
                    'placas' => $validatedData['placas'],
                    'telefono' => $validatedData['telefono'],
                    'transporte' => $validatedData['transporte'],
                ]);
            }

            // 6. Creamos la descripción final para el evento
            $finalDescription = !empty($changesDescription)
                ? 'Datos de la Guía ' . $guia->guia . ' actualizados por ' . auth()->user()->name . '. ' . $changesDescription
                : 'Se actualizaron las facturas de la Guía ' . $guia->guia . ' por ' . auth()->user()->name . '.';
            
            // Obtenemos los IDs de las órdenes afectadas
            $orderIds = \App\Models\CsPlanning::whereIn('id', $planningIds)->pluck('cs_order_id')->unique()->filter();

            // Creamos un evento para CADA orden afectada
            foreach ($orderIds as $orderId) {
                \App\Models\CsOrderEvent::create([
                    'cs_order_id' => $orderId,
                    'user_id' => auth()->id(),
                    'description' => $finalDescription,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al actualizar guía: " . $e->getMessage() . ' en la línea ' . $e->getLine());
            return response()->json(['success' => false, 'message' => 'Ocurrió un error al actualizar. Revise los logs.'], 500);
        }

        return response()->json(['success' => true, 'message' => 'Guía actualizada, órdenes sincronizadas y evento registrado exitosamente.']);
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
            // --- INICIA CAMBIO: Nuevas columnas en la exportación CSV ---
            fputcsv($file, [
                'Guia', 'Operador', 'Placas', 'Pedimento', 'Custodia', 'Hora Planeada', 'Fecha Asignacion', 'Origen', 'Estatus',
                '# Factura', 'Destino', 'Cajas', 'Botellas', 'Hora Cita', 'SO', 'Fecha Entrega'
            ]);

            foreach ($guias as $guia) {
                if ($guia->facturas->count() > 0) {
                    foreach($guia->facturas as $factura) {
                        fputcsv($file, [
                            $guia->guia, $guia->operador, $guia->placas, $guia->pedimento, $guia->custodia, $guia->hora_planeada, $guia->fecha_asignacion, $guia->origen, $guia->estatus,
                            $factura->numero_factura, $factura->destino, $factura->cajas, $factura->botellas, $factura->hora_cita,
                            $factura->so ?? 'N/A', // <-- AÑADIDO
                            $factura->fecha_entrega ? $factura->fecha_entrega->format('d/m/Y') : 'N/A' // <-- AÑADIDO
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

    public function search(Request $request)
    {
        $term = $request->query('term', '');
        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $guias = Guia::where(function ($query) use ($term) {
                        $query->where('guia', 'like', "%{$term}%")
                              ->orWhere('operador', 'like', "%{$term}%")
                              ->orWhere('placas', 'like', "%{$term}%");
                    })
                    ->whereNotIn('estatus', ['Completada', 'Cancelado']) // Solo guías activas
                    ->limit(10)
                    ->get(['id', 'guia', 'operador', 'placas']);

        return response()->json($guias);
    }

    /**
     * Añade nuevos registros de planificación (órdenes) a una guía existente.
     */
    public function addOrdersToGuia(Request $request)
    {
        $validatedData = $request->validate([
            'guia_id' => 'required|exists:guias,id',
            'planning_ids' => 'required|array|min:1',
            'planning_ids.*' => 'exists:cs_plannings,id'
        ]);

        try {
            DB::beginTransaction();

            $guia = Guia::findOrFail($validatedData['guia_id']);
            $incomingPlanningIds = collect($validatedData['planning_ids']);
            
            // Paso 1: Obtener TODOS los registros de planificación involucrados (los nuevos Y los que ya estaban en la guía)
            $existingPlanningIds = $guia->plannings()->pluck('cs_plannings.id');
            $allRelevantIds = $incomingPlanningIds->merge($existingPlanningIds)->unique();
            $allRelevantPlanningRecords = \App\Models\CsPlanning::find($allRelevantIds);

            $masterData = [ 'operador' => null, 'placas' => null, 'telefono' => null ];
            $fields = ['operador', 'placas', 'telefono'];

            // Paso 2: Iterar sobre TODOS los registros para buscar conflictos y datos maestros
            foreach ($allRelevantPlanningRecords as $planning) {
                foreach ($fields as $field) {
                    // Se ignora el valor 'Pendiente' y los vacíos al buscar un dato maestro
                    if (!empty($planning->$field) && $planning->$field !== 'Pendiente') {
                        if ($masterData[$field] === null) {
                            // Se encuentra el primer dato válido y se establece como maestro
                            $masterData[$field] = $planning->$field;
                        } elseif ($masterData[$field] !== $planning->$field) {
                            // Se encuentra un segundo dato válido diferente: ¡Conflicto!
                            DB::rollBack();
                            $fieldNameSpanish = ucfirst($field);
                            return back()->with('error', "Conflicto en '{$fieldNameSpanish}'. Se encontraron datos diferentes ('{$masterData[$field]}' y '{$planning->$field}').");
                        }
                    }
                }
            }
            
            // Paso 3: Identificar solo las órdenes que son realmente nuevas para agregarlas
            $newPlanningIds = $incomingPlanningIds->diff($existingPlanningIds);

            if ($newPlanningIds->isEmpty()) {
                DB::rollBack();
                return back()->with('info', 'Todas las órdenes seleccionadas ya se encuentran en la guía ' . $guia->guia . '.');
            }
            
            $newPlanningRecords = \App\Models\CsPlanning::find($newPlanningIds);

            // Paso 4: Actualizar la Guía con los datos maestros si está vacía o en "Pendiente"
            if ($masterData['operador'] && (empty($guia->operador) || $guia->operador === 'Pendiente')) $guia->operador = $masterData['operador'];
            if ($masterData['placas'] && (empty($guia->placas) || $guia->placas === 'Pendiente')) $guia->placas = $masterData['placas'];
            if ($masterData['telefono'] && empty($guia->telefono)) $guia->telefono = $masterData['telefono'];
            $guia->save();

            // Paso 5: Iterar sobre las NUEVAS órdenes para propagar datos y asignarlas
            foreach ($newPlanningRecords as $planning) {
                // Propagar información a campos vacíos o "Pendiente"
                if ($masterData['operador'] && (empty($planning->operador) || $planning->operador === 'Pendiente')) $planning->operador = $masterData['operador'];
                if ($masterData['placas'] && (empty($planning->placas) || $planning->placas === 'Pendiente')) $planning->placas = $masterData['placas'];
                if ($masterData['telefono'] && (empty($planning->telefono) || $planning->telefono === 'Pendiente')) $planning->telefono = $masterData['telefono'];
                
                // Crear la factura
                $guia->facturas()->create([
                    'cs_planning_id' => $planning->id,
                    'numero_factura' => $planning->factura ?? $planning->so_number,
                    'destino' => $planning->razon_social,
                    'cajas' => $planning->cajas,
                    'botellas' => $planning->pzs,
                    'hora_cita' => $planning->hora_cita,
                    'so' => $planning->so_number,
                    'fecha_entrega' => $planning->fecha_entrega,
                ]);

                // Asignar y guardar TODOS los cambios en el registro de planificación
                $planning->guia_id = $guia->id;
                $planning->status = 'Asignado en Guía';
                $planning->save();
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al añadir órdenes a guía existente: " . $e->getMessage());
            return redirect()->route('customer-service.planning.index')->with('error', 'Ocurrió un error al actualizar la guía.');
        }

        return redirect()->route('customer-service.planning.index')->with('success', $newPlanningIds->count() . ' nuevas órdenes fueron añadidas y sincronizadas con la guía ' . $guia->guia . ' exitosamente.');
    }

    public function updateNumber(Request $request, Guia $guia)
    {
        // 1. Validación mejorada para evitar duplicados
        $validated = $request->validate([
            'guia_number' => 'required|string|max:255|unique:guias,guia,' . $guia->id,
        ]);

        $originalGuiaNumber = $guia->guia;
        $newGuiaNumber = $validated['guia_number'];

        // Si no hay cambios, no hacemos nada
        if ($originalGuiaNumber === $newGuiaNumber) {
            return response()->json([
                'success' => true,
                'message' => 'No se realizaron cambios.',
                'new_guia_number' => $newGuiaNumber
            ]);
        }
        
        // Usamos una transacción para asegurar la integridad de los datos
        DB::beginTransaction();
        try {
            // 2. Actualizamos la guía
            $guia->update(['guia' => $newGuiaNumber]);

            // 3. Creamos la descripción detallada para el evento
            $description = 'El número de Guía cambió de \'' . $originalGuiaNumber . '\' a \'' . $newGuiaNumber . '\' por ' . auth()->user()->name . '.';

            // 4. Buscamos todas las órdenes asociadas a esta guía
            $orderIds = $guia->plannings()->pluck('cs_order_id')->unique()->filter();

            // 5. Creamos un evento para cada orden afectada
            foreach ($orderIds as $orderId) {
                \App\Models\CsOrderEvent::create([
                    'cs_order_id' => $orderId,
                    'user_id'     => auth()->id(),
                    'description' => $description,
                ]);
            }

            DB::commit();

            return response()->json([
                'success'       => true,
                'message'       => 'Número de guía actualizado y evento registrado.',
                'new_guia_number' => $newGuiaNumber
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al actualizar número de guía: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Ocurrió un error en el servidor.'], 500);
        }
    }


    public function details(Guia $guia)
    {
        // Cargamos la guía con todas sus órdenes de planificación
        $guia->load('plannings.order');

        // Calculamos el subtotal
        $totalSubtotal = $guia->plannings->reduce(function ($carry, $planning) {
            return $carry + ($planning->subtotal ?? 0);
        }, 0);

        // ✅ INICIA CAMBIO: Usamos la misma lógica unificada que en el método edit()
        $totalManiobras = $guia->plannings->sum('maniobras');
        
        $observacionesConSO = $guia->plannings
            ->filter(fn($p) => !empty($p->observaciones))
            ->map(fn($p) => ['so' => $p->so_number ?? 'Manual', 'observacion' => $p->observaciones])
            ->values();
        $capacidad = $guia->plannings->firstWhere('capacidad')['capacidad'] ?? 'No definida';


        // Devolvemos la guía y los totales calculados en el formato correcto
        return response()->json([
            'guia' => $guia,
            'total_subtotal' => $totalSubtotal,
            'total_maniobras' => $totalManiobras,
            'observaciones_con_so' => $observacionesConSO,
            'capacidad' => $capacidad
        ]);
    }


}
