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
use App\Models\WMS\ReceiptEvidence;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Warehouse;

class WMSPurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $warehouses = \App\Models\Warehouse::orderBy('name')->get();
        $warehouseId = $request->input('warehouse_id');

        $query = PurchaseOrder::with(['latestArrival', 'lines.product'])
            ->withCount(['pallets' => function($query) {
                $query->where('status', 'Finished');
            }])
            ->latest();        

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
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

        $kpiQuery = PurchaseOrder::query();
        if ($warehouseId) {
            $kpiQuery->where('warehouse_id', $warehouseId);
        }

        $completedOrders = (clone $kpiQuery)->where('status', 'Completed')->whereNotNull(['download_start_time', 'download_end_time']);
        $totalSeconds = $completedOrders->get()->sum(function($order) {
            return \Carbon\Carbon::parse($order->download_end_time)->diffInSeconds(\Carbon\Carbon::parse($order->download_start_time));
        });
        $avgTime = $completedOrders->count() > 0 ? ($totalSeconds / $completedOrders->count()) / 60 : 0;

        $kpis = [
            'receiving' => (clone $kpiQuery)->where('status', 'Receiving')->count(),
            'arrivals_today' => DockArrival::whereDate('arrival_time', today())
                                ->whereHas('purchaseOrder', function($q) use ($warehouseId) {
                                    if ($warehouseId) {
                                        $q->where('warehouse_id', $warehouseId);
                                    }
                                })->count(),
            'pending' => (clone $kpiQuery)->where('status', 'Pending')->count(),
            'avg_unload_time' => round($avgTime),
        ];

        return view('wms.purchase-orders.index', compact('purchaseOrders', 'kpis', 'warehouses', 'warehouseId'));
    }


    public function create()
    {
        $products = Product::orderBy('sku')->get(['id', 'sku', 'name', 'upc']);
        $warehouses = Warehouse::orderBy('name')->get(['id', 'name']);
        
        return view('wms.purchase-orders.create', compact('products', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'po_number' => 'required|string|max:255|unique:purchase_orders,po_number',
            'expected_date' => 'required|date',
            'document_invoice' => 'nullable|string|max:255',
            'container_number' => 'nullable|string|max:255',
            'pedimento_a4' => 'nullable|string|max:255',
            'pedimento_g1' => 'nullable|string|max:255',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity_ordered' => 'required|integer|min:1',
        ]);

        $expected_bottles = collect($validatedData['lines'])->sum('quantity_ordered');

        $purchaseOrder = PurchaseOrder::create([
            'warehouse_id' => $validatedData['warehouse_id'],
            'po_number' => $validatedData['po_number'],
            'expected_date' => $validatedData['expected_date'],
            'document_invoice' => $validatedData['document_invoice'],
            'container_number' => $validatedData['container_number'],
            'pedimento_a4' => $validatedData['pedimento_a4'],
            'pedimento_g1' => $validatedData['pedimento_g1'],
            'expected_bottles' => $expected_bottles,
            'user_id' => auth()->id(),
            'status' => 'Pending',
        ]);

        foreach ($validatedData['lines'] as $line) {
            $purchaseOrder->lines()->create($line);
        }

        return redirect()->route('wms.purchase-orders.show', $purchaseOrder)
                        ->with('success', 'Orden de Compra creada exitosamente.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'user', 
            'lines.product', 
            'pallets' => function ($query) {
                $query->where('status', 'Finished')->latest();
            },
            'pallets.items.product',
            'pallets.items.quality',
            'pallets.user',
            'evidences'
        ]);
        
        return view('wms.purchase-orders.show', compact('purchaseOrder'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($request->has('lines')) {
            
            $validatedData = $request->validate([
                'warehouse_id' => 'required|exists:warehouses,id',
                'po_number' => 'required|string|max:255|unique:purchase_orders,po_number,' . $purchaseOrder->id,
                'expected_date' => 'required|date',
                'container_number' => 'nullable|string|max:255',
                'document_invoice' => 'nullable|string|max:255',
                'pedimento_a4' => 'nullable|string|max:255',
                'pedimento_g1' => 'nullable|string|max:255',
                'lines' => 'required|array|min:1',
                'lines.*.product_id' => 'required|exists:products,id',
                'lines.*.quantity_ordered' => 'required|integer|min:1',
            ]);

            $expected_bottles = collect($validatedData['lines'])->sum('quantity_ordered');
            
            $poData = $validatedData;
            $poData['warehouse_id'] = $validatedData['warehouse_id'];
            $poData['expected_bottles'] = $expected_bottles;

            $purchaseOrder->update($poData);

            $purchaseOrder->lines()->delete();
            foreach ($validatedData['lines'] as $line) {
                $purchaseOrder->lines()->create($line);
            }

            return redirect()->route('wms.purchase-orders.show', $purchaseOrder)
                            ->with('success', 'Orden de Compra actualizada exitosamente.');

        } else {
            
            $validatedData = $request->validate([
                'operator_name' => 'nullable|string|max:255',
                'received_bottles' => 'nullable|integer|min:0',
                'download_start_time' => 'nullable|date',
                'download_end_time' => 'nullable|date|after_or_equal:download_start_time',
            ]);

            $purchaseOrder->update($validatedData);

            return redirect()->route('wms.purchase-orders.show', $purchaseOrder)
                            ->with('success', 'Los detalles del arribo han sido actualizados.');
        }
    }


    public function registerArrival(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate([
            'truck_plate' => 'required|string|max:20',
            'driver_name' => 'required|string|max:255',
        ]);

        DockArrival::create([
            'purchase_order_id' => $purchaseOrder->id,
            'truck_plate' => strtoupper($request->truck_plate),
            'driver_name' => $request->driver_name,
            'arrival_time' => now(),
            'status' => 'Arrived',
        ]);
        
        $purchaseOrder->update([
            'status' => 'Receiving',
            'operator_name' => $request->driver_name,
            'download_start_time' => now(),
        ]);

        return back()->with('success', 'Llegada registrada. El estado de la orden es ahora "En Recepción".');
    }

    public function registerDeparture(Request $request, PurchaseOrder $purchaseOrder)
    {
        $arrival = DockArrival::where('purchase_order_id', $purchaseOrder->id)->latest()->first();
        if ($arrival) {
            $arrival->update(['departure_time' => now(), 'status' => 'Departed']);
        }

        $purchaseOrder->update(['download_end_time' => now()]);
        
        $purchaseOrder->update(['status' => 'Completed']);

        return back()->with('success', 'Salida del vehículo registrada exitosamente.');
    }

    public function completeReceipt(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update(['status' => 'Completed']);

        return back()->with('success', 'La Orden de Compra ha sido marcada como "Completada".');
    }
    
    public function exportCsv(Request $request)
    {
        $query = \App\Models\WMS\Pallet::where('status', 'Finished')
            ->with([
                'purchaseOrder.latestArrival',
                'user',
                'items.product',
                'items.quality'
            ]);

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->whereHas('purchaseOrder', function($q) use ($searchTerm) {
                $q->where('po_number', 'like', "%{$searchTerm}%")
                ->orWhere('container_number', 'like', "%{$searchTerm}%");
            });
        }
        if ($request->filled('status')) {
            $query->whereHas('purchaseOrder', fn($q) => $q->where('status', 'like', "%{$request->status}%"));
        }
        if ($request->filled('sku')) {
            $skuTerm = $request->sku;
            $query->whereHas('items.product', fn($q) => $q->where('sku', 'like', "%{$skuTerm}%"));
        }

        $pallets = $query->latest()->get();
        
        $fileName = 'reporte_exhaustivo_recepcion_' . date('Y-m-d') . '.csv';
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($pallets) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'ID_Tarima',
                'LPN',
                'Fecha_Finalizacion_Tarima',
                'Usuario_Receptor',
                'N_Orden_Compra',
                'Estado_Orden',
                'Fecha_Esperada_Orden',
                'Contenedor',
                'Factura',
                'Pedimento_A4',
                'Pedimento_G1',
                'Fecha_Arribo_Vehiculo',
                'Fecha_Salida_Vehiculo',
                'Operador_Vehiculo',
                'Placas_Vehiculo',
                'SKU',
                'Producto',
                'Calidad',
                'Piezas_Por_Caja_Producto',
                'Cantidad_Recibida_Piezas',
                'Cantidad_Recibida_Cajas',
            ]);

            foreach ($pallets as $pallet) {
                foreach ($pallet->items as $item) {
                    $piecesPerCase = $item->product->pieces_per_case > 0 ? $item->product->pieces_per_case : 1;
                    $casesReceived = ceil($item->quantity / $piecesPerCase);

                    fputcsv($file, [
                        $pallet->id,
                        $pallet->lpn,
                        $pallet->updated_at->format('Y-m-d H:i:s'),
                        $pallet->user->name ?? 'N/A',
                        $pallet->purchaseOrder->po_number ?? '',
                        $pallet->purchaseOrder->status_in_spanish ?? '',
                        $pallet->purchaseOrder->expected_date ?? '',
                        $pallet->purchaseOrder->container_number ?? '',
                        $pallet->purchaseOrder->document_invoice ?? '',
                        $pallet->purchaseOrder->pedimento_a4 ?? '',
                        $pallet->purchaseOrder->pedimento_g1 ?? '',
                        $pallet->purchaseOrder->download_start_time ? \Carbon\Carbon::parse($pallet->purchaseOrder->download_start_time)->format('Y-m-d H:i:s') : '',
                        $pallet->purchaseOrder->download_end_time ? \Carbon\Carbon::parse($pallet->purchaseOrder->download_end_time)->format('Y-m-d H:i:s') : '',
                        $pallet->purchaseOrder->operator_name ?? '',
                        $pallet->purchaseOrder->latestArrival->truck_plate ?? '',
                        $item->product->sku ?? 'N/A',
                        $item->product->name ?? 'N/A',
                        $item->quality->name ?? 'N/A',
                        $item->product->pieces_per_case ?? 1,
                        $item->quantity,
                        $casesReceived,
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache", "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ]);
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('lines.product');
        
        $products = \App\Models\Product::orderBy('name')->get(); 
        
        $warehouses = \App\Models\Warehouse::orderBy('name')->get();

        return view('wms.purchase-orders.edit', compact('purchaseOrder', 'products', 'warehouses'));
    }

    public function uploadEvidence(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate([
            'marchamo' => 'nullable|image|max:5120',
            'puerta_cerrada' => 'nullable|image|max:5120',
            'apertura_puertas' => 'nullable|image|max:5120',
            'proceso_descarga' => 'nullable|array',
            'proceso_descarga.*' => 'image|max:5120',
            'caja_vacia' => 'nullable|image|max:5120',
            'producto_danado' => 'nullable|array',
            'producto_danado.*' => 'image|max:5120',
        ]);

        $types = $request->except('_token');

        foreach ($types as $type => $files) {
            if (!$request->hasFile($type)) continue;

            $files = is_array($files) ? $files : [$files];

            foreach ($files as $file) {
                $path = $file->store("public/receipt_evidence/{$purchaseOrder->po_number}");
                ReceiptEvidence::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'user_id' => Auth::id(),
                    'type' => $type,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }
        }

        return back()->with('success', 'Evidencias fotográficas subidas exitosamente.');
    }

    public function destroyEvidence(ReceiptEvidence $evidence)
    {
        Storage::delete($evidence->file_path);
        $evidence->delete();

        return back()->with('success', 'Fotografía eliminada exitosamente.');
    }

    public function generateArrivalReportPdf(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'user', 'lines.product', 'latestArrival',
            'pallets' => fn($q) => $q->where('status', 'Finished')->with(['user', 'items.product', 'items.quality']),
            'evidences'
        ]);

        $logoBase64 = null;
        $logoPath = 'LogoAzul.png';
        if (Storage::disk('s3')->exists($logoPath)) {
            try {
                $fileContent = Storage::disk('s3')->get($logoPath);
                $logoBase64 = 'data:image/png;base64,' . base64_encode($fileContent);
            } catch (\Exception $e) {
                $logoBase64 = null;
            }
        }

        $data = [
            'purchaseOrder' => $purchaseOrder,
            'summary' => $purchaseOrder->getReceiptSummary(),
            'logoBase64' => $logoBase64,
        ];
        
        $pdf = PDF::loadView('wms.purchase-orders.arrival_report_pdf', $data);
        
        return $pdf->stream('reporte_arribo_' . $purchaseOrder->po_number . '.pdf');
    }   
}