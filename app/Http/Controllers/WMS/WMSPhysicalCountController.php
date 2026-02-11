<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\WMS\PhysicalCountSession;
use App\Models\WMS\PhysicalCountTask;
use App\Models\WMS\InventoryStock;
use App\Models\Location;
use App\Models\User;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\WMS\InventoryAdjustment;
use App\Models\WMS\StockMovement;
use App\Models\Warehouse;

class WMSPhysicalCountController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->hasFfPermission('wms.physical_counts')) {
                abort(403, 'No tienes permiso para realizar conteos físicos/ajustes.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $warehouseId = $request->input('warehouse_id');
        $areaId = $request->input('area_id');
        
        $warehouses = Warehouse::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();

        $query = PhysicalCountSession::with(['user', 'assignedUser', 'warehouse', 'area', 'tasks'])->latest();

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($areaId) {
            $query->where('area_id', $areaId);
        }

        if (!Auth::user()->isSuperAdmin()) {
            $query->where(function($q) {
                $q->where('assigned_user_id', Auth::id())
                  ->orWhere('user_id', Auth::id());
            });
        }

        $sessions = $query->paginate(15)->withQueryString();
        return view('wms.physical-counts.index', compact('sessions', 'warehouses', 'areas', 'warehouseId', 'areaId'));
    }

    public function create()
    {
        $users = User::where('is_client', false)->orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();
        $aisles = Location::select('aisle')
                            ->whereNotNull('aisle')
                            ->distinct()
                            ->orderBy('aisle')
                            ->pluck('aisle');

        return view('wms.physical-counts.create', compact('users', 'warehouses', 'areas', 'aisles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:cycle,full,dirigido',
            'assigned_user_id' => 'required|exists:users,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'area_id' => 'nullable|exists:areas,id',
            'locations_file' => 'required_if:type,dirigido|file|mimes:csv,txt',
            'aisle' => 'required_if:type,cycle|nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $warehouseId = $validated['warehouse_id'];
            $areaId = $validated['area_id'] ?? null;
            
            $session = PhysicalCountSession::create([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'assigned_user_id' => $validated['assigned_user_id'],
                'warehouse_id' => $warehouseId,
                'area_id' => $areaId,
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
                                     ->with(['pallets' => function($q) use ($areaId) {
                                         $q->whereHas('items', fn($i) => $i->where('quantity', '>', 0));
                                         if ($areaId) {
                                             $q->whereHas('purchaseOrder', function($subQ) use ($areaId) {
                                                 $subQ->where('area_id', $areaId);
                                             });
                                         }
                                     }, 'pallets.items'])
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

            } elseif ($validated['type'] === 'cycle') {
                $palletsToCount = \App\Models\WMS\Pallet::where('status', 'Finished')
                                ->whereHas('items', fn($q) => $q->where('quantity', '>', 0))
                                ->whereHas('location', function($q) use ($warehouseId, $validated) {
                                    $q->where('warehouse_id', $warehouseId)
                                      ->where('aisle', $validated['aisle']);
                                })
                                ->when($areaId, function($q) use ($areaId) {
                                    $q->whereHas('purchaseOrder', fn($po) => $po->where('area_id', $areaId));
                                })
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

            } elseif ($validated['type'] === 'full') {
                $palletsToCount = \App\Models\WMS\Pallet::where('status', 'Finished')
                                ->whereHas('items', fn($q) => $q->where('quantity', '>', 0))
                                ->whereHas('location', fn($q) => $q->where('warehouse_id', $warehouseId))
                                ->when($areaId, function($q) use ($areaId) {
                                    $q->whereHas('purchaseOrder', fn($po) => $po->where('area_id', $areaId));
                                })
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
                 throw new \Exception("No se encontraron tareas para generar. Verifique el inventario o los filtros seleccionados (almacén, área, pasillo, etc.).");
            }

            DB::commit();
            return redirect()->route('wms.physical-counts.show', $session)->with('success', 'Sesión de conteo creada y tareas generadas.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear la sesión: ' . $e->getMessage())->withInput();
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
            'area',
            'tasks.product', 
            'tasks.location', 
            'tasks.pallet',
            'tasks.records'
        ]);
        
        return view('wms.physical-counts.show', compact('session'));
    }

    public function showCountTask(PhysicalCountTask $task)
    {
        $task->load(['product', 'location', 'pallet.purchaseOrder.area']); 
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

            $nextTask = PhysicalCountTask::where('physical_count_session_id', $task->physical_count_session_id)
                ->where('id', '!=', $task->id)
                ->where(function($q) {
                    $q->where('status', 'pending')
                      ->orWhere(function($subQ) {
                          $subQ->where('status', 'discrepancy')->has('records', '<', 3);
                      });
                })
                ->orderBy('location_id', 'asc')
                ->orderBy('id', 'asc')
                ->first();

            if ($nextTask) {
                return redirect()->route('wms.physical-counts.tasks.perform', $nextTask)
                                 ->with('success', 'Conteo guardado. Continuando con siguiente ubicación: ' . ($nextTask->location->code ?? 'N/A'));
            }

            return redirect()->route('wms.physical-counts.show', $task->physical_count_session_id)
                             ->with('success', '¡Excelente! Has completado todas las tareas pendientes de esta sesión.');

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
            $palletItemId = $request->input('pallet_item_id');
            
            if($palletItemId) {
                $palletItem = \App\Models\WMS\PalletItem::findOrFail($palletItemId);
            } else {
                $palletItemsToAdjust = \App\Models\WMS\PalletItem::where('product_id', $task->product_id)
                    ->whereHas('pallet', fn($q) => $q->where('location_id', $task->location_id))
                    ->get();

                if ($palletItemsToAdjust->count() > 1) {
                    throw new \Exception("Existen múltiples LPNs. Por favor seleccione el LPN específico en el modal.");
                }
                if ($palletItemsToAdjust->isEmpty()) {
                    throw new \Exception("No se encontró el LPN correspondiente.");
                }
                $palletItem = $palletItemsToAdjust->first();
            }

            $palletItem->quantity = $quantityAfter;
            $palletItem->save();

            $stock = InventoryStock::where('product_id', $task->product_id)
                                ->where('location_id', $task->location_id)
                                ->first();
            
            if(!$stock) {
                 $stock = InventoryStock::create([
                     'product_id' => $task->product_id,
                     'location_id' => $task->location_id,
                     'quality_id' => $palletItem->quality_id,
                     'quantity' => 0
                 ]);
            }
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
                'reason' => $request->input('reason', 'Ajuste por Conteo Cíclico.'),
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
                            ->with('success', 'Inventario ajustado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar el ajuste: ' . $e->getMessage());
        }
    }

    public function getCandidateLpns(PhysicalCountTask $task)
    {
        $palletItems = \App\Models\WMS\PalletItem::where('pallet_id', $task->pallet_id)
            ->where('product_id', $task->product_id)
            ->with([
                'pallet:id,lpn,purchase_order_id', 
                'pallet.purchaseOrder:id,pedimento_a4,area_id',
                'pallet.purchaseOrder.area:id,name',
                'quality:id,name'
            ])
            ->get();

        return response()->json($palletItems);
    }
}