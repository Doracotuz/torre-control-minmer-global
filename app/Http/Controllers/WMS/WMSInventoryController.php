<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\WMS\InventoryStock;
use App\Models\WMS\InventoryTransfer;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\WMS\Pallet;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\WMS\PurchaseOrder;
use App\Models\WMS\DockArrival;
use App\Models\WMS\PalletItem;
use App\Models\WMS\InventoryAdjustment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\WMS\StockMovement;
use App\Models\Warehouse;
use App\Models\Area;

class WMSInventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = Auth::user();

            if ($request->route()->getActionMethod() === 'adjustItemQuantity') {
                if (!$user->hasFfPermission('wms.inventory_adjust')) {
                    abort(403, 'No tienes permiso para ajustar inventario.');
                }
            } elseif (in_array($request->route()->getActionMethod(), ['createTransfer', 'storeTransfer', 'createSplit', 'storeSplit'])) {
                if (!$user->hasFfPermission('wms.inventory_move')) {
                    abort(403, 'No tienes permiso para mover/transferir inventario.');
                }
            } else {
                if (!$user->hasFfPermission('wms.inventory')) {
                    abort(403, 'No tienes permiso para ver el inventario WMS.');
                }
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

        $query = \App\Models\WMS\Pallet::query()
            ->with([
                'purchaseOrder:id,po_number,container_number,operator_name,download_start_time,download_end_time,document_invoice,pedimento_a4,pedimento_g1,area_id',
                'purchaseOrder.area',
                'location', 'user:id,name', 'items.product', 'items.quality'
            ])
            ->where('status', 'Finished');

        if ($warehouseId) {
            $query->whereHas('location', fn($q) => $q->where('warehouse_id', $warehouseId));
        }

        if ($areaId) {
            $query->whereHas('purchaseOrder', fn($q) => $q->where('area_id', $areaId));
        }

        if ($request->filled('lpn')) { $query->where('lpn', 'like', '%' . $request->lpn . '%'); }
        if ($request->filled('po_number')) { $query->whereHas('purchaseOrder', fn($q) => $q->where('po_number', 'like', '%' . $request->po_number . '%')); }
        if ($request->filled('sku')) { $query->whereHas('items.product', fn($q) => $q->where('sku', 'like', '%' . $request->sku . '%')); }
        if ($request->filled('pedimento_a4')) { $query->whereHas('purchaseOrder', fn($q) => $q->where('pedimento_a4', 'like', '%' . $request->pedimento_a4 . '%')); }
        if ($request->filled('quality_id')) { $query->whereHas('items.quality', fn($q) => $q->where('id', $request->quality_id)); }
        if ($request->filled('start_date')) { $query->whereDate('pallets.updated_at', '>=', $request->start_date); }
        if ($request->filled('end_date')) { $query->whereDate('pallets.updated_at', '<=', $request->end_date); }

        if ($request->filled('location')) {
            $locationTerm = $request->location;
            $query->whereHas('location', function($q) use ($locationTerm) {
                $q->where('code', 'like', "%{$locationTerm}%")
                ->orWhere(DB::raw("CONCAT(aisle,'-',rack,'-',shelf,'-',bin)"), 'like', "%{$locationTerm}%");
            });
        }        
        
        $pallets = $query->latest('updated_at')->paginate(25)->withQueryString();

        $palletItems = $pallets->pluck('items')->flatten();
        $locationIds = $pallets->pluck('location_id')->unique();
        $productIds = $palletItems->pluck('product_id')->unique();
        $qualityIds = $palletItems->pluck('quality_id')->unique();

        $stockDataQuery = \App\Models\WMS\InventoryStock::whereIn('product_id', $productIds)
            ->whereIn('quality_id', $qualityIds);

        if ($locationIds->isNotEmpty()) {
             $stockDataQuery->whereIn('location_id', $locationIds);
        }
           
        $stockData = $stockDataQuery->get();

        $stockLedger = [];
        foreach ($stockData as $stock) {
            $key = $stock->product_id . '-' . $stock->quality_id . '-' . $stock->location_id;
            $stockLedger[$key] = [
                'quantity' => $stock->quantity,
                'committed' => $stock->committed_quantity,
                'available' => $stock->quantity - $stock->committed_quantity,
            ];
        }
        
        $purchaseOrderIds = $pallets->pluck('purchaseOrder.id')->filter()->unique();
        if ($purchaseOrderIds->isNotEmpty()) {
            $latestArrivals = \App\Models\WMS\DockArrival::whereIn('id', function($query) use ($purchaseOrderIds) {
                $query->selectRaw('max(id)')
                    ->from('dock_arrivals')
                    ->whereIn('purchase_order_id', $purchaseOrderIds)
                    ->groupBy('purchase_order_id');
            })->get()->keyBy('purchase_order_id');

            $pallets->each(function($pallet) use ($latestArrivals) {
                if ($pallet->purchaseOrder) {
                    $pallet->purchaseOrder->setRelation('latestArrival', $latestArrivals->get($pallet->purchaseOrder->id));
                }
            });
        }

        $kpiBasePallet = \App\Models\WMS\Pallet::where('status', 'Finished');
        $kpiBaseStock = \App\Models\WMS\InventoryStock::query();
        $kpiBaseLocation = \App\Models\Location::where('type', 'Storage');

        if ($warehouseId) {
            $kpiBasePallet->whereHas('location', fn($q) => $q->where('warehouse_id', $warehouseId));
            $kpiBaseStock->whereHas('location', fn($q) => $q->where('warehouse_id', $warehouseId));
            $kpiBaseLocation->where('warehouse_id', $warehouseId);
        }

        if ($areaId) {
            $kpiBasePallet->whereHas('purchaseOrder', fn($q) => $q->where('area_id', $areaId));
            $kpiBaseStock->whereHas('product.area', fn($q) => $q->where('id', $areaId));
        }

        $kpis = [
            'total_pallets' => (clone $kpiBasePallet)->count(),
            'total_units' => (clone $kpiBaseStock)->sum('quantity'),
            'total_skus' => (clone $kpiBaseStock)->where('quantity', '>', 0)->distinct('product_id')->count(),
            'available_locations' => (clone $kpiBaseLocation)->whereDoesntHave('pallets')->count(),
        ];
        
        $qualities = \App\Models\WMS\Quality::orderBy('name')->get();

        return view('wms.inventory.index', compact('pallets', 'kpis', 'qualities', 'stockLedger', 'warehouses', 'warehouseId', 'areas', 'areaId'));
    }

    public function createTransfer()
    {
        return view('wms.inventory.transfer.create');
    }

public function findLpnForTransfer(Request $request)
    {
        $request->validate(['lpn' => 'required|string']);

        $sanitizedLpn = preg_replace('/[^A-Z0-9]/', '', strtoupper($request->lpn));

        $pallet = Pallet::where('lpn', $sanitizedLpn)
            ->with(['location', 'items.product', 'items.quality', 'purchaseOrder'])
            ->first();

        if (!$pallet) {
            return response()->json(['error' => 'LPN no encontrado en el inventario.'], 404);
        }

        return response()->json($pallet);
    }

    public function storeTransfer(Request $request)
    {
        $validated = $request->validate([
            'pallet_id' => 'required|exists:pallets,id',
            'destination_location_code' => 'required|string|exists:locations,code',
        ]);

        DB::beginTransaction();
        try {
            $pallet = Pallet::with(['items', 'location.warehouse'])->findOrFail($validated['pallet_id']);

            if ($pallet->items->isEmpty() || $pallet->items->sum('quantity') <= 0) {
                throw new \Exception("La tarima {$pallet->lpn} está vacía. No se pueden transferir tarimas sin inventario físico.");
            }

            $originLocation = $pallet->location;

            if (!$originLocation || !$originLocation->warehouse_id) {
                throw new \Exception("La tarima no tiene una ubicación de origen válida o la ubicación no tiene un almacén asignado.");
            }
            $currentWarehouseId = $originLocation->warehouse_id;

            $destinationLocation = Location::where('code', $validated['destination_location_code'])
                                              ->where('warehouse_id', $currentWarehouseId)
                                              ->first();

            if (!$destinationLocation) {
                throw new \Exception("Ubicación de destino no encontrada o no pertenece a este almacén.");
            }

            if ($originLocation->id === $destinationLocation->id) {
                return back()->with('error', 'La ubicación de origen y destino no pueden ser la misma.');
            }

            $pallet->location_id = $destinationLocation->id;
            $pallet->user_id = Auth::id();
            $pallet->last_action = 'Transferencia a ' . $destinationLocation->code;
            $pallet->save();

            foreach ($pallet->items as $item) {
                $originStock = InventoryStock::where('product_id', $item->product_id)
                    ->where('quality_id', $item->quality_id)
                    ->where('location_id', $originLocation->id)->first();
                if ($originStock) $originStock->decrement('quantity', $item->quantity);

                StockMovement::create([
                    'user_id' => Auth::id(),
                    'product_id' => $item->product_id,
                    'location_id' => $originLocation->id,
                    'pallet_item_id' => $item->id,
                    'quantity' => -$item->quantity,
                    'movement_type' => 'TRANSFER-OUT',
                    'source_id' => $pallet->id,
                    'source_type' => \App\Models\WMS\Pallet::class,
                ]);                

                $destinationStock = InventoryStock::firstOrCreate(
                    ['product_id' => $item->product_id, 'quality_id' => $item->quality_id, 'location_id' => $destinationLocation->id],
                    ['quantity' => 0]
                );
                $destinationStock->increment('quantity', $item->quantity);

                StockMovement::create([
                    'user_id' => Auth::id(),
                    'product_id' => $item->product_id,
                    'location_id' => $destinationLocation->id,
                    'pallet_item_id' => $item->id,
                    'quantity' => $item->quantity,
                    'movement_type' => 'TRANSFER-IN',
                    'source_id' => $pallet->id,
                    'source_type' => \App\Models\WMS\Pallet::class,
                ]);

            }

            DB::commit();
            return redirect()->route('wms.inventory.transfer.create')->with('success', "La tarima {$pallet->lpn} ha sido transferida exitosamente a la ubicación {$destinationLocation->code}.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error al procesar la transferencia: ' . $e->getMessage())->withInput();
        }
    }
    
    public function exportCsv(Request $request)
    {
        $fileName = 'reporte_inventario_lpn_' . date('Y-m-d') . '.csv';
        $warehouseId = $request->input('warehouse_id');
        $areaId = $request->input('area_id');

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($request, $warehouseId, $areaId) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'ID_Tarima', 'LPN', 'Area_Cliente', 'Estado_Tarima', 'Fecha_Recepcion', 'Usuario_Receptor',
                'Ubicacion_Codigo', 'Ubicacion_Pasillo', 'Ubicacion_Rack', 'Ubicacion_Nivel', 'Ubicacion_Bin',
                'N_Orden_Compra', 'Estado_Orden', 'Fecha_Esperada_Orden', 'Contenedor', 'Factura', 'Pedimento_A4', 'Pedimento_G1',
                'Fecha_Arribo_Vehiculo', 'Fecha_Salida_Vehiculo', 'Operador_Vehiculo',
                'SKU', 'Producto', 'Calidad', 'Piezas_Por_Caja', 
                'Cantidad_en_Pallet',
                'Comprometido (Locacion)',
                'Disponible (Locacion)',
                'Cantidad_Recibida_Cajas',
            ]);
            
            $query = \App\Models\WMS\Pallet::query()
                ->with(['purchaseOrder.area', 'location', 'user', 'items.product', 'items.quality'])
                ->where('status', 'Finished');

            if ($warehouseId) {
                $query->whereHas('location', fn($q) => $q->where('warehouse_id', $warehouseId));
            }

            if ($areaId) {
                $query->whereHas('purchaseOrder', fn($q) => $q->where('area_id', $areaId));
            }
                
            $query->when($request->filled('lpn'), fn($q) => $q->where('lpn', 'like', '%' . $request->lpn . '%'))
                
                ->when($request->filled('po_number'), fn($q) => 
                    $q->whereHas('purchaseOrder', fn($sq) => $sq->where('po_number', 'like', '%' . $request->po_number . '%'))
                )
                ->when($request->filled('sku'), fn($q) => 
                    $q->whereHas('items.product', fn($sq) => $sq->where('sku', 'like', '%' . $request->sku . '%'))
                )
                ->when($request->filled('pedimento_a4'), fn($q) => 
                    $q->whereHas('purchaseOrder', fn($sq) => $sq->where('pedimento_a4', 'like', '%' . $request->pedimento_a4 . '%'))
                )
                ->when($request->filled('quality_id'), fn($q) => 
                    $q->whereHas('items.quality', fn($sq) => $sq->where('id', $request->quality_id))
                )
                ->when($request->filled('start_date'), fn($q) => 
                    $q->whereDate('pallets.updated_at', '>=', $request->start_date)
                )
                ->when($request->filled('end_date'), fn($q) => 
                    $q->whereDate('pallets.updated_at', '<=', $request->end_date)
                )
                ->when($request->filled('location'), function($q) use ($request) {
                    $locationTerm = $request->location;
                    $q->whereHas('location', function($sq) use ($locationTerm) {
                        $sq->where('code', 'like', "%{$locationTerm}%")
                           ->orWhere(DB::raw("CONCAT(aisle,'-',rack,'-',shelf,'-',bin)"), 'like', "%{$locationTerm}%");
                    });
                })
                ->chunk(500, function ($pallets) use ($file) {
                    
                    $palletItems = $pallets->pluck('items')->flatten();
                    $locationIds = $pallets->pluck('location_id')->unique();
                    $productIds = $palletItems->pluck('product_id')->unique();
                    $qualityIds = $palletItems->pluck('quality_id')->unique();

                    $stockData = \App\Models\WMS\InventoryStock::whereIn('location_id', $locationIds)
                        ->whereIn('product_id', $productIds)
                        ->whereIn('quality_id', $qualityIds)
                        ->get();

                    $stockLedger = [];
                    foreach ($stockData as $stock) {
                        $key = $stock->product_id . '-' . $stock->quality_id . '-' . $stock->location_id;
                        $stockLedger[$key] = [
                            'committed' => $stock->committed_quantity,
                            'available' => $stock->quantity - $stock->committed_quantity,
                        ];
                    }

                    foreach ($pallets as $pallet) {
                        foreach ($pallet->items as $item) {
                            $piecesPerCase = $item->product->pieces_per_case > 0 ? $item->product->pieces_per_case : 1;
                            $casesReceived = ceil($item->quantity / $piecesPerCase);

                            $key = $item->product_id . '-' . $item->quality_id . '-' . $pallet->location_id;
                            $stock = $stockLedger[$key] ?? ['committed' => 0, 'available' => 0];
                            $comprometido = $stock['committed'];
                            $disponible = $stock['available'];

                            fputcsv($file, [
                                $pallet->id, $pallet->lpn, $pallet->purchaseOrder->area->name ?? 'N/A', $pallet->status, $pallet->updated_at->format('Y-m-d H:i:s'), $pallet->user->name ?? 'N/A',
                                $pallet->location->code ?? 'N/A', $pallet->location->aisle ?? '', $pallet->location->rack ?? '', $pallet->location->shelf ?? '', $pallet->location->bin ?? '',
                                $pallet->purchaseOrder->po_number ?? '', $pallet->purchaseOrder->status_in_spanish ?? '', $pallet->purchaseOrder->expected_date ?? '',
                                $pallet->purchaseOrder->container_number ?? '', $pallet->purchaseOrder->document_invoice ?? '', $pallet->purchaseOrder->pedimento_a4 ?? '', $pallet->purchaseOrder->pedimento_g1 ?? '',
                                $pallet->purchaseOrder->download_start_time ? \Carbon\Carbon::parse($pallet->purchaseOrder->download_start_time)->format('Y-m-d H:i:s') : '',
                                $pallet->purchaseOrder->download_end_time ? \Carbon\Carbon::parse($pallet->purchaseOrder->download_end_time)->format('Y-m-d H:i:s') : '',
                                $pallet->purchaseOrder->operator_name ?? '',
                                $item->product->sku ?? 'N/A', $item->product->name ?? 'N/A', $item->quality->name ?? 'N/A',
                                $item->product->pieces_per_case ?? 1, 
                                $item->quantity,
                                $comprometido,
                                $disponible,
                                $casesReceived,
                            ]);
                        }
                    }
                });
            fclose($file);
        };

        return new \Symfony\Component\HttpFoundation\StreamedResponse($callback, 200, $headers);
    }

    public function createSplit()
    {
        return view('wms.inventory.split.create');
    }

    public function storeSplit(Request $request)
    {
        $validated = $request->validate([
            'source_pallet_id' => 'required|exists:pallets,id',
            'new_lpn' => 'required|string',
            'items_to_split' => 'required|array|min:1',
            'items_to_split.*.item_id' => 'required|exists:pallet_items,id',
            'items_to_split.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $sourcePallet = Pallet::findOrFail($validated['source_pallet_id']);
            $newLpnCode = $validated['new_lpn'];

            if ($sourcePallet->lpn === $newLpnCode) {
                throw new \Exception("El LPN de destino no puede ser igual al de origen.");
            }

            $newPallet = Pallet::where('lpn', $newLpnCode)->first();

            if ($newPallet) {
                $newPallet->update([
                    'last_action' => 'Fusión (Merge) desde ' . $sourcePallet->lpn,
                    'user_id' => Auth::id()
                ]);
            } else {
                $pregeneratedLpn = \App\Models\WMS\PregeneratedLpn::where('lpn', $newLpnCode)->first();

                if (!$pregeneratedLpn || $pregeneratedLpn->is_used) {
                    throw new \Exception("El nuevo LPN no existe en el sistema o ya fue usado previamente.");
                }

                $newPallet = Pallet::create([
                    'lpn' => $newLpnCode, 
                    'purchase_order_id' => $sourcePallet->purchase_order_id,
                    'status' => 'Finished', 
                    'location_id' => $sourcePallet->location_id,
                    'user_id' => Auth::id(),
                    'last_action' => 'Creado desde Split de ' . $sourcePallet->lpn
                ]);

                $pregeneratedLpn->update(['is_used' => true]);
            }
            
            foreach ($validated['items_to_split'] as $splitData) {
                $sourceItem = PalletItem::findOrFail($splitData['item_id']);
                $splitQuantity = $splitData['quantity'];

                if ($splitQuantity > $sourceItem->quantity) {
                    throw new \Exception("La cantidad a dividir del producto {$sourceItem->product->sku} es mayor a la existente.");
                }

                $sourceItem->decrement('quantity', $splitQuantity);

                $destItem = PalletItem::where('pallet_id', $newPallet->id)
                                    ->where('product_id', $sourceItem->product_id)
                                    ->where('quality_id', $sourceItem->quality_id)
                                    ->first();

                if ($destItem) {
                    $destItem->increment('quantity', $splitQuantity);
                    $newItemId = $destItem->id;
                } else {
                    $newItem = $newPallet->items()->create([
                        'product_id' => $sourceItem->product_id, 
                        'quality_id' => $sourceItem->quality_id, 
                        'quantity' => $splitQuantity
                    ]);
                    $newItemId = $newItem->id;
                }

                StockMovement::create([
                    'user_id' => Auth::id(),
                    'product_id' => $sourceItem->product_id,
                    'location_id' => $sourcePallet->location_id,
                    'pallet_item_id' => $sourceItem->id,
                    'quantity' => -$splitQuantity,
                    'movement_type' => 'SPLIT-OUT',
                    'source_id' => $newPallet->id,
                    'source_type' => Pallet::class,
                ]);

                StockMovement::create([
                    'user_id' => Auth::id(),
                    'product_id' => $sourceItem->product_id,
                    'location_id' => $newPallet->location_id,
                    'pallet_item_id' => $newItemId,
                    'quantity' => $splitQuantity,
                    'movement_type' => 'SPLIT-IN',
                    'source_id' => $sourcePallet->id,
                    'source_type' => Pallet::class,
                ]);
            }

            $sourcePallet->user_id = Auth::id(); 
            $sourcePallet->last_action = 'Split (Origen)';
            $sourcePallet->save();

            $remainingQuantity = $sourcePallet->items()->sum('quantity');

            if ($remainingQuantity <= 0) {
                $sourcePallet->items()->delete();
                $sourcePallet->update(['status' => 'Empty']);
            }
            
            DB::commit();
            
            $msgType = $newPallet->wasRecentlyCreated ? 'Split creado' : 'Fusión completada';
            return redirect()->route('wms.inventory.index')
                           ->with('success', "$msgType. Mercancía movida a {$newPallet->lpn}.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar: ' . $e->getMessage())->withInput();
        }
    }
    
    public function showPalletInfoForm()
    {
        return view('wms.inventory.pallet-info.index', ['pallet' => null]);
    }

    public function findPalletInfo(Request $request)
    {
        $request->validate(['lpn' => 'required|string']);
        $sanitizedLpn = preg_replace('/[^A-Z0-9]/', '', strtoupper($request->lpn));

        $pallet = Pallet::where('lpn', $sanitizedLpn)
            ->with([
                'purchaseOrder.latestArrival',
                'location',
                'user',
                'items.product',
                'items.quality'
            ])
            ->first();

        if (!$pallet) {
            return back()->with('error', 'LPN no encontrado.');
        }

        $history = StockMovement::with(['user', 'product', 'location'])
            ->where(function($q) use ($pallet) {
                $q->where('source_id', $pallet->id)
                  ->where('source_type', Pallet::class);
            })
            ->orWhere(function($q) use ($pallet) {
                $q->whereHas('palletItem', fn($sq) => $sq->where('pallet_id', $pallet->id));
            })
            ->latest()
            ->get();
        
        return view('wms.inventory.pallet-info.index', compact('pallet', 'history'));
    }

    public function showLocationInfoForm()
    {
        return view('wms.inventory.location-info.index', ['location' => null, 'pallets' => collect()]);
    }

    public function findLocationInfo(Request $request)
    {
        $request->validate(['location_code' => 'required|string']);
        $locationCode = strtoupper(trim($request->location_code));

        $location = Location::where('code', $locationCode)
            ->orWhere(DB::raw("CONCAT(aisle,'-',rack,'-',shelf,'-',bin)"), $locationCode)
            ->with(['warehouse', 'area'])
            ->first();

        if (!$location) {
            return back()->with('error', 'Ubicación no encontrada.');
        }

        $pallets = Pallet::where('location_id', $location->id)
            ->where('status', 'Finished')
            ->with(['items.product', 'items.quality', 'purchaseOrder.area'])
            ->get();

        return view('wms.inventory.location-info.index', compact('location', 'pallets'));
    }

    public function adjustItemQuantity(Request $request, PalletItem $palletItem)
    {
        // Permission check handled by middleware

        $validator = Validator::make($request->all(), [
            'new_quantity' => 'required|integer|min:0',
            'reason' => 'required|string|min:5|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('open_adjustment_modal_for_item', $palletItem->id); 
        }

        $validated = $validator->validated();
        
        $pallet = $palletItem->pallet; 
        $palletItem->loadMissing('product'); 

        DB::beginTransaction();
        try {
            $oldQuantity = $palletItem->quantity;
            $newQuantity = $validated['new_quantity'];
            $difference = $newQuantity - $oldQuantity;

            $stock = InventoryStock::where('product_id', $palletItem->product_id)
                                ->where('quality_id', $palletItem->quality_id)
                                ->where('location_id', $pallet->location_id)->first();
            
            if ($stock) {
                $newStockQuantity = max(0, $stock->quantity + $difference);
                $stock->update(['quantity' => $newStockQuantity]);
            }

            $palletItem->update(['quantity' => $newQuantity]);

            $adjustment = InventoryAdjustment::create([
                'pallet_item_id' => $palletItem->id,
                'product_id' => $palletItem->product_id, 
                'location_id' => $pallet->location_id,   
                'user_id' => Auth::id(),
                'quantity_before' => $oldQuantity,
                'quantity_after' => $newQuantity,
                'quantity_difference' => $difference,
                'reason' => $validated['reason'],
                'source' => 'Ajuste Manual LPN'
            ]);

            StockMovement::create([
                'user_id' => Auth::id(),
                'product_id' => $palletItem->product_id,
                'location_id' => $pallet->location_id,
                'pallet_item_id' => $palletItem->id,
                'quantity' => $difference,
                'movement_type' => 'AJUSTE-MANUAL',
                'source_id' => $adjustment->id,
                'source_type' => \App\Models\WMS\InventoryAdjustment::class,
            ]);            

            if ($pallet) {
                $pallet->update([
                    'last_action' => 'Ajuste Item: ' . ($palletItem->product->sku ?? 'SKU N/A'),
                    'user_id' => Auth::id()
                ]);
            }

            DB::commit();
            
            return redirect()->route('wms.inventory.index')->with('success', 'Ajuste de inventario realizado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al ajustar PalletItem {$palletItem->id}: " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al procesar el ajuste: ' . $e->getMessage())
                ->with('open_adjustment_modal_for_item', $palletItem->id);
        }
    }

    public function showAdjustmentsLog(Request $request)
    {
        $warehouseId = $request->input('warehouse_id');
        $warehouses = Warehouse::orderBy('name')->get();

        $query = InventoryAdjustment::with([
            'user', 
            'palletItem.pallet', 
            'palletItem.product',
            'product',
            'location'
        ]);

        if ($warehouseId) {
            $query->whereHas('location', fn($q) => $q->where('warehouse_id', $warehouseId));
        }

        $adjustments = $query->latest()->paginate(25);
            
        return view('wms.inventory.adjustments.index', compact('adjustments', 'warehouses', 'warehouseId'));
    }

    public function apiFindLpn(Request $request)
    {
        $request->validate(['lpn' => 'required|string']);

        $sanitizedLpn = preg_replace('/[^A-Z0-9]/', '', strtoupper($request->lpn));

        $pallet = \App\Models\WMS\Pallet::where('lpn', $sanitizedLpn)
            ->with([
                'location',
                'user',
                'purchaseOrder:id,po_number',
                'items.product:id,sku,name',
                'items.quality:id,name'
            ])
            ->first();

        if (!$pallet) {
            return response()->json(['error' => 'LPN no encontrado.'], 404);
        }

        return response()->json($pallet);
    }
}