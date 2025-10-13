<?php
namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\WMS\PurchaseOrder;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Location;

class WMSPurchaseOrderController extends Controller
{
    public function index()
    {
        // Eliminamos ->with('supplier') de esta línea
        $purchaseOrders = PurchaseOrder::latest()->paginate(15);
        return view('wms.purchase-orders.index', compact('purchaseOrders'));
    }

    public function create()
    {
        // Ya no necesitamos la lista de proveedores
        $products = Product::orderBy('name')->get();
        return view('wms.purchase-orders.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'po_number' => 'required|string|max:255|unique:purchase_orders,po_number',
            'expected_date' => 'required|date',
            'notes' => 'nullable|string', // La validación es correcta
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity_ordered' => 'required|integer|min:1',
            'container_number' => 'nullable|string|max:255',
            'document_invoice' => 'nullable|string|max:255',
            'pedimento_a4' => 'nullable|string|max:255',
            'pedimento_g1' => 'nullable|string|max:255',            
        ]);

        DB::beginTransaction();
        try {
            // Pasamos directamente el array validado.
            // Laravel es lo suficientemente inteligente para mapear solo los campos que existen
            // en el array $fillable del modelo.
            $poData = $validated;
            $poData['user_id'] = Auth::id();

            // Quitamos 'lines' porque no es una columna de la tabla 'purchase_orders'
            unset($poData['lines']);

            $po = PurchaseOrder::create($poData);

            // Creamos las líneas por separado
            $po->lines()->createMany($validated['lines']);

            DB::commit();
            return redirect()->route('wms.purchase-orders.index')->with('success', 'Orden de Compra creada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear la Orden de Compra: ' . $e->getMessage())->withInput();
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        // Eliminamos 'supplier' del array de load()
        $purchaseOrder->load(['user', 'lines.product']);
        $locations = Location::orderBy('code')->get();
        return view('wms.purchase-orders.show', compact('purchaseOrder', 'locations'));
    }
}