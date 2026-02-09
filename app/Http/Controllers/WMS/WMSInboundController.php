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
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->hasFfPermission('wms.receiving')) {
                abort(403, 'No tienes permiso para procesar recibos (Inbound).');
            }
            return $next($request);
        });
    }

    public function storeReceipt(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'document_invoice' => 'nullable|string|max:255',
            'container_number' => 'nullable|string|max:255',
            'pedimento_a4' => 'nullable|string|max:255',
            'pedimento_g1' => 'nullable|string|max:255',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity_received' => 'required|integer|min:0',
            'lines.*.location_id' => 'required_with:lines.*.quantity_received|nullable|exists:locations,id',
        ]);

        DB::beginTransaction();
        try {
            $receipt = $purchaseOrder->inboundReceipts()->create([
                'user_id' => Auth::id(),
                'document_invoice' => $validated['document_invoice'] ?? null,
                'container_number' => $validated['container_number'] ?? null,
                'pedimento_a4' => $validated['pedimento_a4'] ?? null,
                'pedimento_g1' => $validated['pedimento_g1'] ?? null,
            ]);

            foreach ($validated['lines'] as $lineData) {
                if ($lineData['quantity_received'] > 0) {
                    $receipt->lines()->create($lineData);

                    $stock = InventoryStock::firstOrCreate(
                        ['product_id' => $lineData['product_id'], 'location_id' => $lineData['location_id']],
                        ['quantity' => 0]
                    );
                    $stock->increment('quantity', $lineData['quantity_received']);
                }
            }

            $purchaseOrder->status = 'Receiving';
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