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
use App\Models\Warehouse;

class WMSPhysicalCountController extends Controller
{
    public function index(Request $request)
    {
        $warehouseId = $request->input('warehouse_id');
        $warehouses = Warehouse::orderBy('name')->get();

        $query = PhysicalCountSession::with(['user', 'assignedUser', 'warehouse'])->latest();

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        if (!Auth::user()->isSuperAdmin()) {
            $query->where(function($q) {
                $q->where('assigned_user_id', Auth::id())
                  ->orWhere('user_id', Auth::id());
            });
        }

        $sessions = $query->paginate(15)->withQueryString();
        return view('wms.physical-counts.index', compact('sessions', 'warehouses', 'warehouseId'));
    }

    public function create()
    {
        $users = User::where('is_client', false)->orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        return view('wms.physical-counts.create', compact('users', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:cycle,full,dirigido',
            'assigned_user_id' => 'required|exists:users,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'locations_file' => 'required_if:type,dirigido|file|mimes:csv,txt',
        ]);

        DB::beginTransaction();
        try {
            $warehouseId = $validated['warehouse_id'];
            
            $session = PhysicalCountSession::create([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'assigned_user_id' => $validated['assigned_user_id'],
                'warehouse_id' => $warehouseId,
                'user_id' => Auth::id(),
                'status' => 'Pending'
            ]);

            if ($validated['type'] === 'dirigido') {
                
                $file = $request->file('locations_file');
                $csvData = array_map('str_getcsv', file($file->getRealPath()));
                array_shift($csvData); 
                $locationCodes = collect($csvData)->flatten()->map('trim')->filter()->unique();

                if ($locationCodes->isEmpty()) {
                    throw new \Exception("El archivo CSV no contiene códigos de ubicación válidos.");
                }

                $locations = Location::whereIn('code', $locationCodes)
                                     ->where('warehouse_id', $warehouseId)
                                     ->with('pallets.items')
                                     ->get();

                if ($locations->isEmpty()) {
                    throw new \Exception("Ninguna de las ubicaciones especificadas existe en el almacén seleccionado.");
                }

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
                $palletsToCount = \App\Models\WMS\Pallet::where('status', 'Finished')
                                ->whereHas('items')
                                ->whereHas('location', fn($q) => $q->where('warehouse_id', $warehouseId))
                                ->with('items')
                                ->get();
                                
                foreach ($palletsToCount as $pallet) {
                    foreach ($pallet->items as $item) {
                        $session->tasks()->create([
                            'pallet_id' => $pallet->id, 'location_id' => $pallet->location_id,
                            'product_id' => $item->product_id, 'expected_quantity' => $item->quantity,
                        ]);
                    }
                }
            }
            
            if ($session->tasks()->count() === 0) {
                 throw new \Exception("No se encontraron tareas para generar. Verifique el inventario o las ubicaciones en el almacén seleccionado.");
            }

            DB::commit();
            return redirect()->route('wms.physical-counts.show', $session)->with('success', 'Sesión de conteo creada y tareas generadas.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear la sesión: ' (e->getMessage()))->withInput();
        }
    }

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

    public function recordCount(Request $request, PhysicalCountTask $task)
    {
        $validated = $request->validate([
            'counted_quantity' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $countNumber = $task->records()->count() + 1;

            $task->records()->create([
                'user_id' => Auth::id(),
                'count_number' => $countNumber,
                'counted_quantity' => $validated['counted_quantity'],
            ]);

            if ($task->expected_quantity == $validated['counted_quantity']) {
                $task->status = 'resolved';
            } else {
                $task->status = 'discrepancy';
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
            $difference = $quantityAfter - $quantityBefore;

            $palletItemsToAdjust = \App\Models\WMS\PalletItem::where('product_id', $task->product_id)
                ->whereHas('pallet', fn($q) => $q->where('location_id', $task->location_id))
                ->get();

            if ($palletItemsToAdjust->count() > 1) {
                throw new \Exception("Existen múltiples LPNs con este producto en la misma ubicación. Realice un Ajuste Manual por LPN desde el módulo de inventario para especificar cuál tarima ajustar.");
            }
            if ($palletItemsToAdjust->isEmpty()) {
                throw new \Exception("No se encontró el LPN correspondiente en la ubicación para ajustar la cantidad.");
            }

            $palletItem = $palletItemsToAdjust->first();
            $palletItem->quantity = $quantityAfter;
            $palletItem->save();

            $stock = InventoryStock::where('product_id', $task->product_id)
                                ->where('location_id', $task->location_id)
                                ->firstOrFail();
            $stock->quantity = $quantityAfter;
            $stock->save();

            $adjustment = InventoryAdjustment::create([
                'physical_count_task_id' => $task->id,
                'pallet_item_id' => $palletItem->id,
                'product_id' => $task->product_id,
                'location_id' => $task->location_id,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'quantity_difference' => $difference,
                'reason' => 'Ajuste por Conteo Cíclico Físico.',
                'user_id' => Auth::id(),
                'source' => 'Conteo Cíclico',
            ]);

            StockMovement::create([
                'user_id' => Auth::id(),
                'product_id' => $task->product_id,
                'location_id' => $task->location_id,
                'pallet_item_id' => $palletItem->id,
                'quantity' => $difference,
                'movement_type' => 'AJUSTE-CONTEO',
                'source_id' => $adjustment->id,
                'source_type' => InventoryAdjustment::class,
            ]);
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
                'pallet:id,lpn,purchase_order_id', 
                
                'pallet.purchaseOrder:id,pedimento_a4',
                'quality:id,name'
            ])
            ->get();

        return response()->json($palletItems);
    }
}