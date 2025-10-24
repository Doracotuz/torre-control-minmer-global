<?php
namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\WMS\PhysicalCountSession;
use App\Models\WMS\PhysicalCountTask;
use App\Models\WMS\InventoryStock;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\WMS\InventoryAdjustment;
use App\Models\WMS\StockMovement;

class WMSPhysicalCountController extends Controller
{
    public function index()
    {
        $query = PhysicalCountSession::with(['user', 'assignedUser'])->latest();

        if (!Auth::user()->isSuperAdmin()) {
            $query->where('assigned_user_id', Auth::id());
        }

        $sessions = $query->paginate(15);
        return view('wms.physical-counts.index', compact('sessions'));
    }

    public function create()
    {
        $users = User::where('is_client', false)->orderBy('name')->get();
        return view('wms.physical-counts.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:cycle,full,dirigido',
            'assigned_user_id' => 'required|exists:users,id',
            'locations_file' => 'required_if:type,dirigido|file|mimes:csv,txt',
        ]);

        DB::beginTransaction();
        try {
            $session = PhysicalCountSession::create($validated + ['user_id' => Auth::id()]);

            if ($validated['type'] === 'dirigido') {
                
                // --- INICIO DE LA CORRECCIÓN ---
                $file = $request->file('locations_file');
                $csvData = array_map('str_getcsv', file($file->getRealPath()));
                
                // 1. Ignoramos la primera fila (la cabecera) del archivo
                array_shift($csvData); 

                $locationCodes = collect($csvData)->flatten()->map('trim')->filter()->unique();

                if ($locationCodes->isEmpty()) {
                    throw new \Exception("El archivo CSV no contiene códigos de ubicación válidos después de la cabecera.");
                }

                $locations = Location::whereIn('code', $locationCodes)->with('pallets.items')->get();

                // 2. Verificamos si se encontró al menos una ubicación
                if ($locations->isEmpty()) {
                    throw new \Exception("Ninguna de las ubicaciones especificadas en el archivo CSV fue encontrada en el sistema.");
                }
                // --- FIN DE LA CORRECCIÓN ---

                foreach ($locations as $location) {
                    foreach ($location->pallets as $pallet) {
                        foreach ($pallet->items as $item) {
                            $session->tasks()->create([
                                'pallet_id' => $pallet->id, 'location_id' => $location->id,
                                'product_id' => $item->product_id, 'expected_quantity' => $item->quantity,
                            ]);
                        }
                    }
                }

            } else {
                // Lógica para 'cycle' y 'full' (se mantiene igual)
                $palletsToCount = \App\Models\WMS\Pallet::where('status', 'Finished')->with('items')->whereHas('items')->get();
                foreach ($palletsToCount as $pallet) {
                    foreach ($pallet->items as $item) {
                        $session->tasks()->create([
                            'pallet_id' => $pallet->id, 'location_id' => $pallet->location_id,
                            'product_id' => $item->product_id, 'expected_quantity' => $item->quantity,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('wms.physical-counts.show', $session)->with('success', 'Sesión de conteo creada y tareas generadas.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear la sesión: ' . $e->getMessage());
        }
    }

    // --- NUEVO MÉTODO PARA DESCARGAR LA PLANTILLA CSV ---
    public function downloadTemplate()
    {
        $headers = ['Content-type' => 'text/csv', 'Content-Disposition' => 'attachment; filename=plantilla_conteo_dirigido.csv'];
        $columns = ['location_code'];

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function show(PhysicalCountSession $physicalCount)
    {
        $session = $physicalCount->load([
            'user', 
            'assignedUser',
            'tasks.product', 
            'tasks.location', 
            'tasks.pallet',
            'tasks.records'
        ]);
        
        return view('wms.physical-counts.show', compact('session'));
    }

    public function showCountTask(PhysicalCountTask $task)
    {
        $task->load(['product', 'location', 'pallet']); 
        return view('wms.physical-counts.perform-task', compact('task'));
    }

    /**
     * Guarda el resultado de un conteo físico.
     */
    public function recordCount(Request $request, PhysicalCountTask $task)
    {
        $validated = $request->validate([
            'counted_quantity' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            // 1. Determinar el número de este conteo (1er, 2do, etc.)
            $countNumber = $task->records()->count() + 1;

            // 2. Guardar el registro del conteo
            $task->records()->create([
                'user_id' => Auth::id(),
                'count_number' => $countNumber,
                'counted_quantity' => $validated['counted_quantity'],
            ]);

            // 3. Actualizar el estatus de la tarea
            if ($task->expected_quantity == $validated['counted_quantity']) {
                $task->status = 'resolved'; // Conteo coincide, tarea resuelta
            } else {
                $task->status = 'discrepancy'; // Hay una diferencia, requiere atención
            }
            $task->save();

            DB::commit();
            return redirect()->route('wms.physical-counts.show', $task->physical_count_session_id)
                             ->with('success', 'Conteo para la ubicación ' . $task->location->code . ' registrado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar el conteo: ' . $e->getMessage());
        }
    }

    public function adjustInventory(Request $request, PhysicalCountTask $task)
    {
        if ($task->status !== 'discrepancy') {
            return back()->with('error', 'El ajuste solo puede realizarse en una tarea con discrepancia.');
        }
        $lastRecord = $task->records()->latest()->first();
        if (!$lastRecord) {
            return back()->with('error', 'No hay conteos registrados para esta tarea.');
        }

        DB::beginTransaction();
        try {
            $quantityBefore = $task->expected_quantity;
            $quantityAfter = $lastRecord->counted_quantity;
            $difference = $quantityAfter - $quantityBefore; // <-- Calculamos la diferencia

            // --- INICIO DE LA LÓGICA DE VALIDACIÓN DE LPN ---

            // 1. Buscamos todas las tarimas en esa ubicación que contengan ese producto
            $palletItemsToAdjust = \App\Models\WMS\PalletItem::where('product_id', $task->product_id)
                ->whereHas('pallet', fn($q) => $q->where('location_id', $task->location_id))
                ->get();

            // 2. Regla de negocio: Si hay más de un LPN, no podemos ajustar automáticamente.
            if ($palletItemsToAdjust->count() > 1) {
                throw new \Exception("Existen múltiples LPNs con este producto en la misma ubicación. Realice un Ajuste Manual por LPN desde el módulo de inventario para especificar cuál tarima ajustar.");
            }
            if ($palletItemsToAdjust->isEmpty()) {
                throw new \Exception("No se encontró el LPN correspondiente en la ubicación para ajustar la cantidad.");
            }

            // 3. Si solo hay un LPN, actualizamos su cantidad
            $palletItem = $palletItemsToAdjust->first();
            $palletItem->quantity = $quantityAfter;
            $palletItem->save();

            // --- FIN DE LA LÓGICA DE VALIDACIÓN ---

            // 4. Actualizamos el stock general (esta parte ya es correcta)
            $stock = InventoryStock::where('product_id', $task->product_id)
                                ->where('location_id', $task->location_id)
                                ->firstOrFail();
            $stock->quantity = $quantityAfter;
            $stock->save();

            // 5. Creamos el registro de auditoría, ahora incluyendo el pallet_item_id
            $adjustment = InventoryAdjustment::create([ // <-- Guardamos la instancia en una variable
                'physical_count_task_id' => $task->id,
                'pallet_item_id' => $palletItem->id, // Ahora registramos qué item se afectó
                'product_id' => $task->product_id,
                'location_id' => $task->location_id,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'quantity_difference' => $difference, // Usamos la diferencia calculada
                'reason' => 'Ajuste por Conteo Cíclico Físico.',
                'user_id' => Auth::id(),
                'source' => 'Conteo Cíclico',
            ]);

            // --- INICIO DE NUEVO CÓDIGO ---
            // 6. Registrar el movimiento en el Libro Mayor (Ledger)
            StockMovement::create([
                'user_id' => Auth::id(),
                'product_id' => $task->product_id,
                'location_id' => $task->location_id,
                'pallet_item_id' => $palletItem->id,
                'quantity' => $difference, // La diferencia ya tiene el signo (+ o -)
                'movement_type' => 'AJUSTE-CONTEO',
                'source_id' => $adjustment->id, // La fuente es el registro de Ajuste
                'source_type' => InventoryAdjustment::class,
            ]);
            // --- FIN DE NUEVO CÓDIGO ---

            // 7. Marcamos la tarea como resuelta
            $task->status = 'resolved';
            $task->save();

            DB::commit();
            return redirect()->route('wms.physical-counts.show', $task->physical_count_session_id)
                            ->with('success', 'Inventario ajustado correctamente en el LPN y en el stock general.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar el ajuste: ' . $e->getMessage());
        }
    }

    public function getCandidateLpns(PhysicalCountTask $task)
    {
        $palletItems = \App\Models\WMS\PalletItem::where('product_id', $task->product_id)
            ->whereHas('pallet', fn($q) => $q->where('location_id', $task->location_id))
            ->with([
                // --- ¡AQUÍ ESTÁ LA CORRECCIÓN! ---
                // Le pedimos explícitamente la clave foránea para que pueda cargar la relación
                'pallet:id,lpn,purchase_order_id', 
                
                'pallet.purchaseOrder:id,pedimento_a4',
                'quality:id,name'
            ])
            ->get();

        return response()->json($palletItems);
    }

}