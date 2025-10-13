<?php
namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\WMS\SalesOrder;
use App\Models\WMS\PickList;
use App\Models\WMS\InventoryStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WMSPickingController extends Controller
{
    /**
     * Genera una Pick List a partir de una Orden de Venta.
     * Esta es la lógica de "asignación de inventario".
     */
    public function generate(SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== 'Pending') {
            return back()->with('error', 'Esta orden ya está siendo procesada.');
        }

        DB::beginTransaction();
        try {
            $pickListItems = [];
            // Por cada línea de la orden, buscamos stock disponible
            foreach ($salesOrder->lines as $line) {
                $availableStock = InventoryStock::where('product_id', $line->product_id)
                    ->where('quantity', '>=', $line->quantity_ordered)
                    ->first();

                if (!$availableStock) {
                    throw new \Exception('No hay stock suficiente para el producto: ' . $line->product->sku);
                }

                $pickListItems[] = [
                    'product_id' => $line->product_id,
                    'location_id' => $availableStock->location_id, // Sugerimos esta ubicación
                    'quantity_to_pick' => $line->quantity_ordered,
                ];
            }

            // Creamos la Pick List
            $pickList = PickList::create([
                'sales_order_id' => $salesOrder->id,
                'user_id' => Auth::id(),
            ]);
            $pickList->items()->createMany($pickListItems);

            // Actualizamos el estado de la Orden de Venta
            $salesOrder->update(['status' => 'Picking']);

            DB::commit();
            return redirect()->route('wms.picking.show', $pickList)->with('success', 'Pick List generada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al generar Pick List: ' . $e->getMessage());
        }
    }

    /**
     * Muestra la interfaz de picking para el operario.
     */
    public function show(PickList $pickList)
    {
        $pickList->load(['salesOrder', 'items.product', 'items.location']);
        return view('wms.picking.show', compact('pickList'));
    }

    /**
     * Confirma el picking y descuenta el inventario.
     */
    public function confirm(Request $request, PickList $pickList)
    {
        DB::beginTransaction();
        try {
            foreach ($pickList->items as $item) {
                // Decrementamos el inventario de la ubicación de picking
                $stock = InventoryStock::where('product_id', $item->product_id)
                                     ->where('location_id', $item->location_id)
                                     ->firstOrFail();
                $stock->decrement('quantity', $item->quantity_to_pick);

                // Marcamos el item como "pickeado"
                $item->update(['quantity_picked' => $item->quantity_to_pick, 'is_picked' => true]);
            }

            // Actualizamos estados
            $pickList->update(['status' => 'Completed', 'picker_id' => Auth::id()]);
            $pickList->salesOrder->update(['status' => 'Packed']);

            DB::commit();
            return redirect()->route('wms.sales-orders.show', $pickList->sales_order_id)
                             ->with('success', 'Picking confirmado. El inventario ha sido actualizado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al confirmar el picking: ' . $e->getMessage());
        }
    }
}