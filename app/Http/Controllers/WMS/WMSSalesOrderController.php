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
use App\Models\WMS\SalesOrderLine;
use App\Models\WMS\PalletItem;
use App\Models\WMS\Pallet;
use App\Models\WMS\PregeneratedLpn;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Warehouse;
use App\Models\Area;

class WMSSalesOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = Auth::user();

            if ($request->routeIs('wms.sales-orders.index') || $request->routeIs('wms.sales-orders.show') || $request->routeIs('wms.sales-orders.export') || $request->routeIs('wms.sales-orders.template') || $request->routeIs('wms.sales-orders.api.stock') || $request->routeIs('wms.sales-orders.api.qualities')) {
                if (!$user->hasFfPermission('wms.sales_orders.view')) {
                    abort(403, 'No tienes permiso para ver órdenes de venta.');
                }
            } elseif ($request->routeIs('wms.sales-orders.create') || $request->routeIs('wms.sales-orders.store')) {
                if (!$user->hasFfPermission('wms.sales_orders.create')) {
                    abort(403, 'No tienes permiso para crear órdenes de venta.');
                }
            } elseif ($request->routeIs('wms.sales-orders.edit') || $request->routeIs('wms.sales-orders.update') || $request->routeIs('wms.sales-orders.cancel')) {
                if (!$user->hasFfPermission('wms.sales_orders.edit')) {
                    abort(403, 'No tienes permiso para editar órdenes de venta.');
                }
            } elseif ($request->routeIs('wms.sales-orders.import')) {
                $salesOrder = $request->route('sales_order');
                if ($salesOrder && $salesOrder->exists) {
                    if (!$user->hasFfPermission('wms.sales_orders.edit')) {
                        abort(403, 'No tienes permiso para editar órdenes de venta (importación).');
                    }
                } else {
                    if (!$user->hasFfPermission('wms.sales_orders.create')) {
                        abort(403, 'No tienes permiso para crear órdenes de venta (importación).');
                    }
                }
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $warehouses = Warehouse::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();
        
        $warehouseId = $request->input('warehouse_id');
        $areaId = $request->input('area_id');

        $query = SalesOrder::with(['user', 'area'])
            ->withCount('lines') 
            ->withSum('lines', 'quantity_ordered');

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($areaId) {
            $query->where('area_id', $areaId);
        }
        
        if ($request->filled('so_number')) {
            $query->where('so_number', 'like', '%' . $request->so_number . '%');
        }
        if ($request->filled('end_date')) {
            $query->whereDate('order_date', '<=', $request->end_date);
        }        

        $salesOrders = $query->latest()->paginate(15)->withQueryString();

        $kpiQuery = SalesOrder::query();
        if ($warehouseId) {
            $kpiQuery->where('warehouse_id', $warehouseId);
        }
        if ($areaId) {
            $kpiQuery->where('area_id', $areaId);
        }

        if ($request->filled('so_number')) { $kpiQuery->where('so_number', 'like', '%' . $request->so_number . '%'); }
        if ($request->filled('invoice_number')) { $kpiQuery->where('invoice_number', 'like', '%' . $request->invoice_number . '%'); }
        if ($request->filled('customer_name')) { $kpiQuery->where('customer_name', 'like', '%' . $request->customer_name . '%'); }
        if ($request->filled('status')) { $kpiQuery->where('status', $request->status); }
        if ($request->filled('start_date')) { $kpiQuery->whereDate('order_date', '>=', $request->start_date); }
        if ($request->filled('end_date')) { $kpiQuery->whereDate('order_date', '<=', $request->end_date); }

        $kpis = [
            'total' => (clone $kpiQuery)->count(),
            'pending' => (clone $kpiQuery)->where('status', 'Pending')->count(),
            'picking' => (clone $kpiQuery)->where('status', 'Picking')->count(),
            'packed' => (clone $kpiQuery)->where('status', 'Packed')->count(),
        ];

        return view('wms.sales-orders.index', compact('salesOrders', 'kpis', 'warehouses', 'warehouseId', 'areas', 'areaId'));
    }

    public function create()
    {
        $products = Product::orderBy('sku')->get(['id', 'sku', 'name', 'upc']);
        $qualities = Quality::orderBy('name')->get(['id', 'name']);
        $warehouses = Warehouse::orderBy('name')->get(['id', 'name']);
        $areas = Area::orderBy('name')->get(['id', 'name']);
        
        return view('wms.sales-orders.create', compact('products', 'qualities', 'warehouses', 'areas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'so_number' => 'required|string|max:255|unique:sales_orders,so_number',
            'invoice_number' => 'nullable|string|max:255',
            'customer_name' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'area_id' => 'nullable|exists:areas,id',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quality_id' => 'required|exists:qualities,id',
            'lines.*.quantity' => 'required|integer|min:1',
            'lines.*.lpn' => 'nullable|string|exists:pallets,lpn',
        ]);

        DB::beginTransaction();
        try {
            $salesOrder = SalesOrder::create([
                'so_number' => $validated['so_number'],
                'invoice_number' => $validated['invoice_number'],
                'customer_name' => $validated['customer_name'],
                'order_date' => $validated['delivery_date'],
                'warehouse_id' => $validated['warehouse_id'],
                'area_id' => $validated['area_id'],
                'user_id' => Auth::id(),
                'status' => 'Pending',
            ]);

            foreach ($validated['lines'] as $line) {
                $palletItemId = null;
                if (!empty($line['lpn'])) {
                    $query = PalletItem::where('product_id', $line['product_id'])
                        ->where('quality_id', $line['quality_id'])
                        ->whereHas('pallet', fn($q) => $q->where('lpn', $line['lpn']));

                    if (!empty($validated['area_id'])) {
                        $query->whereHas('pallet.purchaseOrder', fn($q) => $q->where('area_id', $validated['area_id']));
                    }

                    $palletItem = $query->first();
                    
                    if (!$palletItem) {
                        throw new \Exception("El LPN {$line['lpn']} no contiene el SKU/Calidad especificado o no pertenece al área seleccionada.");
                    }
                    if (($palletItem->quantity - $palletItem->committed_quantity) < $line['quantity']) {
                         throw new \Exception("Stock insuficiente en LPN {$line['lpn']} para SKU {$palletItem->product->sku}.");
                    }
                    $palletItemId = $palletItem->id;
                }

                $salesOrder->lines()->create([
                    'product_id' => $line['product_id'],
                    'quality_id' => $line['quality_id'],
                    'quantity_ordered' => $line['quantity'],
                    'pallet_item_id' => $palletItemId,
                ]);
            }

            DB::commit();
            return redirect()->route('wms.sales-orders.index')->with('success', 'Orden de Venta creada. El stock será asignado al generar la Pick List.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al crear SO: " . $e->getMessage());
            return back()->with('error', 'Error al crear la orden: ' . $e->getMessage())->withInput();
        }
    }

    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load([
            'user', 
            'warehouse',
            'area',
            'lines.product',
            'lines.quality',
            'lines.palletItem.pallet.location',
            'lines.palletItem.pallet.purchaseOrder',
            'pickList.items.product',
            'pickList.items.quality',
            'pickList.items.pallet.location',
            'pickList.items.pallet.purchaseOrder'
        ]);

        return view('wms.sales-orders.show', compact('salesOrder'));
    }

    public function edit(SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== 'Pending') {
            return redirect()->route('wms.sales-orders.show', $salesOrder)->with('error', 'No se puede editar una orden que ya está en proceso de surtido.');
        }

        $salesOrder->load('lines.product', 'lines.quality', 'lines.palletItem.pallet');
        
        foreach ($salesOrder->lines as $line) {
            $query = PalletItem::where('product_id', $line->product_id)
                ->where('quality_id', $line->quality_id)
                ->whereRaw('quantity > committed_quantity')
                ->whereHas('pallet.location', function ($q) use ($salesOrder) {
                    $q->where('warehouse_id', $salesOrder->warehouse_id);
                });

            if ($salesOrder->area_id) {
                $query->whereHas('pallet.purchaseOrder', function ($q) use ($salesOrder) {
                    $q->where('area_id', $salesOrder->area_id);
                });
            }

            $line->calculated_available = $query->sum(DB::raw('quantity - committed_quantity'));
        }

        $products = Product::orderBy('sku')->get(['id', 'sku', 'name', 'upc']);
        $qualities = Quality::orderBy('name')->get(['id', 'name']);
        $warehouses = Warehouse::orderBy('name')->get(['id', 'name']);
        $areas = Area::orderBy('name')->get(['id', 'name']);
            
        return view('wms.sales-orders.edit', compact('salesOrder', 'products', 'qualities', 'warehouses', 'areas'));
    }

    public function update(Request $request, SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== 'Pending') {
            return back()->with('error', 'No se puede actualizar una orden que ya está en proceso.');
        }

        $validated = $request->validate([
            'so_number' => ['required', 'string', 'max:255', Rule::unique('sales_orders')->ignore($salesOrder->id)],
            'invoice_number' => 'nullable|string|max:255',
            'customer_name' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'area_id' => 'nullable|exists:areas,id',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quality_id' => 'required|exists:qualities,id',
            'lines.*.quantity' => 'required|integer|min:1',
            'lines.*.lpn' => 'nullable|string|exists:pallets,lpn',
        ]);

        DB::beginTransaction();
        try {
            $salesOrder->lines()->delete();

            $salesOrder->update([
                'so_number' => $validated['so_number'],
                'invoice_number' => $validated['invoice_number'],
                'customer_name' => $validated['customer_name'],
                'order_date' => $validated['delivery_date'],
                'warehouse_id' => $validated['warehouse_id'],
                'area_id' => $validated['area_id'],
            ]);

            foreach ($validated['lines'] as $line) {
                $palletItemId = null;
                if (!empty($line['lpn'])) {
                    $query = PalletItem::where('product_id', $line['product_id'])
                        ->where('quality_id', $line['quality_id'])
                        ->whereHas('pallet', fn($q) => $q->where('lpn', $line['lpn']));

                    if (!empty($validated['area_id'])) {
                        $query->whereHas('pallet.purchaseOrder', fn($q) => $q->where('area_id', $validated['area_id']));
                    }

                    $palletItem = $query->first();
                    
                    if (!$palletItem) {
                        throw new \Exception("El LPN {$line['lpn']} no contiene el SKU/Calidad especificado o no pertenece al área seleccionada.");
                    }
                    if (($palletItem->quantity - $palletItem->committed_quantity) < $line['quantity']) {
                         throw new \Exception("Stock insuficiente en LPN {$line['lpn']}.");
                    }
                    $palletItemId = $palletItem->id;
                }

                $salesOrder->lines()->create([
                    'product_id' => $line['product_id'],
                    'quality_id' => $line['quality_id'],
                    'quantity_ordered' => $line['quantity'],
                    'pallet_item_id' => $palletItemId,
                ]);
            }

            DB::commit();
            return redirect()->route('wms.sales-orders.show', $salesOrder)->with('success', 'Orden de Venta actualizada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al actualizar SO {$salesOrder->id}: " . $e->getMessage());
            return back()->with('error', 'Error al actualizar la orden: ' . $e->getMessage())->withInput();
        }
    }

    public function cancel(SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== 'Pending') {
            return back()->with('error', 'No se puede cancelar una orden que ya está en proceso de surtido.');
        }

        DB::beginTransaction();
        try {
            $salesOrder->status = 'Cancelled';
            $salesOrder->save();

            DB::commit();
            return redirect()->route('wms.sales-orders.index')->with('success', 'La Orden de Venta ha sido cancelada.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al cancelar SO {$salesOrder->id}: " . $e->getMessage());
            return back()->with('error', 'Error al cancelar la orden: ' . $e->getMessage());
        }
    }

    public function exportCsv(Request $request)
    {
        $fileName = 'reporte_ordenes_venta_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($request) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'ID Orden',
                'N° Orden (SO)',
                'N° Factura',
                'Cliente',
                'Área',
                'Almacén',
                'Fecha Orden/Entrega',
                'Estatus Orden',
                'Creado Por',
                'Fecha Creación Orden',
                'ID Línea',
                'SKU',
                'Producto',
                'Cantidad Ordenada',
                'LPN Origen',
                'Ubicación Origen (Código)',
                'Ubicación Origen (Física)',
                'Calidad',
                'PO Origen (LPN)',
                'Pedimento Origen (LPN)',
                'Picklist ID',
                'Estatus Picking',
                'Fecha Picking',
                'Picker',
            ]);

            $query = SalesOrder::query()
                ->with([
                    'user',
                    'warehouse',
                    'area',
                    'lines.product',
                    'lines.palletItem.pallet.location',
                    'lines.palletItem.pallet.purchaseOrder',
                    'lines.palletItem.quality',
                    'pickList.picker'
                ]);

            if ($request->filled('warehouse_id')) {
                $query->where('warehouse_id', $request->warehouse_id);
            }
            if ($request->filled('area_id')) {
                $query->where('area_id', $request->area_id);
            }
            if ($request->filled('so_number')) {
                $query->where('so_number', 'like', '%' . $request->so_number . '%');
            }
            if ($request->filled('invoice_number')) {
                $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
            }
            if ($request->filled('customer_name')) {
                $query->where('customer_name', 'like', '%' . $request->customer_name . '%');
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $query->orderBy('created_at', 'desc')->chunk(200, function ($salesOrders) use ($file) {
                foreach ($salesOrders as $so) {
                    $commonData = [
                        $so->id,
                        $so->so_number,
                        $so->invoice_number ?? '',
                        $so->customer_name,
                        $so->area?->name ?? 'N/A',
                        $so->warehouse?->name ?? 'N/A',
                        $so->order_date ? $so->order_date->format('Y-m-d') : '',
                        $so->status,
                        $so->user?->name ?? 'N/A',
                        $so->created_at ? $so->created_at->format('Y-m-d H:i') : '',
                    ];

                    if ($so->lines->isEmpty()) {
                        fputcsv($file, array_merge($commonData, array_fill(0, 14, '')));
                    } else {
                        foreach ($so->lines as $line) {
                            $palletItem = $line->palletItem;
                            $pallet = $palletItem?->pallet;
                            $location = $pallet?->location;
                            
                            $locationCode = $location?->code ?? '';
                            $locationPhysical = $location ? "{$location->aisle}-{$location->rack}-{$location->shelf}-{$location->bin}" : '';
                            
                            fputcsv($file, array_merge($commonData, [
                                $line->id,
                                $line->product?->sku ?? 'N/A',
                                $line->product?->name ?? 'N/A',
                                $line->quantity_ordered,
                                $pallet?->lpn ?? 'N/A',
                                $locationCode,
                                $locationPhysical,
                                $palletItem?->quality?->name ?? 'N/A',
                                $pallet?->purchaseOrder?->po_number ?? 'N/A',
                                $pallet?->purchaseOrder?->pedimento_a4 ?? 'N/A',
                                $so->pickList?->id ?? '',
                                $so->pickList?->status ?? '',
                                $so->pickList?->picked_at ? $so->pickList->picked_at->format('Y-m-d H:i') : '',
                                $so->pickList?->picker?->name ?? '',
                            ]));
                        }
                    }
                }
            });

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=plantilla_orden_venta.csv',
        ];

        $columns = [
            'sku (requerido)', 
            'cantidad (requerido)', 
            'calidad (requerido, ej: Disponible)', 
            'lpn (opcional, para surtido manual)'
        ];

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function importCsv(Request $request, SalesOrder $salesOrder)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt',
        ]);

        if ($validator->fails()) {
            return back()->with('error', 'Se requiere un archivo CSV.');
        }

        $contextAreaId = $request->input('area_id') ?? ($salesOrder->exists ? $salesOrder->area_id : null);

        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());
        $utf8Content = mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
        $lines = preg_split('/(\r\n|\n|\r)/', $utf8Content, -1, PREG_SPLIT_NO_EMPTY);
        
        if (count($lines) < 2) {
            return back()->with('error', 'El archivo está vacío o solo contiene encabezados.');
        }

        $headerLine = str_replace('ï»¿', '', array_shift($lines));
        $delimiter = str_contains($headerLine, ';') ? ';' : ','; 
        $csvHeaders = str_getcsv($headerLine, $delimiter);
        $headers = array_map(fn($h) => trim(explode('(', $h)[0]), $csvHeaders);

        $products = Product::whereIn('sku', array_column($lines, 0))->pluck('id', 'sku');
        $qualities = Quality::pluck('id', 'name');
        
        $errors = [];
        $linesToInsert = [];

        DB::beginTransaction();
        try {
            foreach ($lines as $index => $row) {
                $lineNumber = $index + 2;
                if (empty(trim($row))) continue;
                $data = str_getcsv($row, $delimiter);
                if (count($headers) !== count($data)) continue;
                $rowData = array_combine($headers, $data);

                $sku = trim($rowData['sku']);
                $qualityName = trim($rowData['calidad']);
                $lpn = trim($rowData['lpn']);
                $quantity = (int)trim($rowData['cantidad']);

                if (empty($sku) || $quantity <= 0 || empty($qualityName)) {
                    $errors[] = "Línea $lineNumber: Faltan datos requeridos (SKU, Cantidad o Calidad).";
                    continue;
                }
                
                $product = $products->get($sku);
                if (!$product) {
                    $errors[] = "Línea $lineNumber: SKU '$sku' no encontrado.";
                    continue;
                }

                $quality = $qualities->get($qualityName);
                if (!$quality) {
                    $errors[] = "Línea $lineNumber: Calidad '$qualityName' no encontrada.";
                    continue;
                }

                $palletItemId = null;
                if (!empty($lpn)) {
                    $query = PalletItem::where('product_id', $product)
                        ->where('quality_id', $quality)
                        ->whereHas('pallet', fn($q) => $q->where('lpn', $lpn));
                    
                    if ($contextAreaId) {
                        $query->whereHas('pallet.purchaseOrder', fn($q) => $q->where('area_id', $contextAreaId));
                    }

                    $palletItem = $query->first();
                    
                    if (!$palletItem) {
                        $errors[] = "Línea $lineNumber: El LPN '$lpn' no contiene el SKU/Calidad o no pertenece al área seleccionada.";
                        continue;
                    }
                    if (($palletItem->quantity - $palletItem->committed_quantity) < $quantity) {
                        $errors[] = "Línea $lineNumber: Stock insuficiente en LPN '$lpn'.";
                        continue;
                    }
                    $palletItemId = $palletItem->id;
                }

                $linesToInsert[] = [
                    'product_id' => $product,
                    'quality_id' => $quality,
                    'quantity_ordered' => $quantity,
                    'pallet_item_id' => $palletItemId,
                ];
            }

            if (!empty($errors)) {
                throw new \Exception(implode(' ', $errors));
            }

            if (empty($linesToInsert)) {
                throw new \Exception("No se encontraron líneas válidas para importar.");
            }

            if ($salesOrder->exists) {
                $salesOrder->lines()->delete();
                $salesOrder->lines()->createMany($linesToInsert);
                $route = 'wms.sales-orders.edit';
                $params = $salesOrder;
            } else {
                session()->put('imported_lines', $linesToInsert);
                $route = 'wms.sales-orders.create';
                $params = [];
            }
            
            DB::commit();
            return redirect()->route($route, $params)
                ->with('success', count($linesToInsert) . ' líneas cargadas desde plantilla. Por favor, completa los detalles de la orden.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al importar SO: " . $e->getMessage());
            return back()->with('error', 'Error al importar: ' . $e->getMessage())->withInput();
        }
    }

    public function apiSearchStockProducts(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|min:2',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quality_id' => 'required|exists:qualities,id',
            'area_id' => 'nullable|exists:areas,id',
        ]);

        $term = $validated['query'];
        $warehouseId = $validated['warehouse_id'];
        $qualityId = $validated['quality_id'];
        $areaId = $validated['area_id'] ?? null;

        $palletItemQuery = PalletItem::query()
            ->where('quality_id', $qualityId)
            ->whereRaw('quantity > committed_quantity')
            ->whereHas('pallet.location', function ($q_location) use ($warehouseId) {
                $q_location->where('warehouse_id', $warehouseId);
            });

        if ($areaId) {
            $palletItemQuery->whereHas('pallet.purchaseOrder', fn($q) => $q->where('area_id', $areaId));
        }

        $availableProductIds = $palletItemQuery
            ->select('product_id')
            ->distinct()
            ->pluck('product_id');

        $products = Product::whereIn('id', $availableProductIds)
            ->where(function($q) use ($term) {
                $q->where('sku', 'LIKE', $term . '%')
                  ->orWhere('name', 'LIKE', '%' . $term . '%')
                  ->orWhere('upc', 'LIKE', $term . '%');
            })
            ->select('id', 'sku', 'name', 'upc')
            ->limit(10)
            ->get();
            
        if ($products->isNotEmpty()) {
            $productIds = $products->pluck('id');
            
            $stockDataQuery = PalletItem::whereIn('product_id', $productIds)
                ->where('quality_id', $qualityId)
                ->whereRaw('quantity > committed_quantity')
                ->whereHas('pallet.location', fn($q) => $q->where('warehouse_id', $warehouseId));

            if ($areaId) {
                $stockDataQuery->whereHas('pallet.purchaseOrder', fn($q) => $q->where('area_id', $areaId));
            }

            $stockData = $stockDataQuery
                ->select('product_id', DB::raw('SUM(quantity - committed_quantity) as total_available'))
                ->groupBy('product_id')
                ->get()
                ->keyBy('product_id');

            $products->each(function($product) use ($stockData) {
                $product->total_available = $stockData->get($product->id)?->total_available ?? 0;
            });
        }
        return response()->json($products);
    }

    public function apiGetAvailableQualities(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'area_id' => 'nullable|exists:areas,id',
        ]);

        $warehouseId = $request->warehouse_id;
        $areaId = $request->area_id;

        $query = PalletItem::whereHas('pallet.location', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            })
            ->whereRaw('quantity > committed_quantity');

        if ($areaId) {
            $query->whereHas('pallet.purchaseOrder', function ($q) use ($areaId) {
                $q->where('area_id', $areaId);
            });
        }

        $qualityIds = $query->distinct()->pluck('quality_id');
        
        $qualities = Quality::whereIn('id', $qualityIds)->orderBy('name')->get(['id', 'name']);

        return response()->json($qualities);
    }
}