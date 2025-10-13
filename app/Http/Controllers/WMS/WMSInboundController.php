<?php
namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\WMS\PurchaseOrder;
use App\Models\WMS\InventoryStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WMSInboundController extends Controller
{
    public function storeReceipt(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            // --- NUEVAS REGLAS DE VALIDACIÓN ---
            'document_invoice' => 'nullable|string|max:255',
            'container_number' => 'nullable|string|max:255',
            'pedimento_a4' => 'nullable|string|max:255',
            'pedimento_g1' => 'nullable|string|max:255',
            // --- FIN NUEVAS REGLAS ---
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity_received' => 'required|integer|min:0',
            'lines.*.location_id' => 'required_with:lines.*.quantity_received|nullable|exists:locations,id',
        ]);

        DB::beginTransaction();
        try {
            // 1. Crear el registro de la recepción con los nuevos campos
            $receipt = $purchaseOrder->inboundReceipts()->create([
                'user_id' => Auth::id(),
                'document_invoice' => $validated['document_invoice'] ?? null,
                'container_number' => $validated['container_number'] ?? null,
                'pedimento_a4' => $validated['pedimento_a4'] ?? null,
                'pedimento_g1' => $validated['pedimento_g1'] ?? null,
            ]);

            foreach ($validated['lines'] as $lineData) {
                if ($lineData['quantity_received'] > 0) {
                    // 2. Crear las líneas de la recepción
                    $receipt->lines()->create($lineData);

                    // 3. Actualizar el inventario (el paso más importante)
                    $stock = InventoryStock::firstOrCreate(
                        ['product_id' => $lineData['product_id'], 'location_id' => $lineData['location_id']],
                        ['quantity' => 0]
                    );
                    $stock->increment('quantity', $lineData['quantity_received']);
                }
            }

            // 4. Actualizar el estado de la Orden de Compra (lógica simplificada)
            $purchaseOrder->status = 'Receiving'; // O 'Closed' si está completa
            $purchaseOrder->save();

            DB::commit();
            return redirect()->route('wms.purchase-orders.show', $purchaseOrder)
                            ->with('success', 'Mercancía recibida y inventario actualizado.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar la recepción: ' . $e->getMessage())->withInput();
        }
    }
}