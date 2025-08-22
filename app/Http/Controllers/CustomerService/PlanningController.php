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

class PlanningController extends Controller
{
    public function index()
    {
        $warehouses = \App\Models\CsWarehouse::orderBy('name')->get();
        
        // CORRECCIÓN: Se añade la lista completa de columnas.
        $allColumns = [
            'guia' => 'Guía',
            'fecha_carga' => 'F. Carga',
            'hora_carga' => 'H. Carga',
            'fecha_entrega' => 'F. Entrega',
            'origen' => 'Origen',
            'direccion' => 'Dirección',
            'razon_social' => 'Razón Social',
            'hora_cita' => 'Hora Cita',
            'so_number' => 'SO',
            'factura' => 'Factura',
            'pzs' => 'Pzs',
            'cajas' => 'Cajas',
            'subtotal' => 'Subtotal',
            'canal' => 'Canal',
            'capacidad' => 'Capacidad',
            'transporte' => 'Transporte',
            'destino' => 'Destino',
            'estado' => 'Estado',
            'servicio' => 'Servicio',
            'region' => 'Región',
            'tipo_ruta' => 'Tipo de Ruta',
            'devolucion' => 'Devolución',
            'custodia' => 'Custodia',
            'operador' => 'Operador',
            'placas' => 'Placas',
            'telefono' => 'Teléfono',
            'estatus_de_entrega' => 'Estatus Entrega',
            'urgente' => 'Urgente',
            'status' => 'Estatus General' // Se renombra para evitar confusión con "Estatus Entrega"
        ];

        return view('customer-service.planning.index', compact('warehouses', 'allColumns'));
    }

    public function filter(Request $request)
    {
        $query = CsPlanning::with('guia')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('so_number', 'like', $searchTerm)
                  ->orWhere('factura', 'like', $searchTerm)
                  ->orWhere('razon_social', 'like', $searchTerm)
                  ->orWhere('origen', 'like', $searchTerm)
                  ->orWhere('destino', 'like', $searchTerm);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('fecha_entrega', [$request->date_from, $request->date_to]);
        }

        $plannings = $query->paginate(15)->withQueryString();

        return response()->json($plannings);
    }
    
    public function schedule(CsPlanning $planning)
    {
        $planning->update(['status' => 'Programada']);
        return back()->with('success', 'La ruta ha sido programada.');
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
                    
                    // --- CAMBIO CLAVE 1: Marcamos el nuevo registro como una escala ---
                    $newPlanningRecord->is_scale = true;

                    $newPlanningRecord->save();
                }

                // --- CAMBIO CLAVE 2: Eliminamos el registro original ---
                $planning->delete();
            });

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al guardar las escalas: ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Escalas creadas y ruta original eliminada exitosamente.']);
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
        ];

        return view('customer-service.planning.edit', compact('planning', 'options'));
    }

    /**
     * Actualiza un registro de planificación y crea un evento en la línea de tiempo.
     */
    public function update(Request $request, CsPlanning $planning)
    {
        $validatedData = $request->validate([
            'fecha_carga' => 'nullable|date',
            'hora_carga' => 'nullable|date_format:H:i',
            'hora_cita' => 'nullable|string|max:255',
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
        ]);
        
        $originalData = $planning->getOriginal();
        $changes = [];

        // Compara los datos viejos con los nuevos para registrar los cambios
        foreach ($validatedData as $key => $value) {
            if ($originalData[$key] != $value) {
                $fieldName = ucwords(str_replace('_', ' ', $key));
                $oldValue = $originalData[$key] ?? 'vacío';
                $newValue = $value ?? 'vacío';
                $changes[] = "cambió '{$fieldName}' de '{$oldValue}' a '{$newValue}' en la planificación";
            }
        }
        
        // Si hubo cambios, actualiza el registro y crea el evento
        if (!empty($changes)) {
            $planning->update($validatedData);

            CsOrderEvent::create([
                'cs_order_id' => $planning->cs_order_id, // El evento se asocia a la ORDEN
                'user_id' => Auth::id(),
                'description' => 'El usuario ' . Auth::user()->name . ' ' . implode(', ', $changes) . '.'
            ]);
        }

        return redirect()->route('customer-service.planning.show', $planning)->with('success', 'Planificación actualizada exitosamente.');
    }   
    
    public function bulkEdit(Request $request)
    {
        $request->validate(['ids' => 'required|array|min:1']);
        $planningIds = $request->query('ids');
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

        return view('customer-service.planning.bulk-edit', compact('planningIds', 'planningsCount', 'options'));
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
            CsOrderEvent::create([
                'cs_order_id' => $planning->cs_order_id,
                'user_id' => Auth::id(),
                'description' => 'El usuario ' . Auth::user()->name . ' realizó una ' . $changesDescription . '.'
            ]);
        }

        return redirect()->route('customer-service.planning.index')->with('success', count($planningIds) . ' registros actualizados exitosamente.');
    }
    
    public function markAsDirect(Request $request, CsPlanning $planning)
    {
        $planning->update(['is_direct_route' => true]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'Ruta marcada como directa exitosamente.']);
        }

        return back()->with('success', 'Ruta marcada como directa.');
    }    

}