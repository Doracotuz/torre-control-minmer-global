<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\WMS\PurchaseOrder;
use App\Models\WMS\Pallet;
use App\Models\WMS\InventoryStock;
use App\Models\WMS\Quality;
use App\Models\WMS\PregeneratedLpn;
use App\Models\Product;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class WMSReceivingController extends Controller
{
    // Muestra la interfaz principal de recepción
    public function showReceivingForm(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['lines.product']);
        $products = Product::orderBy('name')->get(['id', 'name', 'sku']);
        $qualities = Quality::orderBy('name')->get();

        // Suma las cantidades de items en tarimas ya finalizadas para esta orden
        $alreadyReceived = DB::table('pallet_items')
            ->join('pallets', 'pallet_items.pallet_id', '=', 'pallets.id')
            ->where('pallets.purchase_order_id', $purchaseOrder->id)
            ->select('pallet_items.product_id', DB::raw('SUM(pallet_items.quantity) as received_qty'))
            ->groupBy('pallet_items.product_id')
            ->pluck('received_qty', 'product_id');

        // Crea el resumen de progreso
        $receivingSummary = $purchaseOrder->lines->map(function ($line) use ($alreadyReceived) {
            $received = $alreadyReceived->get($line->product_id, 0);
            return [
                'product_id' => $line->product_id, 'sku' => $line->product->sku,
                'name' => $line->product->name, 'ordered' => $line->quantity_ordered,
                'received' => $received, 'balance' => $line->quantity_ordered - $received,
            ];
        });

        // --- ¡AQUÍ ESTÁ LA LÓGICA CLAVE! ---
        // Carga las tarimas que ya tienen el estado "Finished" desde la base de datos,
        // incluyendo la información de su contenido y del usuario que la recibió.
        $finishedPallets = Pallet::where('purchase_order_id', $purchaseOrder->id)
            ->where('status', 'Finished') 
            ->with(['items.product', 'items.quality', 'user']) // Asegura cargar el usuario
            ->latest() // Muestra las más recientes primero
            ->get();

        return view('wms.receiving.show', compact(
            'purchaseOrder', 
            'products', 
            'qualities', 
            'receivingSummary', 
            'finishedPallets' // Enviamos el historial persistente a la vista
        ));
    }

    // Inicia una tarima usando un LPN pre-generado
    public function startPallet(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'lpn' => 'required|string',
        ]);

        // --- NUEVA LÍNEA DE "LIMPIEZA" ---
        // Convierte a mayúsculas y elimina CUALQUIER carácter que no sea letra o número.
        $sanitizedLpn = preg_replace('/[^A-Z0-9]/', '', strtoupper($validated['lpn']));
        // --- FIN DE LA LÍNEA DE "LIMPIEZA" ---

        // 1. Validamos usando el LPN "limpio"
        $pregeneratedLpn = PregeneratedLpn::where('lpn', $sanitizedLpn)->first();
        if (!$pregeneratedLpn) {
            return response()->json(['error' => 'El LPN no existe o no fue pre-generado.'], 404);
        }
        if ($pregeneratedLpn->is_used) {
            return response()->json(['error' => 'Este LPN ya ha sido utilizado y finalizado.'], 409);
        }

        try {
            $receivingLocation = Location::where('type', 'receiving')->firstOrFail();
            
            $pallet = Pallet::firstOrCreate(
                [
                    'lpn' => $sanitizedLpn, // Usamos el LPN limpio
                    'purchase_order_id' => $validated['purchase_order_id'],
                ],
                [
                    'status' => 'Receiving',
                    'location_id' => $receivingLocation->id,
                ]
            );

            if (!$pallet->wasRecentlyCreated && $pallet->status !== 'Receiving') {
                return response()->json(['error' => 'Este LPN pertenece a una tarima ya procesada.'], 409);
            }

            return response()->json($pallet->load('items.product', 'items.quality'));

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error de servidor al procesar el LPN: ' . $e->getMessage()], 500);
        }
    }

    // Añade un producto con su calidad a una tarima existente
    public function addItemToPallet(Request $request, Pallet $pallet)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'quality_id' => 'required|exists:qualities,id',
        ]);

        DB::beginTransaction();
        try {
            // --- VALIDACIÓN DE SOBRE-RECEPCIÓN ---
            $purchaseOrderLine = $pallet->purchaseOrder->lines()->where('product_id', $validated['product_id'])->first();
            if (!$purchaseOrderLine) {
                return response()->json(['error' => 'Este producto no pertenece a la orden de compra.'], 422);
            }

            // Calcula cuánto se ha recibido YA para este producto en TODA la orden de compra
            $totalAlreadyReceived = DB::table('pallet_items')
                ->join('pallets', 'pallet_items.pallet_id', '=', 'pallets.id')
                ->where('pallets.purchase_order_id', $pallet->purchase_order_id)
                ->where('pallet_items.product_id', $validated['product_id'])
                ->sum('pallet_items.quantity');
            
            $quantityOrdered = $purchaseOrderLine->quantity_ordered;

            // Si lo ya recibido + lo nuevo que se quiere añadir > a lo ordenado, envía un error
            if (($totalAlreadyReceived + $validated['quantity']) > $quantityOrdered) {
                return response()->json([
                    'error' => 'La cantidad a recibir excede lo ordenado. Total ordenado: ' . $quantityOrdered
                ], 422);
            }
            
            // --- LÓGICA DE INSERCIÓN CORREGIDA ---
            $palletItem = $pallet->items()->firstOrCreate(
                [
                    'product_id' => $validated['product_id'],
                    'quality_id' => $validated['quality_id'],
                ],
                ['quantity' => 0] // Si no existe, lo crea con cantidad 0
            );
            
            // Incrementa la cantidad en la línea de la tarima
            $palletItem->increment('quantity', $validated['quantity']);

            // Incrementa el stock general
            $stock = InventoryStock::firstOrCreate(
                ['product_id' => $validated['product_id'], 'location_id' => $pallet->location_id, 'quality_id' => $validated['quality_id']],
                ['quantity' => 0]
            );
            $stock->increment('quantity', $validated['quantity']);

            DB::commit();
            return response()->json($pallet->load('items.product', 'items.quality'));

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error de servidor: ' . $e->getMessage()], 500);
        }
    }

    public function finishPallet(Request $request, Pallet $pallet)
    {
        if ($pallet->items()->count() === 0) {
            $pallet->delete();
            return response()->json(['success' => true, 'message' => 'Tarima vacía eliminada.']);
        }

        DB::beginTransaction();
        try {
            // 1. Compromete el LPN y finaliza la tarima (lógica existente)
            $pregeneratedLpn = PregeneratedLpn::where('lpn', $pallet->lpn)->firstOrFail();
            $pregeneratedLpn->update(['is_used' => true]);
            
            $pallet->update([
                'status' => 'Finished',
                'user_id' => Auth::id()
            ]);

            // --- LÓGICA NUEVA Y CRUCIAL ---
            // 2. Obtenemos la Orden de Compra a la que pertenece la tarima
            $purchaseOrder = $pallet->purchaseOrder;

            // 3. Recalculamos el total de unidades recibidas para TODA la orden de compra
            // sumando los items de TODAS las tarimas asociadas.
            $totalReceived = DB::table('pallet_items')
                ->join('pallets', 'pallet_items.pallet_id', '=', 'pallets.id')
                ->where('pallets.purchase_order_id', $purchaseOrder->id)
                ->sum('pallet_items.quantity');

            // 4. Actualizamos el campo 'received_bottles' en la orden de compra principal
            $purchaseOrder->update(['received_bottles' => $totalReceived]);
            // --- FIN DE LA LÓGICA NUEVA ---

            DB::commit();
            
            return response()->json($pallet->load('user'));
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al finalizar la tarima: ' . $e->getMessage()], 500);
        }
    }

    public function updatePalletItem(Request $request, \App\Models\WMS\PalletItem $palletItem)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'quality_id' => 'required|exists:qualities,id',
        ]);

        DB::beginTransaction();
        try {
            $pallet = $palletItem->pallet;

            // 1. Revertir el stock del item original
            $oldStock = InventoryStock::where('product_id', $palletItem->product_id)
                ->where('location_id', $pallet->location_id)
                ->where('quality_id', $palletItem->quality_id)->first();
            if ($oldStock) $oldStock->decrement('quantity', $palletItem->quantity);
            
            // 2. Actualizar el item de la tarima con los nuevos datos
            $palletItem->update($validated);

            // 3. Incrementar el stock con los nuevos datos
            $newStock = InventoryStock::firstOrCreate(
                ['product_id' => $validated['product_id'], 'location_id' => $pallet->location_id, 'quality_id' => $validated['quality_id']],
                ['quantity' => 0]
            );
            $newStock->increment('quantity', $validated['quantity']);

            DB::commit();
            return response()->json($pallet->load('items.product', 'items.quality'));

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar el item: ' . $e->getMessage()], 500);
        }
    }

    public function destroyPalletItem(\App\Models\WMS\PalletItem $palletItem)
    {
        DB::beginTransaction();
        try {
            $pallet = $palletItem->pallet;

            // 1. Revertir el stock del item que se va a eliminar
            $stock = InventoryStock::where('product_id', $palletItem->product_id)
                ->where('location_id', $pallet->location_id)
                ->where('quality_id', $palletItem->quality_id)->first();
            if ($stock) $stock->decrement('quantity', $palletItem->quantity);
            
            // 2. Eliminar el item de la tarima
            $palletItem->delete();

            DB::commit();
            return response()->json($pallet->load('items.product', 'items.quality'));
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al eliminar el item: ' . $e->getMessage()], 500);
        }
    }

}