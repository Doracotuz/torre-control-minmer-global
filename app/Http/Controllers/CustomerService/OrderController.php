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
use App\Models\Guia;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\CsPlanning;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


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

        // Filtro de búsqueda rápida (se mantiene)
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('so_number', 'like', $searchTerm)
                  ->orWhere('purchase_order', 'like', $searchTerm)
                  ->orWhere('customer_name', 'like', $searchTerm)
                  ->orWhere('invoice_number', 'like', $searchTerm)
                  ->orWhere('client_contact', 'like', $searchTerm);
            });
        }
        
        // Filtros básicos (se mantienen)
        if ($request->filled('status')) { $query->where('status', $request->status); }
        if ($request->filled('channel')) { $query->where('channel', $request->channel); }
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('creation_date', [$request->date_from, $request->date_to]);
        }

        // --- INICIA CAMBIO: Se añaden los nuevos filtros avanzados ---
        if ($request->filled('status')) { $query->where('status', $request->status); }
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('creation_date', [$request->date_from, $request->date_to]);
        }

        // --- INICIA CAMBIO: Se añaden TODAS las columnas filtrables del modal ---
        if ($request->filled('purchase_order_adv')) { $query->where('purchase_order', 'like', '%' . $request->purchase_order_adv . '%'); }
        if ($request->filled('bt_oc')) { $query->where('bt_oc', 'like', '%' . $request->bt_oc . '%'); }
        if ($request->filled('customer_name_adv')) { $query->where('customer_name', 'like', '%' . $request->customer_name_adv . '%'); }
        if ($request->filled('channel')) { $query->where('channel', $request->channel); }
        if ($request->filled('invoice_number_adv')) { $query->where('invoice_number', 'like', '%' . $request->invoice_number_adv . '%'); }
        if ($request->filled('invoice_date')) { $query->whereDate('invoice_date', $request->invoice_date); }
        if ($request->filled('origin_warehouse')) { $query->where('origin_warehouse', 'like', '%' . $request->origin_warehouse . '%'); }
        if ($request->filled('destination_locality')) { $query->where('destination_locality', 'like', '%' . $request->destination_locality . '%'); }
        if ($request->filled('delivery_date')) { $query->whereDate('delivery_date', $request->delivery_date); }
        if ($request->filled('executive')) { $query->where('executive', 'like', '%' . $request->executive . '%'); }
        if ($request->filled('evidence_reception_date')) { $query->whereDate('evidence_reception_date', $request->evidence_reception_date); }
        if ($request->filled('evidence_cutoff_date')) { $query->whereDate('evidence_cutoff_date', $request->evidence_cutoff_date); }
        // --- TERMINA CAMBIO ---

        $perPage = $request->input('per_page', 10);

        $orders = $query->paginate($perPage)->withQueryString();

        return response()->json($orders);
    }

    /**
     * Muestra la vista de detalle de un pedido.
     */
    public function show(CsOrder $order)
    {
        // 1. Cargamos todas las relaciones necesarias para evitar consultas N+1
        $order->load([
            'details.product',
            'events.user',
            'plannings.guia.eventos.user'
        ]);

        $timelineEvents = collect();

        // 2. Procesa los eventos del PEDIDO (CsOrderEvent), que ahora incluyen los de Guía y Auditoría
        foreach ($order->events as $event) {
            $type = 'Pedido';
            $color = 'blue'; // Color por defecto

            $description = strtolower($event->description); // Convertimos a minúsculas para una comparación segura

            if (str_contains($description, 'auditoría')) {
                $type = 'Auditoría';
                $color = 'yellow';
            } elseif (str_contains($description, 'planificación')) {
                $type = 'Planificación';
                $color = 'purple';
            } elseif (str_contains($description, 'guía')) { // <-- LÓGICA AÑADIDA
                $type = 'Guía';
                $color = 'green';
            }

            $timelineEvents->push([
                'type' => $type,
                'description' => $event->description,
                'user_name' => $event->user->name ?? 'Sistema',
                'date' => $event->created_at,
                'color' => $color,
            ]);
        }

        // 3. (Opcional) Procesa eventos de la tabla 'eventos' si aún los usas para algo específico
        if ($guia = optional(optional($order->plannings)->first())->guia) {
            foreach ($guia->eventos as $event) {
                // Si encuentras eventos duplicados, puedes comentar o eliminar este bloque
                $timelineEvents->push([
                    'type' => 'Logística (Legacy)',
                    'description' => $event->descripcion,
                    'user_name' => $event->user->name ?? 'Sistema',
                    'date' => $event->fecha_evento,
                    'color' => 'gray',
                ]);
            }
        }
        
        // 4. Ordenamos la colección final por fecha
        $timelineEvents = $timelineEvents->sortByDesc('date');

        return view('customer-service.orders.show', compact('order', 'timelineEvents'));
    }
    /**
     * Muestra el formulario para editar un pedido.
     */
    public function edit(CsOrder $order)
    {
        $order->load(['details.product']);
        // $products = CsProduct::all()->sortBy('sku');
        return view('customer-service.orders.edit', compact('order'));
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
    public function cancel(Request $request, CsOrder $order)
    {
        DB::transaction(function () use ($order) {
            // 1. Actualizar el estatus de la Orden principal
            $order->update(['status' => 'Cancelado', 'updated_by_user_id' => Auth::id()]);

            // 2. Buscar todos los registros de planificación asociados a esta orden
            $planningRecords = CsPlanning::where('cs_order_id', $order->id)->get();

            if ($planningRecords->isNotEmpty()) {
                $planningIds = $planningRecords->pluck('id');
                $guiaIds = $planningRecords->pluck('guia_id')->filter()->unique();

                // 3. Cancelar todos los registros de planificación de una sola vez
                CsPlanning::whereIn('id', $planningIds)->update(['status' => 'Cancelado']);

                // 4. Procesar cada guía afectada
                if ($guiaIds->isNotEmpty()) {
                    $guias = Guia::with('facturas')->whereIn('id', $guiaIds)->get();
                    
                    foreach ($guias as $guia) {
                        // Eliminar las facturas de esta guía que pertenecen a la orden cancelada
                        $guia->facturas()->whereIn('cs_planning_id', $planningIds)->delete();
                        
                        // Refrescar la relación para contar las facturas restantes
                        $guia->load('facturas');

                        // Si la guía se quedó sin facturas, se cancela la guía completa
                        if ($guia->facturas->isEmpty()) {
                            $guia->update(['estatus' => 'Cancelado']);
                        }
                    }
                }
            }
            
            // 5. Registrar el evento en la línea de tiempo de la orden
            CsOrderEvent::create([
                'cs_order_id' => $order->id,
                'user_id' => Auth::id(),
                'description' => 'El usuario ' . Auth::user()->name . ' canceló el pedido. El cambio se ha propagado a Planificación y Guías.'
            ]);
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'El pedido ha sido cancelado exitosamente en todos los módulos.']);
        }

        return back()->with('success', 'El pedido ha sido cancelado exitosamente en todos los módulos.');
    }

    /**
     * Mueve un pedido a la tabla de planificación.
     */
    public function moveToPlan(CsOrder $order)
    {
        // Usaremos el cs_order_id para verificar si ya existe en planificación
        if (\App\Models\CsPlanning::where('cs_order_id', $order->id)->exists()) {
            return back()->with('error', 'Este pedido ya ha sido enviado a planificación.');
        }

        DB::transaction(function () use ($order) {
            $order->update(['status' => 'En Planificación', 'updated_by_user_id' => Auth::id()]);

            // Crear el registro en la nueva tabla de planificación
            \App\Models\CsPlanning::create([
                'cs_order_id' => $order->id,
                'fecha_entrega' => $order->delivery_date,
                'origen' => $order->origin_warehouse,
                'direccion' => $order->shipping_address,
                'razon_social' => $order->client_contact ?: $order->customer_name,
                'hora_cita' => $order->schedule,
                'so_number' => $order->so_number,
                'factura' => $order->invoice_number ?: $order->so_number,
                'pzs' => $order->total_bottles,
                'cajas' => $order->total_boxes,
                'subtotal' => $order->subtotal,
                'canal' => $order->channel,
                'destino' => $order->destination_locality,
                'status' => 'En Espera', // Estatus por default
            ]);

            CsOrderEvent::create([
                'cs_order_id' => $order->id,
                'user_id' => Auth::id(),
                'description' => 'El usuario ' . Auth::user()->name . ' marcó el pedido SO: ' . $order->so_number . ' como "Listo" y lo envió a planificación.'
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

        // Eliminamos el BOM (ï»¿) si existe
        if (strpos($fileContent, "\xEF\xBB\xBF") === 0) {
            $fileContent = substr($fileContent, 3);
        }

        $file = fopen("php://memory", 'r+');
        fwrite($file, $fileContent);
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

                    Log::info('DEBUG - Claves recibidas:', array_keys($data['header_data']));


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

                    CsOrderEvent::create([
                        'cs_order_id' => $order->id,
                        'user_id' => Auth::id(),
                        'description' => 'El pedido fue creado por ' . Auth::user()->name . ' mediante una importación masiva (CSV).'
                    ]);                    
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

    public function bulkEdit(Request $request)
    {
        $request->validate(['ids' => 'required|array|min:1']);
        $orderIds = $request->query('ids');

        // --- INICIA MODIFICACIÓN ---
        // 1. Obtenemos las órdenes para acceder a sus datos (id y so_number)
        $orders = CsOrder::whereIn('id', $orderIds)->select('id', 'so_number', 'origin_warehouse')->get();
        $ordersCount = $orders->count();
        
        // 2. Extraemos solo los números de SO para mostrarlos en el título
        $soNumbers = $orders->pluck('so_number')->all();

        $firstOrderOrigin = $orders->first() ? $orders->first()->origin_warehouse : '';

        // 3. Pasamos la colección de órdenes y los números de SO a la vista
        return view('customer-service.orders.bulk-edit', compact('orders', 'ordersCount', 'soNumbers', 'firstOrderOrigin'));
        // --- TERMINA MODIFICACIÓN ---
    }

    /**
     * Procesa la actualización masiva de órdenes.
     */
    public function bulkUpdate(Request $request)
    {
        $validatedData = $request->validate([
            'ids' => 'required|string',
            // Campos generales
            'delivery_date' => 'nullable|date_format:Y-m-d',
            'schedule' => 'nullable|string|max:255',
            'client_contact' => 'nullable|string|max:255',
            'shipping_address' => 'nullable|string',
            'destination_locality' => 'nullable|string|max:255',
            'executive' => 'nullable|string|max:255',
            'evidence_reception_date' => 'nullable|date_format:Y-m-d',
            'evidence_cutoff_date' => 'nullable|date_format:Y-m-d',
            // --- INICIA MODIFICACIÓN ---
            'invoices' => 'nullable|array',
            'invoices.*.invoice_number' => 'nullable|string|max:255',
            'invoices.*.invoice_date' => 'nullable|date_format:Y-m-d',
            'invoices.*.bt_oc' => 'nullable|string|max:255', // Campo BT OC añadido
            // --- TERMINA MODIFICACIÓN ---
        ]);

        $orderIds = json_decode($validatedData['ids']);
        
        $generalDataToUpdate = collect($validatedData)
            ->except(['ids', 'invoices'])
            ->filter()
            ->all();
        
        $individualInvoices = $validatedData['invoices'] ?? [];

        if (empty($generalDataToUpdate) && empty($individualInvoices)) {
            return redirect()->route('customer-service.orders.index')->with('info', 'No se especificaron cambios para aplicar.');
        }

        DB::transaction(function () use ($orderIds, $generalDataToUpdate, $individualInvoices) {
            if (!empty($generalDataToUpdate)) {
                CsOrder::whereIn('id', $orderIds)->update($generalDataToUpdate);
            }

            foreach ($individualInvoices as $orderId => $invoiceData) {
                $filteredInvoiceData = array_filter($invoiceData, fn($value) => !is_null($value) && $value !== '');
                if (!empty($filteredInvoiceData)) {
                    CsOrder::where('id', $orderId)->update($filteredInvoiceData);
                }
            }

            $changesDescription = 'actualización masiva.';
            foreach ($orderIds as $orderId) {
                CsOrderEvent::create([
                    'cs_order_id' => $orderId,
                    'user_id' => auth()->id(),
                    'description' => 'El usuario ' . auth()->user()->name . ' realizó una ' . $changesDescription
                ]);
            }
        });
        
        return redirect()->route('customer-service.orders.index')->with('success', count($orderIds) . ' órdenes actualizadas exitosamente.');
    }
    
    
    public function exportCsv(Request $request)
    {
        // 1. Se inicia la consulta cargando las relaciones para acceder a los datos heredados.
        // Usamos 'plannings' que es la relación correcta para un pedido que puede tener escalas.
        $query = CsOrder::with(['plannings.guia'])->orderBy('creation_date', 'desc');

        // 2. Se aplica la misma lógica de filtrado que en la vista, incluyendo TODOS los filtros.
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('so_number', 'like', $searchTerm)
                  ->orWhere('purchase_order', 'like', $searchTerm)
                  ->orWhere('customer_name', 'like', $searchTerm)
                  ->orWhere('invoice_number', 'like', $searchTerm)
                  ->orWhere('client_contact', 'like', $searchTerm);
            });
        }
        
        if ($request->filled('status')) { $query->where('status', $request->status); }
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('creation_date', [$request->date_from, $request->date_to]);
        }

        // Aplicación de todos los filtros avanzados
        if ($request->filled('purchase_order_adv')) { $query->where('purchase_order', 'like', '%' . $request->purchase_order_adv . '%'); }
        if ($request->filled('bt_oc')) { $query->where('bt_oc', 'like', '%' . $request->bt_oc . '%'); }
        if ($request->filled('customer_name_adv')) { $query->where('customer_name', 'like', '%' . $request->customer_name_adv . '%'); }
        if ($request->filled('channel')) { $query->where('channel', $request->channel); }
        if ($request->filled('invoice_number_adv')) { $query->where('invoice_number', 'like', '%' . $request->invoice_number_adv . '%'); }
        if ($request->filled('invoice_date')) { $query->whereDate('invoice_date', $request->invoice_date); }
        if ($request->filled('origin_warehouse')) { $query->where('origin_warehouse', 'like', '%' . $request->origin_warehouse . '%'); }
        if ($request->filled('destination_locality')) { $query->where('destination_locality', 'like', '%' . $request->destination_locality . '%'); }
        if ($request->filled('delivery_date')) { $query->whereDate('delivery_date', $request->delivery_date); }
        if ($request->filled('executive')) { $query->where('executive', 'like', '%' . $request->executive . '%'); }
        if ($request->filled('evidence_reception_date')) { $query->whereDate('evidence_reception_date', $request->evidence_reception_date); }
        if ($request->filled('evidence_cutoff_date')) { $query->whereDate('evidence_cutoff_date', $request->evidence_cutoff_date); }

        $orders = $query->get();
        
        $fileName = "export_pedidos_" . date('Y-m-d_H-i-s') . ".csv";
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($orders) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para compatibilidad con Excel y acentos

            // Definimos las columnas del CSV
            fputcsv($file, [
                'SO', 'Orden Compra', 'Razón Social', 'Estatus', 'F. Creación', 'Canal', 
                'Factura', 'F. Factura', 'Almacén Origen', 'Localidad Destino', 'Botellas', 'Cajas', 'Subtotal',
                'F. Entrega', 'Horario', 'Contacto', 'Dirección', 'Ejecutivo', 'Observaciones',
                'Guia Asignada', 'Operador', 'Placas', 'Custodia'
            ]);

            foreach ($orders as $order) {
                // Tomamos el primer registro de planificación para los datos heredados
                $planning = $order->plannings->first();

                $row = [
                    $order->so_number, $order->purchase_order, $order->customer_name, $order->status,
                    $order->creation_date?->format('Y-m-d'), $order->channel, $order->invoice_number,
                    $order->invoice_date?->format('Y-m-d'), $order->origin_warehouse, $order->destination_locality,
                    $order->total_bottles, $order->total_boxes, $order->subtotal,
                    $order->delivery_date?->format('Y-m-d'), $order->schedule, $order->client_contact,
                    $order->shipping_address, $order->executive, $order->observations,
                    // Datos heredados con validación para evitar errores
                    $planning->guia->guia ?? 'N/A',
                    $planning->guia->operador ?? 'N/A',
                    $planning->guia->placas ?? 'N/A',
                    $planning->guia->custodia ?? 'N/A',
                ];
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function bulkMoveToPlan(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:cs_orders,id',
        ]);

        $orderIds = $validated['ids'];
        $processedCount = 0;
        $skippedCount = 0;
        $skippedAlreadyPlanned = 0;

        DB::transaction(function () use ($orderIds, &$processedCount, &$skippedCount, &$skippedAlreadyPlanned) {
            $orders = CsOrder::whereIn('id', $orderIds)->get();

            foreach ($orders as $order) {
                // Solo procesamos pedidos que estén en estatus 'Pendiente'
                if ($order->status !== 'Pendiente') {
                    $skippedCount++;
                    continue;
                }

                // Verificamos si ya existe en planificación para evitar duplicados
                if (CsPlanning::where('cs_order_id', $order->id)->exists()) {
                    $skippedAlreadyPlanned++;
                    continue;
                }

                $order->update(['status' => 'En Planificación', 'updated_by_user_id' => Auth::id()]);

                // Crear el registro en la tabla de planificación
                CsPlanning::create([
                    'cs_order_id' => $order->id,
                    'fecha_entrega' => $order->delivery_date,
                    'origen' => $order->origin_warehouse,
                    'direccion' => $order->shipping_address,
                    'razon_social' => $order->client_contact ?: $order->customer_name,
                    'hora_cita' => $order->schedule,
                    'so_number' => $order->so_number,
                    'factura' => $order->invoice_number ?: $order->so_number,
                    'pzs' => $order->total_bottles,
                    'cajas' => $order->total_boxes,
                    'subtotal' => $order->subtotal,
                    'canal' => $order->channel,
                    'destino' => $order->destination_locality,
                    'status' => 'En Espera',
                ]);

                CsOrderEvent::create([
                    'cs_order_id' => $order->id,
                    'user_id' => Auth::id(),
                    'description' => 'El usuario ' . Auth::user()->name . ' envió el pedido SO: ' . $order->so_number . ' a planificación (acción masiva).'
                ]);

                $processedCount++;
            }
        });

        $message = "{$processedCount} pedidos enviados a planificación exitosamente.";
        if ($skippedCount > 0) {
            $message .= " Se omitieron {$skippedCount} pedidos por no tener estatus 'Pendiente'.";
        }
        if ($skippedAlreadyPlanned > 0) {
            $message .= " Se omitieron {$skippedAlreadyPlanned} pedidos que ya estaban en planificación.";
        }

        return redirect()->route('customer-service.orders.index')->with('success', $message);
    }    


}