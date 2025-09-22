<?php

namespace App\Http\Controllers\CustomerService;

use App\Http\Controllers\Controller;
use App\Models\CsPlanning;
use App\Models\CsWarehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CsOrderEvent;
use Illuminate\Support\Facades\Auth;
use App\Models\Guia;
use App\Models\CsPlanningEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PlanningController extends Controller
{
    public function index()
    {
        $warehouses = \App\Models\CsWarehouse::query()
            ->select('name')
            ->distinct()
            ->orderBy('name')
            ->get();
        
        $allColumns = [
            'guia' => 'Guía',
            'fecha_carga' => 'F. Carga',
            'hora_carga' => 'H. Carga',
            'fecha_entrega' => 'F. Entrega',
            'origen' => 'Origen',
            'destino' => 'Destino',
            'razon_social' => 'Razón Social',
            'direccion' => 'Dirección',
            'hora_cita' => 'Hora Cita',
            'so_number' => 'SO',
            'factura' => 'Factura',
            'pzs' => 'Pzs',
            'cajas' => 'Cajas',
            'subtotal' => 'Subtotal',
            'observaciones' => 'Observaciones',
            'maniobras' => 'Maniobras', 
            'canal' => 'Canal',
            'transporte' => 'Transporte',
            'operador' => 'Operador',
            'placas' => 'Placas',
            'telefono' => 'Teléfono',
            'capacidad' => 'Capacidad',
            'custodia' => 'Custodia',
            'servicio' => 'Servicio',
            'tipo_ruta' => 'Tipo de Ruta',
            'region' => 'Región',
            'estado' => 'Estado',
            'urgente' => 'Urgente',
            'devolucion' => 'Devolución',
            'estatus_de_entrega' => 'Estatus Entrega',
            'status' => 'Estatus General',
            'created_at' => 'Fecha Creación'
        ];

        return view('customer-service.planning.index', compact('warehouses', 'allColumns'));
    }

    private function getFilteredQuery(Request $request)
    {
        $sortableColumns = ['guia', 'fecha_carga', 'hora_carga', 'fecha_entrega', 'origen', 'destino', 'razon_social', 'direccion', 'hora_cita', 'so_number', 'factura', 'pzs', 'cajas', 'subtotal', 'canal', 'transporte', 'operador', 'placas', 'telefono', 'capacidad', 'custodia', 'servicio', 'tipo_ruta', 'region', 'estado', 'urgente', 'devolucion', 'estatus_de_entrega', 'status', 'created_at'];
        
        $sorts = json_decode($request->input('sorts', '[]'), true);
        if (empty($sorts)) {
            $sorts = [['column' => 'created_at', 'dir' => 'asc']];
        }

        $query = CsPlanning::query()
            ->leftJoin('guias', 'cs_plannings.guia_id', '=', 'guias.id')
            ->select('cs_plannings.*');

        foreach ($sorts as $sort) {
            $sortBy = $sort['column'] ?? 'created_at';
            $sortDir = ($sort['dir'] ?? 'asc') === 'asc' ? 'asc' : 'desc';
            if (!in_array($sortBy, $sortableColumns)) { continue; }
            
            if ($sortBy === 'guia') { 
                $query->orderBy('guias.guia', $sortDir); 
            } else { 
                // CORRECCIÓN: Se añade el prefijo de la tabla para evitar ambigüedad
                $query->orderBy('cs_plannings.' . $sortBy, $sortDir); 
            }
        }

        // Filtros básicos con prefijos de tabla para evitar ambigüedad
        if ($request->filled('search')) { 
            $searchTerm = '%' . $request->search . '%'; 
            $query->where(function ($q) use ($searchTerm) { 
                $q->where('cs_plannings.so_number', 'like', $searchTerm)
                ->orWhere('cs_plannings.factura', 'like', $searchTerm)
                ->orWhere('cs_plannings.razon_social', 'like', $searchTerm); 
            }); 
        }
        if ($request->filled('status')) { 
            $query->where('cs_plannings.status', $request->status); 
        }
        if ($request->filled('origen')) { 
            $query->where('cs_plannings.origen', $request->origen); 
        }
        if ($request->filled('destino')) { 
            $query->where('cs_plannings.destino', $request->destino); 
        }
        if ($request->filled('date_created_from') && $request->filled('date_created_to')) { 
            $query->whereBetween(DB::raw('DATE(cs_plannings.created_at)'), [$request->date_created_from, $request->date_created_to]); 
        }
        
        // Filtros avanzados con prefijos de tabla
        if ($request->filled('guia_adv')) { $query->where('guias.guia', 'like', '%' . $request->guia_adv . '%'); }
        if ($request->filled('so_number_adv')) { $query->where('cs_plannings.so_number', 'like', '%' . $request->so_number_adv . '%'); }
        if ($request->filled('factura_adv')) { $query->where('cs_plannings.factura', 'like', '%' . $request->factura_adv . '%'); }
        if ($request->filled('razon_social_adv')) { $query->where('cs_plannings.razon_social', 'like', '%' . $request->razon_social_adv . '%'); }
        if ($request->filled('direccion_adv')) { $query->where('cs_plannings.direccion', 'like', '%' . $request->direccion_adv . '%'); }
        if ($request->filled('fecha_entrega_adv')) { $query->whereDate('cs_plannings.fecha_entrega', $request->fecha_entrega_adv); }
        if ($request->filled('fecha_carga_adv')) { $query->whereDate('cs_plannings.fecha_carga', $request->fecha_carga_adv); }
        if ($request->filled('origen_adv')) { $query->where('cs_plannings.origen', $request->origen_adv); }
        if ($request->filled('destino_adv')) { $query->where('cs_plannings.destino', $request->destino_adv); }
        if ($request->filled('estado_adv')) { $query->where('cs_plannings.estado', 'like', '%' . $request->estado_adv . '%'); }
        if ($request->filled('transporte_adv')) { $query->where('cs_plannings.transporte', 'like', '%' . $request->transporte_adv . '%'); }
        if ($request->filled('operador_adv')) { $query->where('cs_plannings.operador', 'like', '%' . $request->operador_adv . '%'); }
        if ($request->filled('placas_adv')) { $query->where('cs_plannings.placas', 'like', '%' . $request->placas_adv . '%'); }
        if ($request->filled('tipo_ruta_adv')) { $query->where('cs_plannings.tipo_ruta', $request->tipo_ruta_adv); }
        if ($request->filled('servicio_adv')) { $query->where('cs_plannings.servicio', $request->servicio_adv); }
        if ($request->filled('canal_adv')) { $query->where('cs_plannings.canal', 'like', '%' . $request->canal_adv . '%'); }
        if ($request->filled('custodia_adv')) { $query->where('cs_plannings.custodia', $request->custodia_adv); }
        if ($request->filled('urgente_adv')) { $query->where('cs_plannings.urgente', $request->urgente_adv); }
        if ($request->filled('devolucion_adv')) { $query->where('cs_plannings.devolucion', $request->devolucion_adv); }
        
        return $query;
    }

    public function filter(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        $perPage = $request->input('per_page', 15);
        
        // Cargar la relación 'guia' después de paginar para el JSON
        $plannings = $query->paginate($perPage)->withQueryString();
        $plannings->getCollection()->load(['guia', 'order']);

        return response()->json($plannings);
    }
    
    public function exportCsv(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        $plannings = $query->with('guia')->get();
        
        $fileName = 'planificacion_'.date('Y-m-d_H-i-s').'.csv';
        $headers = [ "Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=$fileName", "Pragma" => "no-cache", "Cache-Control" => "must-revalidate, post-check=0, pre-check=0", "Expires" => "0" ];
        $allColumns = $this->index()->getData()['allColumns'];

        $callback = function() use ($plannings, $allColumns) {
            $file = fopen('php://output', 'w');
            
            // --- LÍNEA AÑADIDA PARA LA CORRECCIÓN ---
            // Añade el BOM (Byte Order Mark) para asegurar la compatibilidad de UTF-8 con Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // El resto del código no cambia
            fputcsv($file, array_values($allColumns)); // Escribir los encabezados
            foreach ($plannings as $planning) {
                $row = [];
                foreach (array_keys($allColumns) as $columnKey) {
                if ($columnKey === 'guia') { $row[] = $planning->guia->guia ?? 'Sin Asignar'; } 
                elseif (in_array($columnKey, ['fecha_carga', 'fecha_entrega', 'created_at']) && $planning->{$columnKey}) { $row[] = Carbon::parse($planning->{$columnKey})->format('Y-m-d H:i:s'); }
                else { $row[] = $planning->{$columnKey}; }
                }
                fputcsv($file, $row);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }
    
    public function schedule(CsPlanning $planning)
    {
        $planning->update(['status' => 'Programada']);
        return back()->with('success', 'La ruta ha sido programada.');
    }


    public function create()
    {
        return view('customer-service.planning.create');
    }

    /**
     * Guarda el nuevo registro de planificación manual en la base de datos.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'razon_social' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'so_number' => 'nullable|string|max:255',
            'factura' => 'required|string|max:255',
            'origen' => 'required|string|max:255',
            'destino' => 'required|string|max:255',
            'fecha_entrega' => 'nullable|date',
            'hora_cita' => 'nullable|string|max:255',
            'pzs' => 'nullable|integer',
            'cajas' => 'nullable|integer',
            'subtotal' => 'nullable|numeric',
            'canal' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
            'maniobras' => 'nullable|integer|min:0',
        ]);
        
        $validatedData['status'] = 'En Espera';
        $planning = CsPlanning::create($validatedData);

        CsPlanningEvent::create([
            'cs_planning_id' => $planning->id,
            'user_id' => Auth::id(),
            'description' => 'El usuario ' . Auth::user()->name . ' creó el registro de planificación manualmente.'
        ]);

        return redirect()->route('customer-service.planning.index')
                         ->with('success', 'Registro de planificación manual creado exitosamente.');
    }

    public function addScales(Request $request, CsPlanning $planning)
    {
        $validated = $request->validate([
            'scales' => 'required|array|min:1',
            'scales.*.origen' => 'required|string|exists:cs_warehouses,name',
            'scales.*.destino' => 'required|string|exists:cs_warehouses,name',
        ]);

        try {
            DB::transaction(function () use ($validated, $planning) {
                foreach ($validated['scales'] as $scaleData) {
                    $newPlanningRecord = $planning->replicate();
                    $newPlanningRecord->origen = $scaleData['origen'];
                    $newPlanningRecord->destino = $scaleData['destino'];
                    $newPlanningRecord->is_scale = true;
                    $newPlanningRecord->save();
                }
                $planning->delete();
            });
        } catch (\Exception $e) {
            // Se devuelve una respuesta JSON consistente con 'success' y 'message'
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar las escalas: ' . $e->getMessage()
            ], 500);
        }

        // Se devuelve una respuesta JSON consistente con 'success' y 'message'
        return response()->json([
            'success' => true,
            'message' => 'Escalas creadas y ruta original actualizada exitosamente.'
        ]);
    }

    public function show(CsPlanning $planning)
    {
        // Eager load para optimizar consultas: carga la orden, sus detalles, los productos de esos detalles y sus eventos.
        $planning->load('order.details.product', 'order.events.user');
        return view('customer-service.planning.show', compact('planning'));
    }

    /**
     * Muestra el formulario para editar un registro de planificación.
     */
    public function edit(CsPlanning $planning)
    {
        // Opciones para los menús desplegables. Puedes mover esto a un lugar más central si lo usas en otros sitios.
        $options = [
            'capacidad' => ['1 Ton', '1.5 Ton', '3.5 Ton', '4.5 Ton', 'Torthon', 'Rabón', 'Mudancero', 'Trailer 48"', 'Trailer 53"', 'Automovil', 'Motocicleta', 'Paqueteria', 'Contenedor 20"', 'Contenedor 40"', 'Contenedor 48"', 'Contenedor 53"'],
            'estado' => ['Aguascalientes', 'Baja California', 'Baja California Sur', 'Campeche', 'Chiapas', 'Chihuahua', 'Ciudad de México', 'Coahuila', 'Colima', 'Durango', 'Estado de México', 'Guanajuato', 'Guerrero', 'Hidalgo', 'Jalisco', 'Michoacán', 'Morelos', 'Nayarit', 'Nuevo León', 'Oaxaca', 'Puebla', 'Querétaro', 'Quintana Roo', 'San Luis Potosí', 'Sinaloa', 'Sonora', 'Tabasco', 'Tamaulipas', 'Tlaxcala', 'Veracruz', 'Yucatán', 'Zacatecas'],
            'servicio' => ['Local', 'Foraneo', 'Ejecutivo'],
            'region' => ['Bajio', 'Centro', 'Noreste', 'Pacifico', 'Sureste'],
            'tipo_ruta' => ['Consolidado', 'Dedicado', 'Directo'],
            'devolucion' => ['Si', 'No'],
            'custodia' => ['Sepsa', 'Planus', 'Ninguna'],
            'urgente' => ['Si', 'No'],
            'origenes' => ['MEX', 'CUN', 'MTY', 'GDL', 'SJD', 'MIN', 'SOTANO 5'],
            'destinos' => ['AGS', 'BCN', 'CDMX', 'CUU', 'COA', 'CUL', 'CUN', 'CVJ', 'GDL', 'GRO', 'GTO', 'HGO', 'MEX', 'MIC', 'MID', 'MLM', 'MTY', 'MZN', 'NAY', 'DGO', 'ZAC', 'OAX', 'PUE', 'QRO', 'SIN', 'SJD', 'SLP', 'SMA', 'SON', 'TAB', 'TGZ', 'TIJ', 'TLX', 'VER', 'YUC', 'ZAM'],
        ];

        return view('customer-service.planning.edit', compact('planning', 'options'));
    }

    /**
     * Actualiza un registro de planificación y crea un evento en la línea de tiempo.
     */
    public function update(Request $request, CsPlanning $planning)
    {
        $validatedData = $request->validate([
            'razon_social' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'so_number' => 'nullable|string|max:255',
            'factura' => 'required|string|max:255',
            'pzs' => 'nullable|integer',
            'cajas' => 'nullable|integer',
            'origen' => 'required|string|max:255',
            'destino' => 'required|string|max:255',
            'hora_cita' => 'nullable|string|max:255',
            'fecha_carga' => 'nullable|date',
            'hora_carga' => 'nullable|date_format:H:i', // La validación se mantiene estricta
            'capacidad' => 'nullable|string',
            'transporte' => 'nullable|string|max:255',
            'estado' => 'nullable|string',
            'servicio' => 'nullable|string',
            'region' => 'nullable|string',
            'tipo_ruta' => 'nullable|string',
            'devolucion' => 'nullable|string',
            'custodia' => 'nullable|string',
            'operador' => 'nullable|string|max:255',
            'placas' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:255',
            'estatus_de_entrega' => 'nullable|string|max:255',
            'urgente' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'maniobras' => 'nullable|integer|min:0',
        ]);
        
        $originalData = $planning->getOriginal();
        $changes = [];

        foreach ($validatedData as $key => $value) {
            $oldValue = $originalData[$key] ?? null;
            $oldValueFormatted = $oldValue;
            $newValueFormatted = $value;

            if ($key === 'fecha_carga') {
                $oldValueFormatted = $oldValue ? Carbon::parse($oldValue)->format('Y-m-d') : null;
            }
            if ($key === 'hora_carga') {
                $oldValueFormatted = $oldValue ? Carbon::parse($oldValue)->format('H:i') : null;
            }

            if ($oldValueFormatted != $newValueFormatted) {
                $fieldName = ucwords(str_replace('_', ' ', $key));
                $oldValueText = $oldValue ? ($key === 'hora_carga' ? Carbon::parse($oldValue)->format('H:i') : $oldValue) : 'vacío';
                $newValueText = $value ? ($key === 'hora_carga' ? Carbon::parse($value)->format('H:i') : $value) : 'vacío';
                $changes[] = "cambió '{$fieldName}' de '{$oldValueText}' a '{$newValueText}'";
            }
        }
        
        if (!empty($changes)) {
            $planning->update($validatedData);
            $description = 'El usuario ' . Auth::user()->name . ' ' . implode(', ', $changes);

            if ($planning->order) {
                \App\Models\CsOrderEvent::create(['cs_order_id' => $planning->cs_order_id, 'user_id' => Auth::id(), 'description' => $description . ' en la planificación.']);
            } else {
                CsPlanningEvent::create(['cs_planning_id' => $planning->id, 'user_id' => Auth::id(), 'description' => $description . '.']);
            }
        }

        return redirect()->route('customer-service.planning.show', $planning)->with('success', 'Planificación actualizada exitosamente.');
    }
    
    public function bulkEdit(Request $request)
    {
        $request->validate(['ids' => 'required|array|min:1']);
        $planningIds = $request->query('ids');
        $plannings = CsPlanning::whereIn('id', $planningIds)->get();
        $soNumbers = $plannings->pluck('so_number')->filter()->implode(', ');
        $planningsCount = count($planningIds);

        // Opciones para los menús desplegables
        $options = [
            'capacidad' => ['1 Ton', '1.5 Ton', '3.5 Ton', '4.5 Ton', 'Torthon', 'Rabón', 'Mudancero', 'Trailer 48"', 'Trailer 53"', 'Automovil', 'Motocicleta', 'Paqueteria', 'Contenedor 20"', 'Contenedor 40"', 'Contenedor 48"', 'Contenedor 53"'],
            'estado' => ['Aguascalientes', 'Baja California', 'Baja California Sur', 'Campeche', 'Chiapas', 'Chihuahua', 'Ciudad de México', 'Coahuila', 'Colima', 'Durango', 'Estado de México', 'Guanajuato', 'Guerrero', 'Hidalgo', 'Jalisco', 'Michoacán', 'Morelos', 'Nayarit', 'Nuevo León', 'Oaxaca', 'Puebla', 'Querétaro', 'Quintana Roo', 'San Luis Potosí', 'Sinaloa', 'Sonora', 'Tabasco', 'Tamaulipas', 'Tlaxcala', 'Veracruz', 'Yucatán', 'Zacatecas'],
            'servicio' => ['Local', 'Foraneo', 'Ejecutivo'],
            'region' => ['Bajio', 'Centro', 'Noreste', 'Pacifico', 'Sureste'],
            'tipo_ruta' => ['Consolidado', 'Dedicado', 'Directo'],
            'devolucion' => ['Si', 'No'],
            'custodia' => ['Sepsa', 'Planus', 'Ninguna'],
            'urgente' => ['Si', 'No'],
        ];

        return view('customer-service.planning.bulk-edit', compact('planningIds', 'planningsCount', 'options', 'soNumbers'));
    }

    /**
     * Procesa la actualización masiva de registros de planificación.
     */
    public function bulkUpdate(Request $request)
    {
        $validatedData = $request->validate([
            'ids' => 'required|string', // Viene como JSON string
            'fecha_carga' => 'nullable|date',
            'hora_carga' => 'nullable|date_format:H:i',
            'capacidad' => 'nullable|string',
            'transporte' => 'nullable|string|max:255',
            'estado' => 'nullable|string',
            'servicio' => 'nullable|string',
            'region' => 'nullable|string',
            'tipo_ruta' => 'nullable|string',
            'devolucion' => 'nullable|string',
            'custodia' => 'nullable|string',
            'operador' => 'nullable|string|max:255',
            'placas' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:255',
            'estatus_de_entrega' => 'nullable|string|max:255',
            'urgente' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'maniobras' => 'nullable|integer|min:0',
        ]);

        $planningIds = json_decode($validatedData['ids']);
        
        // Filtramos solo los campos que el usuario realmente llenó
        $dataToUpdate = collect($validatedData)->except('ids')->filter()->all();
        
        if (empty($dataToUpdate)) {
            return redirect()->route('customer-service.planning.index')->with('info', 'No se especificaron cambios para aplicar.');
        }

        // Actualizamos todos los registros en una sola consulta
        CsPlanning::whereIn('id', $planningIds)->update($dataToUpdate);

        // Creamos un evento en la línea de tiempo para cada orden afectada
        $plannings = CsPlanning::whereIn('id', $planningIds)->get();
        $changesDescription = 'actualización masiva en planificación: ' . implode(', ', array_keys($dataToUpdate));

        foreach ($plannings as $planning) {
            if ($planning->order) {
                \App\Models\CsOrderEvent::create([
                    'cs_order_id' => $planning->cs_order_id,
                    'user_id' => Auth::id(),
                    'description' => 'El usuario ' . Auth::user()->name . ' realizó una ' . $changesDescription . '.'
                ]);
            } else {
                \App\Models\CsPlanningEvent::create([
                    'cs_planning_id' => $planning->id,
                    'user_id' => Auth::id(),
                    'description' => 'El usuario ' . Auth::user()->name . ' realizó una ' . $changesDescription . '.'
                ]);
            }
        }

        return redirect()->route('customer-service.planning.index')->with('success', count($planningIds) . ' registros actualizados exitosamente.');
    }

    protected function prepareForValidation()
    {
        if ($this->has('hora_carga') && $this->input('hora_carga')) {
            try {
                // Tomamos el valor que envía el navegador (ej: "16:45" o "15:30:00")
                // y lo convertimos a un formato consistente con segundos (H:i:s).
                $formattedTime = Carbon::parse($this->input('hora_carga'))->format('H:i');
                
                // Reemplazamos el valor en la solicitud para que la validación lo reciba ya limpio.
                $this->merge(['hora_carga' => $formattedTime]);
            } catch (\Exception $e) {
                // Si el formato es inválido, no hacemos nada y dejamos que la validación falle.
            }
        }
    }   
    
    public function markAsDirect(Request $request, CsPlanning $planning)
    {
        try {
            $planning->update(['is_direct_route' => true]);

            // Esta es la respuesta que verá el JavaScript y mostrará la notificación
            return response()->json([
                'success' => true,
                'message' => 'Ruta marcada como directa exitosamente.'
            ]);

        } catch (\Exception $e) {
            Log::error("Error al marcar ruta como directa: " . $e->getMessage());
            
            // Si algo falla, también devolvemos una respuesta JSON de error
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al procesar la solicitud.'
            ], 500);
        }
    }

    public function disassociateFromGuia(\App\Models\CsPlanning $planning)
    {
        if (!$planning->guia_id) {
            return back()->with('error', 'Esta orden no está asignada a ninguna guía.');
        }

        try {
            DB::beginTransaction();
            
            // Se guarda el ID de la guía antes de desasignarla de la planificación
            $guiaId = $planning->guia_id;

            // 1. Eliminar la factura correspondiente en la tabla de facturas
            \App\Models\Factura::where('cs_planning_id', $planning->id)->delete();

            // 2. Actualizar el registro de planificación para "liberarlo"
            $planning->update([
                'guia_id' => null,
                'status' => 'En Espera',
                'operador' => 'Pendiente',
                'placas' => 'Pendiente',
                'telefono' => 'Pendiente'
            ]);

            // 3. Obtener la guía y comprobar si se quedó sin facturas
            $guia = Guia::find($guiaId);
            if ($guia && $guia->facturas()->count() === 0) {
                $guia->update(['estatus' => 'Cancelado']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al desasignar planning: " . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al procesar la solicitud.');
        }

        return back()->with('success', 'La orden ha sido desasignada de la guía exitosamente.');
    }

    public function bulkUpdateCapacity(Request $request)
    {
        $validated = $request->validate([
            'guia_id' => 'required|integer|exists:guias,id',
            'capacidad' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();
            $guia = Guia::find($validated['guia_id']);
            $planningIds = $guia->plannings()->pluck('cs_plannings.id');

            if ($planningIds->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'La guía no tiene órdenes asociadas.'], 404);
            }

            // Actualiza la capacidad en todos los registros de planificación
            CsPlanning::whereIn('id', $planningIds)->update(['capacidad' => $validated['capacidad']]);

            // Registra el evento en la línea de tiempo de cada pedido original
            $orderIds = CsPlanning::whereIn('id', $planningIds)->pluck('cs_order_id')->unique()->filter();
            $description = 'El usuario ' . auth()->user()->name . ' actualizó la Capacidad a "' . $validated['capacidad'] . '" para la guía ' . $guia->guia;

            foreach ($orderIds as $orderId) {
                CsOrderEvent::create([
                    'cs_order_id' => $orderId,
                    'user_id' => auth()->id(),
                    'description' => $description
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Capacidad actualizada exitosamente para ' . $planningIds->count() . ' órdenes.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error en bulkUpdateCapacity: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Ocurrió un error en el servidor.'], 500);
        }
    }    

}