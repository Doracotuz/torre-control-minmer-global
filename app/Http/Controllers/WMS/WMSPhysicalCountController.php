<?php
namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\WMS\PhysicalCountSession;
use App\Models\WMS\PhysicalCountTask;
use App\Models\WMS\InventoryStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\WMS\InventoryAdjustment;

class WMSPhysicalCountController extends Controller
{
    public function index()
    {
        $sessions = PhysicalCountSession::with('user')->latest()->paginate(15);
        return view('wms.physical-counts.index', compact('sessions'));
    }

    public function create()
    {
        return view('wms.physical-counts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:cycle,full',
        ]);

        DB::beginTransaction();
        try {
            $session = PhysicalCountSession::create([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'user_id' => Auth::id(),
            ]);

            // Lógica para generar tareas de conteo
            // Simplificado: contaremos todo el inventario
            $stocksToCount = InventoryStock::where('quantity', '>', 0)->get();

            foreach ($stocksToCount as $stock) {
                $session->tasks()->create([
                    'product_id' => $stock->product_id,
                    'location_id' => $stock->location_id,
                    'expected_quantity' => $stock->quantity,
                ]);
            }

            DB::commit();
            return redirect()->route('wms.physical-counts.show', $session)->with('success', 'Sesión de conteo creada. Se han generado las tareas.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear la sesión: ' . $e->getMessage());
        }
    }

    public function show(PhysicalCountSession $physicalCount)
    {
        // Renombramos la variable para claridad
        $session = $physicalCount->load(['user', 'tasks.product', 'tasks.location', 'tasks.records']);
        return view('wms.physical-counts.show', compact('session'));
    }

    public function showCountTask(PhysicalCountTask $task)
    {
        $task->load(['product', 'location']);
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
        // 1. Reglas de Negocio: Solo se puede ajustar si hay discrepancia.
        // (Eliminé la regla de los 3 conteos para darte más flexibilidad, pero puedes volver a añadirla si quieres).
        if ($task->status !== 'discrepancy') {
            return back()->with('error', 'El ajuste solo puede realizarse en una tarea con discrepancia.');
        }

        $lastRecord = $task->records()->latest()->first();
        if (!$lastRecord) {
            return back()->with('error', 'No hay conteos registrados para esta tarea.');
        }

        DB::beginTransaction();
        try {
            // 2. Cálculo de Cantidades
            $quantityBefore = $task->expected_quantity; // La cantidad que el sistema esperaba
            $quantityAfter = $lastRecord->counted_quantity; // La cantidad final contada
            $quantityDifference = $quantityAfter - $quantityBefore; // La diferencia

            // 3. Actualización de Stock
            // Se busca el registro de stock para este producto y ubicación.
            $stock = InventoryStock::where('product_id', $task->product_id)
                                ->where('location_id', $task->location_id)
                                ->firstOrFail();
            
            // Se actualiza la cantidad de forma segura.
            $stock->quantity = $quantityAfter;
            $stock->save();

            // 4. Registro de Auditoría Detallado
            // Se crea un registro inmutable del ajuste.
            InventoryAdjustment::create([
                'physical_count_task_id' => $task->id,
                'product_id' => $task->product_id,
                'location_id' => $task->location_id,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'quantity_difference' => $quantityDifference,
                'reason' => 'Ajuste por Conteo Cíclico Físico.',
                'user_id' => Auth::id(), // El supervisor que aprueba el ajuste
                'source' => 'Conteo Cíclico',
            ]);

            // 5. Marcar la tarea como resuelta
            $task->status = 'resolved';
            $task->save();

            DB::commit();
            return redirect()->route('wms.physical-counts.show', $task->physical_count_session_id)
                            ->with('success', 'Inventario ajustado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar el ajuste: ' . $e->getMessage());
        }
    }
}