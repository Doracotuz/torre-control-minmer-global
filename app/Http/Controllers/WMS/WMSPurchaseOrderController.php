<?php
namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\WMS\PurchaseOrder;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Location;
use App\Models\WMS\DockArrival;

class WMSPurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        // Carga de relaciones para eficiencia
        $query = PurchaseOrder::with(['latestArrival', 'lines.product'])->latest();

        // --- Filtros Avanzados Corregidos ---
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                // Corregido: 'order_number' a 'po_number'
                $q->where('po_number', 'like', "%{$searchTerm}%")
                ->orWhere('container_number', 'like', "%{$searchTerm}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('sku')) {
            $skuTerm = $request->sku;
            $query->whereHas('lines.product', function($q) use ($skuTerm) {
                $q->where('sku', 'like', "%{$skuTerm}%");
            });
        }

        $purchaseOrders = $query->paginate(10)->withQueryString();

        // --- KPIs (sin cambios) ---
        $completedOrders = PurchaseOrder::where('status', 'Completed')->whereNotNull(['download_start_time', 'download_end_time']);
        $totalSeconds = $completedOrders->get()->sum(function($order) {
            return \Carbon\Carbon::parse($order->download_end_time)->diffInSeconds(\Carbon\Carbon::parse($order->download_start_time));
        });
        $avgTime = $completedOrders->count() > 0 ? ($totalSeconds / $completedOrders->count()) / 60 : 0;

        $kpis = [
            'receiving' => PurchaseOrder::where('status', 'Receiving')->count(),
            'arrivals_today' => DockArrival::whereDate('arrival_time', today())->count(),
            'pending' => PurchaseOrder::where('status', 'Pending')->count(),
            'avg_unload_time' => round($avgTime),
        ];

        return view('wms.purchase-orders.index', compact('purchaseOrders', 'kpis'));
    }


    public function create()
    {
        $products = Product::orderBy('name')->get();
        return view('wms.purchase-orders.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'po_number' => 'required|string|max:255|unique:purchase_orders,po_number',
            'expected_date' => 'required|date',
            'document_invoice' => 'nullable|string|max:255',
            'container_number' => 'nullable|string|max:255',
            'pedimento_a4' => 'nullable|string|max:255',
            'pedimento_g1' => 'nullable|string|max:255',
            'total_pallets' => 'nullable|integer|min:0', // Validar el nuevo campo
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity_ordered' => 'required|integer|min:1',
        ]);

        // Calculamos el total de botellas esperadas sumando las líneas
        $expected_bottles = collect($validatedData['lines'])->sum('quantity_ordered');

        // Creamos la orden de compra
        $purchaseOrder = PurchaseOrder::create([
            'po_number' => $validatedData['po_number'],
            'expected_date' => $validatedData['expected_date'],
            'document_invoice' => $validatedData['document_invoice'],
            'container_number' => $validatedData['container_number'],
            'pedimento_a4' => $validatedData['pedimento_a4'],
            'pedimento_g1' => $validatedData['pedimento_g1'],
            'total_pallets' => $validatedData['total_pallets'],
            'expected_bottles' => $expected_bottles, // Guardamos el total calculado
            'user_id' => auth()->id(),
            'status' => 'Pending',
        ]);

        // Guardamos las líneas de la orden
        foreach ($validatedData['lines'] as $line) {
            $purchaseOrder->lines()->create($line);
        }

        return redirect()->route('wms.purchase-orders.show', $purchaseOrder)
                        ->with('success', 'Orden de Compra creada exitosamente.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        // Carga todas las relaciones necesarias para la vista de detalle
        $purchaseOrder->load([
            'user', 
            'lines.product', 
            'pallets' => function ($query) {
                $query->where('status', 'Finished')->latest(); // Carga solo tarimas finalizadas, las más nuevas primero
            },
            'pallets.items.product', // Los productos dentro de cada item de la tarima
            'pallets.items.quality', // La calidad de cada item
            'pallets.user'           // El usuario que finalizó la tarima
        ]);
        
        // La función getReceiptSummary ya está en el modelo y no necesita más datos
        return view('wms.purchase-orders.show', compact('purchaseOrder'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validatedData = $request->validate([
            'operator_name' => 'nullable|string|max:255',
            'total_pallets' => 'nullable|integer|min:0',
            // No validamos 'expected_bottles' aquí porque no se puede cambiar
            'received_bottles' => 'nullable|integer|min:0',
            'download_start_time' => 'nullable|date',
            'download_end_time' => 'nullable|date|after_or_equal:download_start_time',
            // Opcional: Permitir cambiar el estado desde aquí también
            // 'status' => 'required|string|in:Pending,Receiving,Completed', 
        ]);

        $purchaseOrder->update($validatedData);

        return redirect()->route('wms.purchase-orders.show', $purchaseOrder)
                        ->with('success', 'Los detalles del arribo han sido actualizados.');
    }


    public function registerArrival(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate([
            'truck_plate' => 'required|string|max:20',
            'driver_name' => 'required|string|max:255',
        ]);

        // Opcional: Si usas una tabla DockArrival
        DockArrival::create([
            'purchase_order_id' => $purchaseOrder->id,
            'truck_plate' => strtoupper($request->truck_plate),
            'driver_name' => $request->driver_name,
            'arrival_time' => now(),
            'status' => 'Arrived',
        ]);
        
        // Actualizamos la orden de compra directamente
        $purchaseOrder->update([
            'status' => 'Receiving',
            'operator_name' => $request->driver_name, // Asigna el conductor como operador
            'download_start_time' => now(),
        ]);

        return back()->with('success', 'Llegada registrada. El estado de la orden es ahora "En Recepción".');
    }

    public function registerDeparture(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Opcional: Actualizar la tabla DockArrival si la usas
        $arrival = DockArrival::where('purchase_order_id', $purchaseOrder->id)->latest()->first();
        if ($arrival) {
            $arrival->update(['departure_time' => now(), 'status' => 'Departed']);
        }

        // Actualizamos la orden de compra
        $purchaseOrder->update(['download_end_time' => now()]);
        
        // Opcional: Podrías cambiar el estado a 'Completed' si la recepción ya terminó
        // $purchaseOrder->update(['status' => 'Completed']);

        return back()->with('success', 'Salida del vehículo registrada exitosamente.');
    }

    public function completeReceipt(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update(['status' => 'Completed']);

        return back()->with('success', 'La Orden de Compra ha sido marcada como "Completada".');
    }    

}