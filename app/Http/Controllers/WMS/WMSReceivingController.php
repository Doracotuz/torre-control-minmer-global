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
use App\Models\WMS\StockMovement;

class WMSReceivingController extends Controller
{
    public function showReceivingForm(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['lines.product']);
        $products = Product::orderBy('name')->get(['id', 'name', 'sku', 'upc']);
        $qualities = Quality::orderBy('name')->get();

        $alreadyReceived = DB::table('pallet_items')
            ->join('pallets', 'pallet_items.pallet_id', '=', 'pallets.id')
            ->where('pallets.purchase_order_id', $purchaseOrder->id)
            ->select('pallet_items.product_id', DB::raw('SUM(pallet_items.quantity) as received_qty'))
            ->groupBy('pallet_items.product_id')
            ->pluck('received_qty', 'product_id');

        $receivingSummary = $purchaseOrder->lines->map(function ($line) use ($alreadyReceived) {
            $received = $alreadyReceived->get($line->product_id, 0);
            return [
                'product_id' => $line->product_id, 'sku' => $line->product->sku,
                'name' => $line->product->name, 'ordered' => $line->quantity_ordered,
                'received' => $received, 'balance' => $line->quantity_ordered - $received,
            ];
        });

        $finishedPallets = Pallet::where('purchase_order_id', $purchaseOrder->id)
            ->where('status', 'Finished') 
            ->with(['items.product', 'items.quality', 'user'])
            ->latest()
            ->get();

        return view('wms.receiving.show', compact(
            'purchaseOrder', 
            'products', 
            'qualities', 
            'receivingSummary', 
            'finishedPallets'
        ));
    }

    public function startPallet(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'lpn' => 'required|string',
        ]);

        $sanitizedLpn = preg_replace('/[^A-Z0-9]/', '', strtoupper($validated['lpn']));
        $pregeneratedLpn = PregeneratedLpn::where('lpn', $sanitizedLpn)->first();
        if (!$pregeneratedLpn) {
            return response()->json(['error' => 'El LPN no existe o no fue pre-generado.'], 404);
        }
        if ($pregeneratedLpn->is_used) {
            return response()->json(['error' => 'Este LPN ya ha sido utilizado y finalizado.'], 409);
        }

        try {
            $purchaseOrder = PurchaseOrder::findOrFail($validated['purchase_order_id']);
            
            $receivingLocation = Location::where('type', 'receiving')
                                        ->where('warehouse_id', $purchaseOrder->warehouse_id)
                                        ->first();

            if (!$receivingLocation) {
                throw new \Exception("No se encontró una ubicación de 'Recepción' configurada para el almacén de esta PO.");
            }
            
            $pallet = Pallet::firstOrCreate(
                [
                    'lpn' => $sanitizedLpn,
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

    public function addItemToPallet(Request $request, Pallet $pallet)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'quality_id' => 'required|exists:qualities,id',
        ]);

        DB::beginTransaction();
        try {
            $purchaseOrderLine = $pallet->purchaseOrder->lines()->where('product_id', $validated['product_id'])->first();
            if (!$purchaseOrderLine) {
                return response()->json(['error' => 'Este producto no pertenece a la orden de compra.'], 422);
            }

            $totalAlreadyReceived = DB::table('pallet_items')
                ->join('pallets', 'pallet_items.pallet_id', '=', 'pallets.id')
                ->where('pallets.purchase_order_id', $pallet->purchase_order_id)
                ->where('pallet_items.product_id', $validated['product_id'])
                ->sum('pallet_items.quantity');
            
            $quantityOrdered = $purchaseOrderLine->quantity_ordered;

            if (($totalAlreadyReceived + $validated['quantity']) > $quantityOrdered) {
                return response()->json([
                    'error' => 'La cantidad a recibir excede lo ordenado. Total ordenado: ' . $quantityOrdered
                ], 422);
            }
            
            $palletItem = $pallet->items()->firstOrCreate(
                [
                    'product_id' => $validated['product_id'],
                    'quality_id' => $validated['quality_id'],
                ],
                ['quantity' => 0]
            );
            
            $palletItem->increment('quantity', $validated['quantity']);

            $stock = InventoryStock::firstOrCreate(
                ['product_id' => $validated['product_id'], 'location_id' => $pallet->location_id, 'quality_id' => $validated['quality_id']],
                ['quantity' => 0]
            );
            $stock->increment('quantity', $validated['quantity']);

            StockMovement::create([
                'user_id' => Auth::id(),
                'product_id' => $validated['product_id'],
                'location_id' => $pallet->location_id,
                'pallet_item_id' => $palletItem->id,
                'quantity' => $validated['quantity'],
                'movement_type' => 'RECEPCION',
                'source_id' => $palletItem->id,
                'source_type' => \App\Models\WMS\PalletItem::class,
            ]);

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
            $pregeneratedLpn = PregeneratedLpn::where('lpn', $pallet->lpn)->firstOrFail();
            $pregeneratedLpn->update(['is_used' => true]);
            
            $pallet->update([
                'status' => 'Finished',
                'user_id' => Auth::id(),
                'last_action' => 'Recepción Finalizada'
            ]);

            $purchaseOrder = $pallet->purchaseOrder;

            $totalReceived = DB::table('pallet_items')
                ->join('pallets', 'pallet_items.pallet_id', '=', 'pallets.id')
                ->where('pallets.purchase_order_id', $purchaseOrder->id)
                ->sum('pallet_items.quantity');

            $purchaseOrder->update(['received_bottles' => $totalReceived]);

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

            $oldStock = InventoryStock::where('product_id', $palletItem->product_id)
                ->where('location_id', $pallet->location_id)
                ->where('quality_id', $palletItem->quality_id)->first();
            if ($oldStock) $oldStock->decrement('quantity', $palletItem->quantity);
            
            $palletItem->update($validated);

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

            $stock = InventoryStock::where('product_id', $palletItem->product_id)
                ->where('location_id', $pallet->location_id)
                ->where('quality_id', $palletItem->quality_id)->first();
            if ($stock) $stock->decrement('quantity', $palletItem->quantity);

            StockMovement::create([
                'user_id' => Auth::id(),
                'product_id' => $palletItem->product_id,
                'location_id' => $pallet->location_id,
                'pallet_item_id' => $palletItem->id,
                'quantity' => -$palletItem->quantity,
                'movement_type' => 'RECEPCION-REVERSA',
                'source_id' => $palletItem->id,
                'source_type' => \App\Models\WMS\PalletItem::class,
            ]);          
            
            $palletItem->delete();

            DB::commit();
            return response()->json($pallet->load('items.product', 'items.quality'));
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al eliminar el item: ' . $e->getMessage()], 500);
        }
    }

}