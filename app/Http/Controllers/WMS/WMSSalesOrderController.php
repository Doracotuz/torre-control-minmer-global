<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\WMS\InventoryStock; // Aún podría ser útil para otras lógicas o reportes
use App\Models\WMS\Quality;
use App\Models\Product;
use App\Models\WMS\SalesOrder;
use App\Models\WMS\SalesOrderLine;
use App\Models\WMS\PalletItem;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Log;

class WMSSalesOrderController extends Controller
{
    /**
     * Muestra una lista paginada de Órdenes de Venta.
     */
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
        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }
        if ($request->filled('customer_name')) {
            $query->where('customer_name', 'like', '%' . $request->customer_name . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            // Filtra por 'order_date' mayor o igual a la fecha de inicio
            $query->whereDate('order_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            // Filtra por 'order_date' menor o igual a la fecha de fin
            $query->whereDate('order_date', '<=', $request->end_date);
        }        

        $salesOrders = $query->latest()->paginate(15)->withQueryString();

        // Lógica para las tarjetas de KPI
        $kpiQuery = SalesOrder::query();
        
        // Si hay filtros aplicados, los KPIs también deben reflejarlos
        if ($request->filled('so_number')) { $kpiQuery->where('so_number', 'like', '%' . $request->so_number . '%'); }
        if ($request->filled('invoice_number')) { $kpiQuery->where('invoice_number', 'like', '%' . $request->invoice_number . '%'); }
        if ($request->filled('customer_name')) { $kpiQuery->where('customer_name', 'like', '%' . $request->customer_name . '%'); }
        if ($request->filled('status')) { $kpiQuery->where('status', $request->status); }
        if ($request->filled('start_date')) { $kpiQuery->whereDate('order_date', '>=', $request->start_date); }
        if ($request->filled('end_date')) { $kpiQuery->whereDate('order_date', '<=', $request->end_date); }

        // Clonamos la consulta para obtener los diferentes conteos
        $kpis = [
            'total' => (clone $kpiQuery)->count(),
            'pending' => (clone $kpiQuery)->where('status', 'Pending')->count(),
            'picking' => (clone $kpiQuery)->where('status', 'Picking')->count(),
            'packed' => (clone $kpiQuery)->where('status', 'Packed')->count(),
        ];

        return view('wms.sales-orders.index', compact('salesOrders', 'kpis'));
    }

    /**
     * Muestra el formulario para crear una nueva Orden de Venta.
     */
    public function create()
    {
        // Obtiene todos los lotes de inventario FÍSICO disponibles
        $stockData = PalletItem::where('quantity', '>', 0)
            ->with(['product', 'quality', 'pallet.purchaseOrder']) // Carga relaciones necesarias para el dropdown
            ->get();
            
        return view('wms.sales-orders.create', compact('stockData'));
    }

    /**
     * Guarda una nueva Orden de Venta en la base de datos.
     */
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
                'order_date' => $validated['delivery_date'], // Asegúrate que el modelo lo castea a fecha/hora
                'user_id' => Auth::id(),
                'status' => 'Pending', // Estado inicial
            ]);

            foreach ($validated['lines'] as $line) {
                // Busca el PalletItem específico
                $palletItem = PalletItem::with('pallet', 'product')->findOrFail($line['pallet_item_id']);

                // --- LÓGICA DE STOCK COMPROMETIDO (EN PALLETITEM) ---
                // Calcula la cantidad disponible REAL en este LOTE específico
                $availableQuantityInPallet = $palletItem->quantity - $palletItem->committed_quantity;

                // Valida si hay suficiente stock disponible en ESTE LOTE
                if ($availableQuantityInPallet < $line['quantity']) {
                    throw new \Exception("La cantidad ({$line['quantity']}) para el SKU {$palletItem->product->sku} en el LPN {$palletItem->pallet->lpn} excede el stock disponible ({$availableQuantityInPallet}) en ese lote.");
                }
                
                // Incrementamos la cantidad comprometida EN EL PALLETITEM específico
                $palletItem->increment('committed_quantity', $line['quantity']);

                // Crear la línea de la orden de venta, guardando el pallet_item_id
                $salesOrder->lines()->create([
                    'product_id' => $palletItem->product_id,
                    'pallet_item_id' => $line['pallet_item_id'], // ¡Importante!
                    'quantity_ordered' => $line['quantity'],
                    // 'quality_id' => $palletItem->quality_id, // Podrías guardar quality_id aquí también si lo necesitas
                ]);
            }

            DB::commit();
            return redirect()->route('wms.sales-orders.index')->with('success', 'Orden de Venta creada e inventario comprometido exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error("Error al crear SO: " . $e->getMessage()); // Opcional: Registrar error
            return back()->with('error', 'Error al crear la orden: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Muestra los detalles de una Orden de Venta específica.
     */
    public function show(SalesOrder $salesOrder)
    {
        // Carga todas las relaciones necesarias para la vista detallada
        $salesOrder->load([
            'user', 
            'lines.product',
            'lines.palletItem.pallet.location', // Carga el pallet y su ubicación
            'lines.palletItem.pallet.purchaseOrder', // Carga la PO para el pedimento
            'lines.palletItem.quality', // Carga la calidad
            'pickList' // Carga la picklist si existe
        ]);

        return view('wms.sales-orders.show', compact('salesOrder'));
    }

    /**
     * Muestra el formulario para editar una Orden de Venta existente.
     */
    public function edit(SalesOrder $salesOrder)
    {
        // 1. Validar que la orden se pueda editar (solo si está Pendiente)
        if ($salesOrder->status !== 'Pending') {
            return redirect()->route('wms.sales-orders.show', $salesOrder)->with('error', 'No se puede editar una orden que ya está en proceso de surtido.');
        }

        // 2. Cargar las relaciones de las LÍNEAS EXISTENTES para el formulario
        $salesOrder->load(
            'lines.palletItem.product',
            'lines.palletItem.pallet.purchaseOrder',
            'lines.palletItem.quality'
        );
        
        // 3. Obtener los IDs de los lotes que esta orden YA está usando.
        $existingItemIds = $salesOrder->lines->pluck('pallet_item_id')->filter()->unique();

        // 4. Cargar el stock disponible, asegurándonos de incluir los lotes
        //    que ya están en la orden, incluso si su cantidad física es 0.
        $stockData = PalletItem::query()
            // Opción A: Lotes con cantidad física disponible
            ->where('quantity', '>', 0)
            // Opción B: O lotes que ya están en esta orden (para que aparezcan seleccionados)
            ->orWhereIn('id', $existingItemIds)
            // Cargar todas las relaciones necesarias para el dropdown en la vista
            ->with([
                'product', 
                'quality', 
                'pallet.purchaseOrder'
            ])
            ->get();
            
        // 5. Manda las variables a la vista de edición
        return view('wms.sales-orders.edit', compact('salesOrder', 'stockData'));
    }


    /**
     * Actualiza una Orden de Venta en la base de datos.
     */
    public function update(Request $request, SalesOrder $salesOrder)
    {
        // Solo permite actualizar órdenes pendientes
        if ($salesOrder->status !== 'Pending') {
            return back()->with('error', 'No se puede actualizar una orden que ya está en proceso.');
        }

        $validated = $request->validate([
            'so_number' => ['required', 'string', 'max:255', Rule::unique('sales_orders')->ignore($salesOrder->id)],
            'invoice_number' => 'nullable|string|max:255',
            'customer_name' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'lines' => 'required|array|min:1',
            'lines.*.pallet_item_id' => 'required|exists:pallet_items,id',
            'lines.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // 1. Revertir el stock comprometido original (desde PalletItem)
            //    Cargamos 'palletItem' para asegurarnos de que esté disponible
            foreach ($salesOrder->lines()->with('palletItem')->get() as $oldLine) {
                if ($oldLine->palletItem) { // Verifica si el pallet item aún existe
                    // Usamos max(0, ...) para evitar que baje de cero si algo raro pasó
                    $newCommitted = max(0, $oldLine->palletItem->committed_quantity - $oldLine->quantity_ordered);
                    $oldLine->palletItem->update(['committed_quantity' => $newCommitted]);
                    // $oldLine->palletItem->decrement('committed_quantity', $oldLine->quantity_ordered); // Alternativa
                }
            }

            // 2. Borrar las líneas antiguas de la orden
            $salesOrder->lines()->delete();

            // 3. Actualizar la cabecera de la orden
            $salesOrder->update([
                'so_number' => $validated['so_number'],
                'invoice_number' => $validated['invoice_number'],
                'customer_name' => $validated['customer_name'],
                'order_date' => $validated['delivery_date'],
                // No actualizamos 'user_id' ni 'status' aquí
            ]);

            // 4. Crear las nuevas líneas y comprometer el nuevo stock (en PalletItem)
            foreach ($validated['lines'] as $line) {
                // Busca el PalletItem, cargando relaciones necesarias para validación/datos
                $palletItem = PalletItem::with('pallet', 'product')->findOrFail($line['pallet_item_id']);
                
                // Calcula disponibilidad en este lote
                $availableQuantityInPallet = $palletItem->quantity - $palletItem->committed_quantity;

                // Valida stock disponible en el lote
                if ($availableQuantityInPallet < $line['quantity']) {
                    throw new \Exception("La cantidad ({$line['quantity']}) para el SKU {$palletItem->product->sku} en el LPN {$palletItem->pallet->lpn} excede el stock disponible ({$availableQuantityInPallet}) en ese lote.");
                }
                
                // Incrementa compromiso en el PalletItem
                $palletItem->increment('committed_quantity', $line['quantity']);

                // Crea la nueva línea de la orden
                $salesOrder->lines()->create([
                    'product_id' => $palletItem->product_id,
                    'pallet_item_id' => $line['pallet_item_id'],
                    'quantity_ordered' => $line['quantity'],
                    // 'quality_id' => $palletItem->quality_id, // Opcional
                ]);
            }

            DB::commit();
            return redirect()->route('wms.sales-orders.show', $salesOrder)->with('success', 'Orden de Venta actualizada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error("Error al actualizar SO {$salesOrder->id}: " . $e->getMessage()); // Opcional: Registrar error
            return back()->with('error', 'Error al actualizar la orden: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Cancela una Orden de Venta (cambia estado y libera inventario comprometido).
     */
    public function cancel(SalesOrder $salesOrder)
    {
        // Solo permite cancelar órdenes pendientes
        if ($salesOrder->status !== 'Pending') {
            return back()->with('error', 'No se puede cancelar una orden que ya está en proceso de surtido.');
        }

        DB::beginTransaction();
        try {
            // Revertir el stock comprometido (desde PalletItem)
            foreach ($salesOrder->lines()->with('palletItem')->get() as $line) {
                if ($line->palletItem) {
                    // Usamos max(0, ...) para seguridad
                    $newCommitted = max(0, $line->palletItem->committed_quantity - $line->quantity_ordered);
                    $line->palletItem->update(['committed_quantity' => $newCommitted]);
                    // $line->palletItem->decrement('committed_quantity', $line->quantity_ordered); // Alternativa
                }
            }

            // Actualizar el estado de la orden a 'Cancelled'
            $salesOrder->status = 'Cancelled';
            $salesOrder->save();

            DB::commit();
            return redirect()->route('wms.sales-orders.index')->with('success', 'La Orden de Venta ha sido cancelada y el inventario ha sido liberado.');

        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error("Error al cancelar SO {$salesOrder->id}: " . $e->getMessage()); // Opcional: Registrar error
            return back()->with('error', 'Error al cancelar la orden: ' . $e->getMessage());
        }
    }

    public function exportCsv(Request $request)
    {
        $fileName = 'reporte_ordenes_venta_' . now()->format('Ymd_His') . '.csv';

        // Encabezados HTTP para la descarga del CSV
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // Definimos el callback que generará el contenido del CSV
        $callback = function() use ($request) {
            $file = fopen('php://output', 'w');
            
            // Añadir BOM para compatibilidad con Excel y UTF-8 (acentos)
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezados del CSV (lo más completo posible)
            fputcsv($file, [
                'ID Orden',
                'N° Orden (SO)',
                'N° Factura',
                'Cliente',
                'Fecha Orden/Entrega',
                'Estatus Orden',
                'Creado Por',
                'Fecha Creación Orden',
                // --- Datos de la Línea ---
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
                // --- Datos de Picking (si existe) ---
                'Picklist ID',
                'Estatus Picking',
                'Fecha Picking',
                'Picker',
            ]);

            // Replicar la lógica de consulta y filtros del método index
            $query = SalesOrder::query()
                ->with([ // Eager load para eficiencia
                    'user', // Creador de la SO
                    'lines.product', // Producto de la línea
                    'lines.palletItem.pallet.location', // LPN, Ubicación Origen
                    'lines.palletItem.pallet.purchaseOrder', // PO y Pedimento del LPN Origen
                    'lines.palletItem.quality', // Calidad del Lote
                    'pickList.picker' // Picklist y quién la completó
                ]);

            // Aplicar filtros del request (igual que en index)
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

            // Procesar en lotes (chunks) para no agotar memoria con muchos datos
            $query->orderBy('created_at', 'desc')->chunk(200, function ($salesOrders) use ($file) {
                foreach ($salesOrders as $so) {
                    // Si la orden no tiene líneas, escribir una fila solo con datos de cabecera
                    if ($so->lines->isEmpty()) {
                        fputcsv($file, [
                            $so->id,
                            $so->so_number,
                            $so->invoice_number ?? '',
                            $so->customer_name,
                            $so->order_date ? $so->order_date->format('Y-m-d') : '',
                            $so->status,
                            $so->user->name ?? 'N/A',
                            $so->created_at ? $so->created_at->format('Y-m-d H:i') : '',
                            // Datos de línea vacíos
                            '', '', '', '', '', '', '', '', '', '',
                            // Datos de picking vacíos
                             $so->pickList->id ?? '',
                             $so->pickList->status ?? '',
                             $so->pickList->picked_at ? $so->pickList->picked_at->format('Y-m-d H:i') : '',
                             $so->pickList->picker->name ?? '',
                        ]);
                    } else {
                        // Por cada línea de la orden, escribir una fila en el CSV
                        foreach ($so->lines as $line) {
                            $location = $line->palletItem->pallet->location ?? null;
                            $locationCode = $location->code ?? '';
                            $locationPhysical = $location ? "{$location->aisle}-{$location->rack}-{$location->shelf}-{$location->bin}" : '';
                            
                            fputcsv($file, [
                                // Datos de Cabecera (se repiten por cada línea)
                                $so->id,
                                $so->so_number,
                                $so->invoice_number ?? '',
                                $so->customer_name,
                                $so->order_date ? $so->order_date->format('Y-m-d') : '',
                                $so->status,
                                $so->user->name ?? 'N/A',
                                $so->created_at ? $so->created_at->format('Y-m-d H:i') : '',
                                // Datos de la Línea
                                $line->id,
                                $line->product->sku ?? 'N/A',
                                $line->product->name ?? 'N/A',
                                $line->quantity_ordered,
                                $line->palletItem->pallet->lpn ?? 'N/A',
                                $locationCode,
                                $locationPhysical,
                                $line->palletItem->quality->name ?? 'N/A',
                                $line->palletItem->pallet->purchaseOrder->po_number ?? 'N/A',
                                $line->palletItem->pallet->purchaseOrder->pedimento_a4 ?? 'N/A',
                                // Datos de Picking
                                $so->pickList->id ?? '',
                                $so->pickList->status ?? '',
                                $so->pickList->picked_at ? $so->pickList->picked_at->format('Y-m-d H:i') : '',
                                $so->pickList->picker->name ?? '',
                            ]);
                        }
                    }
                }
            });

            fclose($file);
        };

        // Retornar la respuesta para iniciar la descarga
        return new StreamedResponse($callback, 200, $headers);
    }

}