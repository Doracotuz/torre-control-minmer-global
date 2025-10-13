<?php
namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\WMS\PurchaseOrder;
use App\Models\WMS\Pallet;
use App\Models\WMS\InventoryStock;
use App\Models\Product;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WMSReceivingController extends Controller
{
    public function showReceivingForm(PurchaseOrder $purchaseOrder)
    {
        // La lógica del resumen no cambia
        $purchaseOrder->load(['lines.product']);
        $products = Product::orderBy('name')->get(['id', 'name', 'sku']);
        $alreadyReceived = DB::table('pallet_items')->join('pallets', 'pallet_items.pallet_id', '=', 'pallets.id')->where('pallets.purchase_order_id', $purchaseOrder->id)->select('pallet_items.product_id', DB::raw('SUM(pallet_items.quantity) as received_qty'))->groupBy('pallet_items.product_id')->pluck('received_qty', 'product_id');
        $receivingSummary = $purchaseOrder->lines->map(function ($line) use ($alreadyReceived) {
            $received = $alreadyReceived->get($line->product_id, 0);
            return ['product_id' => $line->product_id, 'sku' => $line->product->sku, 'name' => $line->product->name, 'ordered' => $line->quantity_ordered, 'received' => $received, 'balance' => $line->quantity_ordered - $received];
        });
        return view('wms.receiving.show', compact('purchaseOrder', 'products', 'receivingSummary'));
    }

    public function startPallet(Request $request)
    {
        $request->validate(['purchase_order_id' => 'required|exists:purchase_orders,id']);

        // Buscamos la ubicación de recepción por defecto
        $receivingLocation = Location::where('type', 'receiving')->first();
        if (!$receivingLocation) {
            return response()->json(['error' => 'No se encontró una ubicación de tipo "Receiving". Por favor, créala primero.'], 400);
        }

        $lpn = 'LPN-' . strtoupper(uniqid());

        $pallet = Pallet::create([
            'lpn' => $lpn,
            'purchase_order_id' => $request->purchase_order_id,
            'status' => 'Receiving',
            'location_id' => $receivingLocation->id, // Asignación automática
        ]);

        return response()->json($pallet->load('items.product'));
    }

    public function addItemToPallet(Request $request, Pallet $pallet)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Añadimos o actualizamos el item en la tarima
            $palletItem = $pallet->items()->where('product_id', $validated['product_id'])->first();
            if ($palletItem) {
                $palletItem->increment('quantity', $validated['quantity']);
            } else {
                $pallet->items()->create($validated);
            }

            // Actualizamos el inventario en la ubicación de recepción AL INSTANTE
            $stock = InventoryStock::firstOrCreate(
                ['product_id' => $validated['product_id'], 'location_id' => $pallet->location_id],
                ['quantity' => 0]
            );
            $stock->increment('quantity', $validated['quantity']);

            DB::commit();
            return response()->json($pallet->load('items.product'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al añadir item: ' . $e->getMessage()], 500);
        }
    }
}