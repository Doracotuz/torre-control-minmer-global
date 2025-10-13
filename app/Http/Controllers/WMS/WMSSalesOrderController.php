<?php
namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\WMS\SalesOrder;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WMSSalesOrderController extends Controller
{
    public function index()
    {
        $salesOrders = SalesOrder::latest()->paginate(15);
        return view('wms.sales-orders.index', compact('salesOrders'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();
        return view('wms.sales-orders.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'so_number' => 'required|string|max:255|unique:sales_orders,so_number',
            'invoice_number' => 'nullable|string|max:255|unique:sales_orders,invoice_number',
            'customer_name' => 'required|string|max:255',
            'order_date' => 'required|date',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity_ordered' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $so = SalesOrder::create([
                'so_number' => $validated['so_number'],
                'invoice_number' => $validated['invoice_number'],
                'customer_name' => $validated['customer_name'],
                'order_date' => $validated['order_date'],
                'user_id' => Auth::id(),
            ]);

            $so->lines()->createMany($validated['lines']);

            DB::commit();
            return redirect()->route('wms.sales-orders.index')->with('success', 'Orden de Venta creada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear la Orden de Venta: ' . $e->getMessage())->withInput();
        }
    }

    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load(['user', 'lines.product']);
        return view('wms.sales-orders.show', compact('salesOrder'));
    }
}