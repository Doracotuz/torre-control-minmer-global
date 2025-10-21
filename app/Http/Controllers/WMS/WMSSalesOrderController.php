<?php
namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\WMS\InventoryStock;
use App\Models\WMS\Quality;
use App\Models\Product;
use App\Models\WMS\SalesOrder;
use App\Models\WMS\PalletItem;

class WMSSalesOrderController extends Controller
{
    public function index(Request $request)
    {
        // Carga las relaciones 'user' y cuenta/suma las líneas
        $query = SalesOrder::with(['user'])
            ->withCount('lines') 
            ->withSum('lines', 'quantity_ordered');

        // Aplicar filtros si existen en la petición
        if ($request->filled('so_number')) {
            $query->where('so_number', 'like', '%' . $request->so_number . '%');
        }
        if ($request->filled('customer_name')) {
            $query->where('customer_name', 'like', '%' . $request->customer_name . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $salesOrders = $query->latest()->paginate(15);

        return view('wms.sales-orders.index', compact('salesOrders'));
    }

    public function create()
    {
        $stockData = PalletItem::where('quantity', '>', 0)
            ->with(['product', 'quality', 'pallet.purchaseOrder'])
            ->get();
            
        return view('wms.sales-orders.create', compact('stockData'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'so_number' => 'required|string|max:255|unique:sales_orders,so_number',
            'invoice_number' => 'nullable|string|max:255',
            'customer_name' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'lines' => 'required|array|min:1',
            'lines.*.pallet_item_id' => 'required|exists:pallet_items,id',
            'lines.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $salesOrder = SalesOrder::create([
                'so_number' => $validated['so_number'],
                'invoice_number' => $validated['invoice_number'],
                'customer_name' => $validated['customer_name'],
                'order_date' => $validated['delivery_date'],
                'user_id' => Auth::id(),
                'status' => 'Pending', // Aseguramos un estado inicial
            ]);

            foreach ($validated['lines'] as $line) {
                $palletItem = \App\Models\WMS\PalletItem::with('pallet')->findOrFail($line['pallet_item_id']);

                // --- LÓGICA DE STOCK COMPROMETIDO ---
                $stock = \App\Models\WMS\InventoryStock::where('product_id', $palletItem->product_id)
                    ->where('location_id', $palletItem->pallet->location_id)
                    ->where('quality_id', $palletItem->quality_id)
                    ->first();

                if (!$stock || ($stock->quantity - $stock->committed_quantity) < $line['quantity']) {
                    throw new \Exception("La cantidad para el SKU {$palletItem->product->sku} excede el stock disponible.");
                }
                
                // Incrementamos la cantidad comprometida
                $stock->increment('committed_quantity', $line['quantity']);

                // Crear la línea de la orden de venta
                $salesOrder->lines()->create([
                    'product_id' => $palletItem->product_id,
                    'quality_id' => $palletItem->quality_id,
                    'pallet_id' => $palletItem->pallet_id,
                    'quantity_ordered' => $line['quantity'],
                ]);
            }

            DB::commit();
            return redirect()->route('wms.sales-orders.index')->with('success', 'Orden de Venta creada e inventario comprometido exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear la orden: ' . $e->getMessage())->withInput();
        }
    }

    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load(['user', 'lines.product']);
        return view('wms.sales-orders.show', compact('salesOrder'));
    }
}