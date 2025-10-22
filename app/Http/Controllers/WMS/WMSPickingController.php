<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\WMS\SalesOrder;
use App\Models\WMS\PickList;
use App\Models\WMS\InventoryStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Models\Location; // Importamos el modelo Location

class WMSPickingController extends Controller
{
    /**
     * Genera una Pick List a partir de una Orden de Venta.
     */
    public function generate(SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== 'Pending') {
            return back()->with('error', 'Esta orden ya está siendo procesada o ya tiene una Pick List.');
        }

        DB::beginTransaction();
        try {
            $pickListItems = [];
            foreach ($salesOrder->lines as $line) {
                // Buscamos un PalletItem específico que cumpla las condiciones
                $palletItem = \App\Models\WMS\PalletItem::where('product_id', $line->product_id)
                    ->where('quantity', '>=', $line->quantity_ordered)
                    ->orderBy('created_at') // FIFO básico
                    ->first();

                if (!$palletItem) {
                    throw new \Exception('No hay un pallet con stock suficiente para el producto: ' . $line->product->sku);
                }

                $pickListItems[] = [
                    'product_id' => $line->product_id,
                    'pallet_id' => $palletItem->pallet_id,
                    'location_id' => $palletItem->pallet->location_id,
                    'quantity_to_pick' => $line->quantity_ordered,
                    'quality_id' => $palletItem->quality_id,
                ];
            }

            $pickList = \App\Models\WMS\PickList::create([
                'sales_order_id' => $salesOrder->id,
                'user_id' => Auth::id(),
                'status' => 'Generated',
            ]);

            $pickList->items()->createMany($pickListItems);
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
        if ($pickList->status === 'Completed') {
            return back()->with('error', 'Este picking ya fue confirmado anteriormente.');
        }

        DB::beginTransaction();
        try {
            foreach ($pickList->items as $item) {
                // 1. Descontar del Inventario General (el resumen)
                $stock = \App\Models\WMS\InventoryStock::where('product_id', $item->product_id)
                                    ->where('location_id', $item->location_id)
                                    ->firstOrFail();
                
                // Aquí está la validación que agregamos antes...
                if ($stock->quantity < $item->quantity_to_pick) {
                    throw new \Exception("Stock insuficiente...");
                }

                // --- INICIO DE LA CORRECCIÓN ---

                // Descontar del inventario físico total (esta línea ya la tienes)
                $stock->decrement('quantity', $item->quantity_to_pick);

                // Descontar del inventario comprometido (ESTA ES LA LÍNEA QUE FALTA)
                $stock->decrement('committed_quantity', $item->quantity_to_pick);
                
                // --- FIN DE LA CORRECCIÓN ---


                // 2. Descontar del Inventario Específico (el pallet real)
                $palletItem = \App\Models\WMS\PalletItem::where('pallet_id', $item->pallet_id)
                    ->where('product_id', $item->product_id)
                    ->where('quality_id', $item->quality_id) // <-- AÑADIR ESTA LÍNEA
                    ->first();

                if ($palletItem) {
                    $palletItem->decrement('quantity', $item->quantity_to_pick);
                }
            }

            $pickList->update([
                'status' => 'Completed',
                'picker_id' => Auth::id(),
                'picked_at' => now()
            ]);
            
            $pickList->salesOrder->update(['status' => 'Packed']);

            DB::commit();
            return redirect()->route('wms.sales-orders.show', $pickList->sales_order_id)
                            ->with('success', 'Picking confirmado. El inventario ha sido actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al confirmar el picking: ' . $e->getMessage());
        }
    }
    
    /**
     * Genera y muestra el PDF de la Pick List.
     */
    public function generatePickListPdf(PickList $pickList)
    {
        $pickList->load('salesOrder', 'items.product', 'items.location');
        
        $logoBase64 = null;
        if (Storage::disk('s3')->exists('LogoAzul.png')) {
            $logoContent = Storage::disk('s3')->get('LogoAzul.png');
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoContent);
        }

        $data = ['pickList' => $pickList, 'logoBase64' => $logoBase64];
        $pdf = PDF::loadView('wms.picking.pdf', $data);
        $fileName = 'PickList-' . $pickList->salesOrder->so_number . '.pdf';

        return $pdf->stream($fileName);
    }
}