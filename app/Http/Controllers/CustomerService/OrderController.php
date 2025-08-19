<?php

namespace App\Http\Controllers\CustomerService;

use App\Http\Controllers\Controller;
use App\Models\CsCustomer;
use App\Models\CsOrder;
use App\Models\CsOrderEvent;
use App\Models\CsOrderDetail;
use App\Models\CsPlan;
use App\Models\CsProduct;
use App\Models\CsWarehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    /**
     * Muestra la vista principal de gestión de pedidos.
     */
    public function index(Request $request)
    {
        // Inicialmente carga la vista. Los datos se obtendrán vía AJAX.
        return view('customer-service.orders.index');
    }

    /**
     * Filtra los pedidos según los criterios de búsqueda y paginación (para AJAX).
     */
    public function filter(Request $request)
    {
        $query = CsOrder::with('plan')->orderBy('creation_date', 'desc');

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('so_number', 'like', $searchTerm)
                  ->orWhere('purchase_order', 'like', $searchTerm)
                  ->orWhere('customer_name', 'like', $searchTerm)
                  ->orWhere('invoice_number', 'like', $searchTerm);
            });
        }
        if ($request->filled('status')) { $query->where('status', $request->status); }
        if ($request->filled('channel')) { $query->where('channel', $request->channel); }
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('creation_date', [$request->date_from, $request->date_to]);
        }

        $orders = $query->paginate(15)->withQueryString();

        return response()->json($orders);
    }

    /**
     * Muestra la vista de detalle de un pedido.
     */
    public function show(CsOrder $order)
    {
        $order->load(['details', 'events.user', 'createdBy']);
        return view('customer-service.orders.show', compact('order'));
    }

    /**
     * Muestra el formulario para editar un pedido.
     */
    public function edit(CsOrder $order)
    {
        $order->load(['details.product']);
        $products = CsProduct::all()->sortBy('sku'); // Obtiene todos los productos
        return view('customer-service.orders.edit', compact('order', 'products'));
    }

    /**
     * Actualiza un pedido y registra los cambios en la línea de tiempo.
     */
    public function update(Request $request, CsOrder $order)
    {
        $validatedData = $request->validate([
            'bt_oc' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|max:255',
            'invoice_date' => 'nullable|date_format:Y-m-d',
            'delivery_date' => 'nullable|date_format:Y-m-d',
            'schedule' => 'nullable|string|max:255',
            'client_contact' => 'nullable|string|max:255',
            'shipping_address' => 'nullable|string',
            'destination_locality' => 'nullable|string|max:255',
            'executive' => 'nullable|string|max:255',
            'observations' => 'nullable|string',
            'evidence_reception_date' => 'nullable|date_format:Y-m-d',
            'evidence_cutoff_date' => 'nullable|date_format:Y-m-d',
            'details' => 'array',
            'details.*.id' => 'required|exists:cs_order_details,id',
            'details.*.sent' => 'nullable|integer|min:0',
        ]);
        
        $fieldLabels = [
            'bt_oc' => 'BT de OC',
            'invoice_number' => 'Número de Factura',
            'invoice_date' => 'Fecha de Factura',
            'delivery_date' => 'Fecha de Entrega',
            'schedule' => 'Horario',
            'client_contact' => 'Cliente',
            'shipping_address' => 'Dirección de Envío',
            'destination_locality' => 'Localidad Destino',
            'executive' => 'Ejecutivo',
            'observations' => 'Observaciones',
            'evidence_reception_date' => 'Fecha de Recepción de Evidencia',
            'evidence_cutoff_date' => 'Fecha de Corte de Evidencia',
        ];

        $changes = [];

        // Separar los detalles para procesarlos por separado
        $detailsData = $validatedData['details'] ?? [];
        unset($validatedData['details']);

        // Actualizar los datos del pedido principal
        $oldOrderData = $order->getOriginal();
        $order->update($validatedData);

        foreach ($validatedData as $key => $value) {
            $oldValue = $oldOrderData[$key];
            if (in_array($key, ['invoice_date', 'delivery_date', 'evidence_reception_date', 'evidence_cutoff_date']) && $oldValue) {
                $oldValue = \Carbon\Carbon::parse($oldValue)->format('Y-m-d');
            }
            if ($oldValue != $value) {
                $fieldName = $fieldLabels[$key] ?? ucwords(str_replace('_', ' ', $key));
                $changes[] = "cambió '{$fieldName}' de '".($oldValue ?? 'vacío')."' a '".$value."'";
            }
        }
        
        // Actualizar los detalles del pedido si se enviaron
        if (!empty($detailsData)) {
            foreach ($detailsData as $detailData) {
                $detail = $order->details()->find($detailData['id']);
                if ($detail) {
                    $oldSent = $detail->sent;
                    $newSent = $detailData['sent'] ?? 0;
                    if ($oldSent != $newSent) {
                        $changes[] = "actualizó la cantidad enviada para el SKU {$detail->sku} de '{$oldSent}' a '{$newSent}'";
                        $detail->update(['sent' => $newSent]);
                    }
                }
            }
        }

        if (!empty($changes)) {
            \App\Models\CsOrderEvent::create([
                'cs_order_id' => $order->id,
                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                'description' => 'El usuario ' . \Illuminate\Support\Facades\Auth::user()->name . ' ' . implode(', ', $changes) . '.'
            ]);
        }

        return redirect()->route('customer-service.orders.show', $order)->with('success', 'Pedido actualizado exitosamente.');
    }

    /**
     * Marca un pedido como "Cancelado".
     */
    public function cancel(CsOrder $order)
    {
        $order->update(['status' => 'Cancelado', 'updated_by_user_id' => Auth::id()]);
        
        CsOrderEvent::create([
            'cs_order_id' => $order->id,
            'user_id' => Auth::id(),
            'description' => 'El usuario ' . Auth::user()->name . ' canceló el pedido.'
        ]);

        return back()->with('success', 'El pedido ha sido cancelado.');
    }

    /**
     * Mueve un pedido a la tabla de planificación.
     */
    public function moveToPlan(CsOrder $order)
    {
        if ($order->plan) {
            return back()->with('error', 'Este pedido ya ha sido enviado a planificación.');
        }

        DB::transaction(function () use ($order) {
            $order->update(['status' => 'En Planificación', 'updated_by_user_id' => Auth::id()]);

            CsPlan::create([
                'cs_order_id' => $order->id,
                'planned_by_user_id' => Auth::id(),
                'status' => 'Pendiente',
            ]);

            CsOrderEvent::create([
                'cs_order_id' => $order->id,
                'user_id' => Auth::id(),
                'description' => 'El usuario ' . Auth::user()->name . ' marcó el pedido como "Listo" y lo envió a planificación.'
            ]);
        });

        return back()->with('success', 'Pedido enviado a planificación.');
    }

    /**
     * Procesa la importación del archivo CSV de órdenes de venta.
     */
    public function importCsv(Request $request)
    {
        $request->validate(['csv_file' => 'required|mimes:csv,txt']);
        $path = $request->file('csv_file')->getRealPath();
        
        $fileContent = file_get_contents($path);
        $utf8Content = mb_convert_encoding($fileContent, 'UTF-8', mb_detect_encoding($fileContent, 'UTF-8, ISO-8859-1', true));
        $file = fopen("php://memory", 'r+');
        fwrite($file, $utf8Content);
        rewind($file);

        $raw_header = fgetcsv($file);
        $header = array_map(function($h) {
            return trim(preg_replace('/^\x{FEFF}/u', '', $h));
        }, $raw_header);

        $products = CsProduct::pluck('packaging_factor', 'sku')->all();
        $productTypes = CsProduct::pluck('type', 'sku')->all();
        $warehouses = CsWarehouse::pluck('name', 'warehouse_id')->all();
        $customers = CsCustomer::select('channel', 'client_id')->distinct()->get()->keyBy('client_id');

        $ordersData = [];
        $errorDocs = [];
        $csvDocNumbers = [];
        $rowNumber = 1;

        while (($row = fgetcsv($file)) !== FALSE) {
            if (count($row) !== count($header)) continue;
            
            $row = array_map('trim', $row);
            $rowData = array_combine($header, $row);
            
            $docNum = $rowData['Document Number'];
            $csvDocNumbers[] = $docNum;
            
            $rowErrors = [];

            if (empty($rowData['Item No.'])) {
                $rowErrors[] = "El campo 'Item No.' es obligatorio.";
            }
            if (empty($rowData['Document Number'])) {
                $rowErrors[] = "El campo 'Document Number' es obligatorio.";
            }
            if (empty($rowData['Customer/Vendor Code'])) {
                $rowErrors[] = "El campo 'Customer/Vendor Code' es obligatorio.";
            }
            
            if (!isset($products[$rowData['Item No.']])) {
                $rowErrors[] = "SKU no encontrado: " . $rowData['Item No.'];
            }
            if (!isset($warehouses[$rowData['Warehouse Code']])) {
                $rowErrors[] = "Almacén no encontrado: " . $rowData['Warehouse Code'];
            }
            if (!isset($customers[$rowData['Customer/Vendor Code']])) {
                $rowErrors[] = "Cliente no encontrado: " . $rowData['Customer/Vendor Code'];
            }
            
            if (!empty($rowErrors)) {
                $errorDocs[$docNum] = true;
                $rowData['Motivo del Error'] = "Línea " . $rowNumber . ": " . implode(' ', $rowErrors);
            } else {
                $rowData['Motivo del Error'] = '';
            }

            if (!isset($ordersData[$docNum])) {
                $ordersData[$docNum] = [
                    'header_data' => $rowData,
                    'details' => [],
                    'has_promo_item' => false,
                    'calculations' => ['total_bottles' => 0, 'total_boxes' => 0, 'subtotal' => 0],
                    'raw_rows' => []
                ];
            }
            
            $ordersData[$docNum]['raw_rows'][] = $rowData;

            $sku = $rowData['Item No.'];
            $quantity = (int)$rowData['Quantity'];
            $packagingFactor = $products[$sku] ?? 1;

            $rowTotal = str_replace(',', '', $rowData['Row Total']);

            $ordersData[$docNum]['details'][] = ['sku' => $sku, 'quantity' => $quantity];
            $ordersData[$docNum]['calculations']['total_bottles'] += $quantity;
            $ordersData[$docNum]['calculations']['total_boxes'] += ($packagingFactor > 0) ? ($quantity / $packagingFactor) : 0;
            $ordersData[$docNum]['calculations']['subtotal'] += (float)$rowTotal;
            
            if (isset($productTypes[$sku]) && $productTypes[$sku] === 'Promocional') {
                $ordersData[$docNum]['has_promo_item'] = true;
            }
            $rowNumber++;
        }
        fclose($file);

        $existingOrders = CsOrder::whereIn('so_number', array_unique($csvDocNumbers))->pluck('so_number')->toArray();
        $newOrdersData = [];
        $errorRows = [];
        $processedDocNumbers = [];

        foreach($ordersData as $docNum => $data) {
            if(in_array($docNum, $existingOrders)) {
                $errorDocs[$docNum] = true;
                foreach($data['raw_rows'] as $idx => $row) {
                    $data['raw_rows'][$idx]['Motivo del Error'] = "El SO {$docNum} ya existe en la base de datos.";
                }
            }
        }
        
        foreach($ordersData as $docNum => $data) {
            if(isset($errorDocs[$docNum])) {
                $errorRows = array_merge($errorRows, $data['raw_rows']);
            } else {
                $newOrdersData[$docNum] = $data;
            }
        }

        if (!empty($newOrdersData)) {
            DB::transaction(function () use ($newOrdersData, $warehouses, $customers, &$processedDocNumbers) {
                foreach ($newOrdersData as $docNum => $data) {
                    $customerCode = $data['header_data']['Customer/Vendor Code'];
                    $channel = $data['has_promo_item'] ? 'POSM' : ($customers[$customerCode]->channel ?? 'N/A');

                    $postingDate = $data['header_data']['Posting Date'];
                    if (is_numeric($postingDate)) {
                        $creationDate = \Carbon\Carbon::createFromFormat('Y-m-d', '1900-01-01')->addDays($postingDate - 2)->format('Y-m-d');
                    } else {
                        $creationDate = \Carbon\Carbon::createFromFormat('d/m/Y', $postingDate)->format('Y-m-d');
                    }

                    $order = CsOrder::create(
                        [
                            'so_number' => $docNum,
                            'purchase_order' => $data['header_data']['BP Reference No.'],
                            'creation_date' => $creationDate,
                            'authorization_date' => now()->format('Y-m-d'),
                            'customer_name' => $data['header_data']['Customer/Vendor Name'],
                            'origin_warehouse' => $warehouses[$data['header_data']['Warehouse Code']],
                            'total_bottles' => $data['calculations']['total_bottles'],
                            'total_boxes' => ceil($data['calculations']['total_boxes']),
                            'subtotal' => $data['calculations']['subtotal'],
                            'channel' => $channel,
                            'shipping_address' => $data['header_data']['Ship To'],
                            'status' => 'Pendiente',
                            'created_by_user_id' => Auth::id(),
                        ]
                    );

                    $order->details()->createMany($data['details']);
                    $processedDocNumbers[] = $docNum;
                }
            });
        }

        $successMessage = !empty($processedDocNumbers) ? 'Se procesaron exitosamente ' . count($processedDocNumbers) . ' pedidos.' : '';
        
        if (!empty($errorRows)) {
            session()->put('import_error_rows', $errorRows);
            $warningMessage = 'Se encontraron errores en la importación. Revisa la sección de errores y descarga el reporte.';
            if ($successMessage) {
                session()->flash('success', $successMessage);
            }
            return back()->with('warning', $warningMessage);
        }

        return redirect()->route('customer-service.orders.index')->with('success', $successMessage ?: 'Archivo procesado. No se encontraron nuevos pedidos para importar.');
    }

    /**
     * Descarga el archivo CSV con los errores de la última importación.
     */
    public function downloadImportErrors()
    {
        $errorRows = session('import_error_rows', []);
        if (empty($errorRows)) {
            return redirect()->route('customer-service.orders.index')->with('info', 'No hay errores de importación para descargar.');
        }

        // --- CORRECCIÓN: Se añade el BOM (Byte Order Mark) para asegurar la codificación UTF-8 en Excel ---
        $headers = [
            'BP Reference No.', 'Document Number', 'Posting Date', 'Customer/Vendor Code',
            'Customer/Vendor Name', 'Warehouse Code', 'Warehouse Name', 'Item No.',
            'Item/Service Description', 'Quantity', 'Row Total', 'Gross Total', 'Ship To', 'Motivo del Error'
        ];

        $processedRows = [];
        foreach ($errorRows as $row) {
            $processedRow = [];
            foreach ($headers as $header) {
                $processedRow[$header] = $row[$header] ?? '';
            }
            $processedRows[] = $processedRow;
        }

        $responseHeaders = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=errores_importacion_so.csv",
        ];        
        
        $callback = function() use ($processedRows, $headers) {
            $file = fopen('php://output', 'w');
            
            // Escribir el BOM para Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Escribir el encabezado
            fputcsv($file, $headers);
            
            // Escribir los datos procesados
            foreach ($processedRows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };
        
        // Limpiar los errores de la sesión después de generar la descarga
        session()->forget('import_error_rows');

        return response()->stream($callback, 200, $responseHeaders);
    }

    /**
     * Permite descargar la plantilla CSV.
     */
    public function downloadTemplate()
    {
        $headers = [ "Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=plantilla_carga_so.csv" ];
        $callback = function() {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, [
                'BP Reference No.', 'Document Number', 'Posting Date', 'Customer/Vendor Code',
                'Customer/Vendor Name', 'Warehouse Code', 'Warehouse Name', 'Item No.',
                'Item/Service Description', 'Quantity', 'Row Total', 'Gross Total', 'Ship To'
            ]);
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Muestra el dashboard de pedidos.
     */
    public function dashboard()
    {
        $ordersByChannel = CsOrder::select('channel', DB::raw('count(*) as total'))
            ->groupBy('channel')->pluck('total', 'channel');
        $ordersByStatus = CsOrder::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')->pluck('total', 'status');
        $amountByChannel = CsOrder::select('channel', DB::raw('SUM(subtotal) as total_amount'))
            ->groupBy('channel')->pluck('total_amount', 'channel');
        $topCustomersByOrders = CsOrder::select('customer_name', DB::raw('count(*) as total'))
            ->groupBy('customer_name')->orderBy('total', 'desc')->limit(10)->pluck('total', 'customer_name');
        $topCustomersByAmount = CsOrder::select('customer_name', DB::raw('SUM(subtotal) as total_amount'))
            ->groupBy('customer_name')->orderBy('total_amount', 'desc')->limit(10)->pluck('total_amount', 'customer_name');
        $recentOrders = CsOrder::where('creation_date', '>=', now()->subDays(30))
            ->groupBy('date')->orderBy('date', 'ASC')
            ->get([ DB::raw('DATE(creation_date) as date'), DB::raw('COUNT(*) as count') ]);
        $bottlesByChannel = CsOrder::select('channel', DB::raw('SUM(total_bottles) as total_bottles'))
            ->groupBy('channel')->pluck('total_bottles', 'channel');
        $avgSubtotal = CsOrder::avg('subtotal');
        $completionStatus = CsOrder::select(
                DB::raw("SUM(CASE WHEN status = 'Cancelado' THEN 1 ELSE 0 END) as cancelled"),
                DB::raw("SUM(CASE WHEN status != 'Cancelado' THEN 1 ELSE 0 END) as not_cancelled")
            )->first();
        $topWarehouses = CsOrder::select('origin_warehouse', DB::raw('count(*) as total'))
            ->groupBy('origin_warehouse')->orderBy('total', 'desc')->limit(5)->pluck('total', 'origin_warehouse');

        $chartData = [
            'ordersByChannel' => ['labels' => $ordersByChannel->keys(), 'data' => $ordersByChannel->values()],
            'ordersByStatus' => ['labels' => $ordersByStatus->keys(), 'data' => $ordersByStatus->values()],
            'amountByChannel' => ['labels' => $amountByChannel->keys(), 'data' => $amountByChannel->values()],
            'topCustomersByOrders' => ['labels' => $topCustomersByOrders->keys(), 'data' => $topCustomersByOrders->values()],
            'topCustomersByAmount' => ['labels' => $topCustomersByAmount->keys(), 'data' => $topCustomersByAmount->values()],
            'recentOrders' => ['labels' => $recentOrders->pluck('date')->map(fn($date) => Carbon::parse($date)->format('d M')), 'data' => $recentOrders->pluck('count')],
            'bottlesByChannel' => ['labels' => $bottlesByChannel->keys(), 'data' => $bottlesByChannel->values()],
            'avgSubtotal' => number_format($avgSubtotal, 2),
            'completionStatus' => $completionStatus,
            'topWarehouses' => ['labels' => $topWarehouses->keys(), 'data' => $topWarehouses->values()],
        ];

        return view('customer-service.orders.dashboard', compact('chartData'));
    }

    public function clearImportErrorsSession()
    {
        session()->forget('import_error_rows');
        return response()->json(['message' => 'Errores de importación borrados.']);
    }

    public function create()
    {
        $channels = ['Corporate', 'Especialista', 'Moderno', 'On', 'On trade', 'Private' ,'POSM'];
        $customers = CsCustomer::all()->sortBy('name');
        $warehouses = CsWarehouse::all()->sortBy('name');
        $products = CsProduct::all()->sortBy('sku');        
        return view('customer-service.orders.create', compact('channels', 'customers', 'warehouses', 'products'));
    }

    public function store(Request $request)
    {
        // Validación actualizada para incluir el subtotal por SKU
        $validatedData = $request->validate([
            'so_number' => 'required|string|max:255|unique:cs_orders,so_number',
            'purchase_order' => 'nullable|string|max:255',
            'creation_date' => 'required|date_format:Y-m-d',
            'customer_name' => 'required|string|max:255|exists:cs_customers,name',
            'channel' => 'required|string|max:255',
            'origin_warehouse' => 'required|string|max:255|exists:cs_warehouses,name',
            'shipping_address' => 'required|string|max:255',
            'details' => 'required|array|min:1',
            'details.*.sku' => 'required|string|exists:cs_products,sku',
            'details.*.quantity' => 'required|integer|min:1',
            'details.*.subtotal' => 'required|numeric|min:0', // Nueva validación
        ]);

        $totalBottles = 0;
        $totalBoxes = 0;
        $totalSubtotal = 0;
        $hasPromoItem = false;
        
        $products = CsProduct::all()->keyBy('sku');

        foreach ($validatedData['details'] as $detail) {
            $product = $products[$detail['sku']];
            
            $totalBottles += $detail['quantity'];
            $totalBoxes += ($product->packaging_factor > 0) ? ($detail['quantity'] / $product->packaging_factor) : 0;
            
            // --- CORRECCIÓN: Suma el subtotal proporcionado en el formulario ---
            $totalSubtotal += $detail['subtotal'];
            
            if ($product->type === 'Promocional') {
                $hasPromoItem = true;
            }
        }

        $orderChannel = $hasPromoItem ? 'POSM' : $validatedData['channel'];

        $order = CsOrder::create([
            'so_number' => $validatedData['so_number'],
            'purchase_order' => $validatedData['purchase_order'],
            'creation_date' => $validatedData['creation_date'],
            'authorization_date' => now()->format('Y-m-d'),
            'customer_name' => $validatedData['customer_name'],
            'channel' => $orderChannel,
            'origin_warehouse' => $validatedData['origin_warehouse'],
            'total_bottles' => $totalBottles,
            'total_boxes' => ceil($totalBoxes),
            'subtotal' => $totalSubtotal, // Usa el subtotal calculado
            'shipping_address' => $validatedData['shipping_address'],
            'status' => 'Pendiente',
            'created_by_user_id' => Auth::id(),
        ]);

        // Elimina el subtotal de los detalles antes de crear los registros secundarios
        $detailsToCreate = collect($validatedData['details'])->map(function($detail) {
            unset($detail['subtotal']);
            return $detail;
        })->toArray();
        
        $order->details()->createMany($detailsToCreate);

        CsOrderEvent::create([
            'cs_order_id' => $order->id,
            'user_id' => Auth::id(),
            'description' => 'El usuario ' . Auth::user()->name . ' creó el pedido manualmente.'
        ]);

        return redirect()->route('customer-service.orders.show', $order)->with('success', 'Pedido creado exitosamente.');
    }

    public function editOriginalData(CsOrder $order)
    {
        $channels = ['Corporate', 'Especialista', 'Moderno', 'On', 'On trade', 'Private' ,'POSM'];
        $customers = CsCustomer::all()->sortBy('name');
        $warehouses = CsWarehouse::all()->sortBy('name');
        $products = CsProduct::all()->sortBy('sku'); // Obtiene todos los productos

        return view('customer-service.orders.edit-original', compact('order', 'channels', 'customers', 'warehouses', 'products'));
    }

    // Nuevo método para actualizar los datos originales
    public function updateOriginalData(Request $request, CsOrder $order)
    {
        $validatedData = $request->validate([
            'so_number' => 'required|string|max:255|unique:cs_orders,so_number,'.$order->id,
            'purchase_order' => 'nullable|string|max:255',
            'customer_name' => 'required|string|max:255|exists:cs_customers,name',
            'channel' => 'required|string|max:255',
            'origin_warehouse' => 'required|string|max:255|exists:cs_warehouses,name',
            'total_bottles' => 'required|integer|min:0',
            'total_boxes' => 'required|integer|min:0',
            'subtotal' => 'required|numeric|min:0',
            'shipping_address' => 'required|string|max:255',
        ]);
        
        $changes = [];
        $oldOrderData = $order->getOriginal();

        foreach ($validatedData as $key => $value) {
            $oldValue = $oldOrderData[$key];
            if ($oldValue != $value) {
                $fieldName = ucwords(str_replace('_', ' ', $key));
                $changes[] = "cambió '{$fieldName}' de '".($oldValue ?? 'vacío')."' a '".$value."'";
            }
        }

        $order->update($validatedData);

        if (!empty($changes)) {
            CsOrderEvent::create([
                'cs_order_id' => $order->id,
                'user_id' => Auth::id(),
                'description' => 'El usuario ' . Auth::user()->name . ' actualizó los datos originales: ' . implode(', ', $changes) . '.'
            ]);
        }

        return redirect()->route('customer-service.orders.show', $order)->with('success', 'Datos originales actualizados exitosamente.');
    }


}