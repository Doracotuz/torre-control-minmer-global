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

class WMSInventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\WMS\Pallet::query()
            ->with([
                'purchaseOrder:id,po_number,container_number,operator_name,download_start_time,pedimento_a4,pedimento_g1',
                'location', 'user:id,name', 'items.product', 'items.quality'
            ])
            ->where('status', 'Finished');

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

        $stockData = \App\Models\WMS\InventoryStock::whereIn('location_id', $locationIds)
            ->whereIn('product_id', $productIds)
            ->whereIn('quality_id', $qualityIds)
            ->get();

        $stockLedger = [];
        foreach ($stockData as $stock) {
            $key = $stock->product_id . '-' . $stock->quality_id . '-' . $stock->location_id;
            $stockLedger[$key] = [
                'quantity' => $stock->quantity,
                'committed' => $stock->committed_quantity,
                'available' => $stock->quantity - $stock->committed_quantity,
            ];
        }
        
        // --- FIN DE MODIFICACIÓN ---


        // 3. Cargar manualmente la información del 'latestArrival' ...
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

        // --- KPIs Ampliados ---
        $kpis = [
            'total_pallets' => \App\Models\WMS\Pallet::where('status', 'Finished')->count(),
            'total_units' => \App\Models\WMS\InventoryStock::sum('quantity'),
            'total_skus' => \App\Models\WMS\InventoryStock::where('quantity', '>', 0)->distinct('product_id')->count(),
            'available_locations' => \App\Models\Location::where('type', 'Storage')->whereDoesntHave('pallets')->count(),
        ];
        $qualities = \App\Models\WMS\Quality::orderBy('name')->get();

        // 4. Pasar el nuevo mapa $stockLedger a la vista
        return view('wms.inventory.index', compact('pallets', 'kpis', 'qualities', 'stockLedger'));
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
            ->with(['location', 'items.product', 'items.quality'])
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
            'destination_location_code' => 'required|exists:locations,code',
        ]);

        DB::beginTransaction();
        try {
            $pallet = Pallet::with('items')->findOrFail($validated['pallet_id']);
            $originLocation = $pallet->location;
            $destinationLocation = Location::where('code', $validated['destination_location_code'])->firstOrFail();

            if ($originLocation->id === $destinationLocation->id) {
                return back()->with('error', 'La ubicación de origen y destino no pueden ser la misma.');
            }

            // Actualiza la ubicación de la tarima
            $pallet->location_id = $destinationLocation->id;
            $pallet->user_id = Auth::id();
            $pallet->last_action = 'Transferencia a ' . $destinationLocation->code;
            $pallet->save();

            // Mueve el stock de cada item de la tarima
            foreach ($pallet->items as $item) {
                // Decrementa stock en origen
                $originStock = InventoryStock::where('product_id', $item->product_id)
                    ->where('quality_id', $item->quality_id)
                    ->where('location_id', $originLocation->id)->first();
                if ($originStock) $originStock->decrement('quantity', $item->quantity);

                // Incrementa stock en destino
                $destinationStock = InventoryStock::firstOrCreate(
                    ['product_id' => $item->product_id, 'quality_id' => $item->quality_id, 'location_id' => $destinationLocation->id],
                    ['quantity' => 0]
                );
                $destinationStock->increment('quantity', $item->quantity);
            }

            DB::commit();
            return redirect()->route('wms.inventory.index')->with('success', "La tarima {$pallet->lpn} ha sido transferida exitosamente a la ubicación {$destinationLocation->code}.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error al procesar la transferencia: ' . $e->getMessage())->withInput();
        }
    }
    
    public function exportCsv(Request $request)
    {
        $fileName = 'reporte_inventario_lpn_' . date('Y-m-d') . '.csv';

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($request) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para acentos

            // --- Encabezados Exhaustivos (CON NUEVAS COLUMNAS) ---
            fputcsv($file, [
                'ID_Tarima', 'LPN', 'Estado_Tarima', 'Fecha_Recepcion', 'Usuario_Receptor',
                'Ubicacion_Codigo', 'Ubicacion_Pasillo', 'Ubicacion_Rack', 'Ubicacion_Nivel', 'Ubicacion_Bin',
                'N_Orden_Compra', 'Estado_Orden', 'Fecha_Esperada_Orden', 'Contenedor', 'Factura', 'Pedimento_A4', 'Pedimento_G1',
                'Fecha_Arribo_Vehiculo', 'Fecha_Salida_Vehiculo', 'Operador_Vehiculo',
                'SKU', 'Producto', 'Calidad', 'Piezas_Por_Caja', 
                'Cantidad_en_Pallet', // <-- NOMBRE CAMBIADO
                'Comprometido (Locacion)', // <-- NUEVA COLUMNA
                'Disponible (Locacion)', // <-- NUEVA COLUMNA
                'Cantidad_Recibida_Cajas',
            ]);

            // Procesamiento por lotes
            \App\Models\WMS\Pallet::query()
                ->with(['purchaseOrder', 'location', 'user', 'items.product', 'items.quality'])
                ->where('status', 'Finished')
                ->when($request->filled('lpn'), fn($q) => $q->where('lpn', 'like', '%' . $request->lpn . '%'))
                // ... (resto de filtros replicados)
                ->chunk(500, function ($pallets) use ($file) {
                    
                    // --- INICIO DE MODIFICACIÓN: OBTENER STOCK PARA EL CHUNK ---
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
                    // --- FIN DE MODIFICACIÓN ---

                    foreach ($pallets as $pallet) {
                        foreach ($pallet->items as $item) {
                            $piecesPerCase = $item->product->pieces_per_case > 0 ? $item->product->pieces_per_case : 1;
                            $casesReceived = ceil($item->quantity / $piecesPerCase);

                            // Buscar los datos del ledger
                            $key = $item->product_id . '-' . $item->quality_id . '-' . $pallet->location_id;
                            $stock = $stockLedger[$key] ?? ['committed' => 0, 'available' => 0];
                            $comprometido = $stock['committed'];
                            $disponible = $stock['available'];

                            fputcsv($file, [
                                $pallet->id, $pallet->lpn, $pallet->status, $pallet->updated_at->format('Y-m-d H:i:s'), $pallet->user->name ?? 'N/A',
                                $pallet->location->code ?? 'N/A', $pallet->location->aisle ?? '', $pallet->location->rack ?? '', $pallet->location->shelf ?? '', $pallet->location->bin ?? '',
                                $pallet->purchaseOrder->po_number ?? '', $pallet->purchaseOrder->status_in_spanish ?? '', $pallet->purchaseOrder->expected_date ?? '',
                                $pallet->purchaseOrder->container_number ?? '', $pallet->purchaseOrder->document_invoice ?? '', $pallet->purchaseOrder->pedimento_a4 ?? '', $pallet->purchaseOrder->pedimento_g1 ?? '',
                                $pallet->purchaseOrder->download_start_time ? \Carbon\Carbon::parse($pallet->purchaseOrder->download_start_time)->format('Y-m-d H:i:s') : '',
                                $pallet->purchaseOrder->download_end_time ? \Carbon\Carbon::parse($pallet->purchaseOrder->download_end_time)->format('Y-m-d H:i:s') : '',
                                $pallet->purchaseOrder->operator_name ?? '',
                                $item->product->sku ?? 'N/A', $item->product->name ?? 'N/A', $item->quality->name ?? 'N/A',
                                $item->product->pieces_per_case ?? 1, 
                                $item->quantity, // Cantidad en Pallet
                                $comprometido,   // Comprometido (Locación)
                                $disponible,     // Disponible (Locación)
                                $casesReceived,
                            ]);
                        }
                    }
                });
            fclose($file);
        };

        return new \Symfony\Component\HttpFoundation\StreamedResponse($callback, 200, [
            "Content-type" => "text/csv; charset=UTF-8", "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache", "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }

    public function createSplit()
    {
        return view('wms.inventory.split.create');
    }

    public function storeSplit(Request $request)
    {
        $validated = $request->validate([
            'source_pallet_id' => 'required|exists:pallets,id',
            'new_lpn' => 'required|string|unique:pallets,lpn',
            'items_to_split' => 'required|array|min:1',
            'items_to_split.*.item_id' => 'required|exists:pallet_items,id',
            'items_to_split.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $sourcePallet = Pallet::findOrFail($validated['source_pallet_id']);
            $pregeneratedLpn = \App\Models\WMS\PregeneratedLpn::where('lpn', $validated['new_lpn'])->first();

            if (!$pregeneratedLpn || $pregeneratedLpn->is_used) {
                throw new \Exception("El nuevo LPN es inválido o ya está en uso.");
            }

            // Crear la nueva tarima (hereda la información)
            $newPallet = Pallet::create([
                'lpn' => $validated['new_lpn'], 'purchase_order_id' => $sourcePallet->purchase_order_id,
                'status' => 'Finished', 'location_id' => $sourcePallet->location_id,
                'user_id' => Auth::id(),
                'last_action' => 'Creado desde Split de ' . $sourcePallet->lpn
            ]);
            
            // Mover los items
            foreach ($validated['items_to_split'] as $splitData) {
                $sourceItem = \App\Models\WMS\PalletItem::findOrFail($splitData['item_id']);
                if ($splitData['quantity'] > $sourceItem->quantity) {
                    throw new \Exception("La cantidad a dividir del producto {$sourceItem->product->sku} es mayor a la existente.");
                }
                $sourceItem->decrement('quantity', $splitData['quantity']);
                $newPallet->items()->create(['product_id' => $sourceItem->product_id, 'quality_id' => $sourceItem->quality_id, 'quantity' => $splitData['quantity']]);
            }

            $pregeneratedLpn->update(['is_used' => true]);

            $sourcePallet->user_id = Auth::id(); 
            $sourcePallet->last_action = 'Split (Origen)';
            $sourcePallet->save();

            $remainingQuantity = $sourcePallet->items()->sum('quantity');

            if ($remainingQuantity <= 0) {
                $sourcePallet->items()->delete();
                $sourcePallet->update(['status' => 'Empty']);
            }
            
            DB::commit();
            return redirect()->route('wms.inventory.index')->with('success', "Split completado. Se creó la nueva tarima {$newPallet->lpn}.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar el split: ' . $e->getMessage())->withInput();
        }
    }
    
    public function showPalletInfoForm()
    {
        // Simplemente muestra la vista con el formulario de búsqueda
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
                'user', // Usuario que finalizó la recepción
                'items.product',
                'items.quality'
            ])
            ->first();

        if (!$pallet) {
            return back()->with('error', 'LPN no encontrado.');
        }
        
        // Devuelve la misma vista, pero ahora con la información del pallet
        return view('wms.inventory.pallet-info.index', compact('pallet'));
    }

    public function adjustItemQuantity(Request $request, PalletItem $palletItem)
    {
        if (!Auth::user()->isSuperAdmin()) {
            return back()->with('error', 'No tienes permisos para realizar ajustes.');
        }

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

        DB::beginTransaction();
        try {
            $oldQuantity = $palletItem->quantity;
            $newQuantity = $validated['new_quantity'];
            $difference = $newQuantity - $oldQuantity;

            $stock = InventoryStock::where('product_id', $palletItem->product_id)
                                ->where('quality_id', $palletItem->quality_id)
                                ->where('location_id', $pallet->location_id)->first();
            
            if ($stock) {
                $difference > 0 ? $stock->increment('quantity', $difference) : $stock->decrement('quantity', abs($difference));
            }

            $palletItem->update(['quantity' => $newQuantity]);

            // --- ¡AQUÍ ESTÁ LA CORRECCIÓN! ---
            // Ahora pasamos todos los datos que la tabla necesita.
            InventoryAdjustment::create([
                'pallet_item_id' => $palletItem->id,
                'product_id' => $palletItem->product_id, // <-- AÑADIDO
                'location_id' => $pallet->location_id,   // <-- AÑADIDO
                'user_id' => Auth::id(),
                'quantity_before' => $oldQuantity,
                'quantity_after' => $newQuantity,
                'quantity_difference' => $difference,
                'reason' => $validated['reason'],
                'source' => 'Ajuste Manual LPN'
            ]);

            DB::commit();
            // Redirige a la página principal de inventario para ver el resultado
            return redirect()->route('wms.inventory.index')->with('success', 'Ajuste de inventario realizado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar el ajuste: ' . $e->getMessage());
        }
    }
    public function showAdjustmentsLog()
    {
        $adjustments = InventoryAdjustment::with([
            'user', 
            'palletItem.pallet', 
            'palletItem.product',
            'product',  // <-- Carga el producto directamente
            'location'  // <-- Carga la ubicación directamente
        ])->latest()->paginate(25);
            
        return view('wms.inventory.adjustments.index', compact('adjustments'));
    }

}