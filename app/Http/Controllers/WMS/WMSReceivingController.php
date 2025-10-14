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

class WMSReceivingController extends Controller
{
    // Muestra la interfaz principal de recepción
    public function showReceivingForm(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['lines.product']);
        $products = Product::orderBy('name')->get(['id', 'name', 'sku']);
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
                'product_id' => $line->product_id,
                'sku' => $line->product->sku,
                'name' => $line->product->name,
                'ordered' => $line->quantity_ordered,
                'received' => $received,
                'balance' => $line->quantity_ordered - $received,
            ];
        });

        return view('wms.receiving.show', compact('purchaseOrder', 'products', 'qualities', 'receivingSummary'));
    }

    // Inicia una tarima usando un LPN pre-generado
    public function startPallet(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'lpn' => 'required|string',
        ]);

        $pregeneratedLpn = PregeneratedLpn::where('lpn', $validated['lpn'])->first();

        if (!$pregeneratedLpn) {
            return response()->json(['error' => 'El LPN no existe o no fue pre-generado.'], 404);
        }
        if ($pregeneratedLpn->is_used) {
            return response()->json(['error' => 'Este LPN ya ha sido utilizado en otra tarima.'], 409);
        }

        DB::beginTransaction();
        try {
            $pregeneratedLpn->update(['is_used' => true]);

            $receivingLocation = Location::where('type', 'receiving')->firstOrFail();
            
            $pallet = Pallet::create([
                'lpn' => $validated['lpn'],
                'purchase_order_id' => $validated['purchase_order_id'],
                'status' => 'Receiving',
                'location_id' => $receivingLocation->id,
            ]);

            DB::commit();
            return response()->json($pallet->load('items.product', 'items.quality'));

        } catch (\Exception $e) {
            DB::rollBack();
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
            $palletItem = $pallet->items()
                ->where('product_id', $validated['product_id'])
                ->where('quality_id', $validated['quality_id'])
                ->first();

            if ($palletItem) {
                $palletItem->increment('quantity', $validated['quantity']);
            } else {
                $pallet->items()->create($validated);
            }

            $stock = InventoryStock::firstOrCreate(
                [
                    'product_id' => $validated['product_id'], 
                    'location_id' => $pallet->location_id,
                    'quality_id' => $validated['quality_id']
                ],
                ['quantity' => 0]
            );
            $stock->increment('quantity', $validated['quantity']);

            DB::commit();
            return response()->json($pallet->load('items.product', 'items.quality'));

        } catch (\Exception $e) {
            DB::rollBack();
            // Esta línea detendrá el código y mostrará el error completo en lugar del mensaje genérico.
            throw $e;
        }
    }
}