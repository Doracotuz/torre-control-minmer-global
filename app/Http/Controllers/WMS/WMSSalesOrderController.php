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
use App\Models\WMS\SalesOrderLine;
use Illuminate\Validation\Rule;

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
        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }
        if ($request->filled('customer_name')) {
            $query->where('customer_name', 'like', '%' . $request->customer_name . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $salesOrders = $query->latest()->paginate(15)->withQueryString();

        // Lógica para las tarjetas de KPI
        $kpiQuery = SalesOrder::query();
        
        // Si hay filtros aplicados, los KPIs también deben reflejarlos
        if ($request->filled('so_number')) { $kpiQuery->where('so_number', 'like', '%' . $request->so_number . '%'); }
        if ($request->filled('invoice_number')) { $kpiQuery->where('invoice_number', 'like', '%' . $request->invoice_number . '%'); }
        if ($request->filled('customer_name')) { $kpiQuery->where('customer_name', 'like', '%' . $request->customer_name . '%'); }
        if ($request->filled('status')) { $kpiQuery->where('status', $request->status); }

        // Clonamos la consulta para obtener los diferentes conteos
        $kpis = [
            'total' => (clone $kpiQuery)->count(),
            'pending' => (clone $kpiQuery)->where('status', 'Pending')->count(),
            'picking' => (clone $kpiQuery)->where('status', 'Picking')->count(),
            'packed' => (clone $kpiQuery)->where('status', 'Packed')->count(),
        ];

        return view('wms.sales-orders.index', compact('salesOrders', 'kpis'));
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
                    'pallet_item_id' => $line['pallet_item_id'],
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
        $salesOrder->load([
            'user', 
            'lines.product',
            'lines.palletItem.pallet.location', // Carga el pallet y su ubicación
            'lines.palletItem.pallet.purchaseOrder', // Carga la PO para el pedimento
            'lines.palletItem.quality' // Carga la calidad
        ]);

        return view('wms.sales-orders.show', compact('salesOrder'));
    }

    public function edit(SalesOrder $salesOrder)
    {
        // 1. Validar que la orden se pueda editar
        if ($salesOrder->status !== 'Pending') {
            return redirect()->route('wms.sales-orders.show', $salesOrder)->with('error', 'No se puede editar una orden que ya está en proceso de surtido.');
        }

        // 2. Cargar las relaciones de las LÍNEAS EXISTENTES
        $salesOrder->load(
            'lines.palletItem.product',
            'lines.palletItem.pallet.purchaseOrder',
            'lines.palletItem.quality'
        );
        
        // 3. Obtener los IDs de los lotes que esta orden YA está usando.
        $existingItemIds = $salesOrder->lines->pluck('pallet_item_id')->filter()->unique();

        // 4. Cargar el stock disponible (incluyendo los lotes de esta orden)
        $stockData = PalletItem::query()
            ->where('quantity', '>', 0)
            ->orWhereIn('id', $existingItemIds)
            ->with([
                'product', 
                'quality', 
                'pallet.purchaseOrder'
            ])
            ->get();
            
        // --- INICIO DE LA NUEVA DEPURACIÓN ---

        // 1. Obtener la primera línea (si existe)
        $linea = $salesOrder->lines->first();

        // 2. Obtener el ID del lote que la línea TIENE guardado
        $id_lote_guardado = $linea ? $linea->pallet_item_id : null;

        // 3. Obtener los IDs de los lotes que se mostrarán en el dropdown
        $ids_lotes_en_dropdown = $stockData->pluck('id')->toArray();

        // 4. Comprobar si el lote guardado está en la lista del dropdown
        $lote_existe_en_dropdown = $id_lote_guardado ? in_array($id_lote_guardado, $ids_lotes_en_dropdown) : false;

        // 5. Comprobar si las relaciones del stock se cargaron bien
        $primera_opcion_stock = $stockData->first();
        $relaciones_stock_cargadas = [
            'pallet_cargado' => $primera_opcion_stock ? ($primera_opcion_stock->pallet ? 'SI' : 'NO') : 'N/A',
            'calidad_cargada' => $primera_opcion_stock ? ($primera_opcion_stock->quality ? 'SI' : 'NO') : 'N/A',
            'producto_cargado' => $primera_opcion_stock ? ($primera_opcion_stock->product ? 'SI' : 'NO') : 'N/A',
            'po_cargada' => $primera_opcion_stock ? ($primera_opcion_stock->pallet?->purchaseOrder ? 'SI' : 'NO') : 'N/A',
        ];

        // dd([
        //     'ID_LOTE_GUARDADO_EN_LINEA' => $id_lote_guardado,
        //     'IDS_DE_LOTES_EN_DROPDOWN' => $ids_lotes_en_dropdown,
        //     '¿EL_LOTE_GUARDADO_ESTA_EN_EL_DROPDOWN?' => $lote_existe_en_dropdown ? 'SI' : '!!! NO, ESTE ES EL PROBLEMA !!!',
        //     '¿RELACIONES_DEL_DROPDOWN_CARGADAS?' => $relaciones_stock_cargadas
        // ]);

        // --- FIN DE LA DEPURACIÓN ---

        // Esta línea no se ejecutará por el dd
        return view('wms.sales-orders.edit', compact('salesOrder', 'stockData'));
    }

    /**
     * Actualiza una Orden de Venta en la base de datos.
     */
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
            'lines' => 'required|array|min:1',
            'lines.*.pallet_item_id' => 'required|exists:pallet_items,id',
            'lines.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // 1. Revertir todo el stock comprometido original de esta orden
            foreach ($salesOrder->lines as $oldLine) {
                $stock = InventoryStock::whereHas('location', function ($q) use ($oldLine) {
                    $q->where('id', $oldLine->palletItem->pallet->location_id);
                })->where('product_id', $oldLine->product_id)
                  ->where('quality_id', $oldLine->palletItem->quality_id)
                  ->first();
                
                if ($stock) {
                    $stock->decrement('committed_quantity', $oldLine->quantity_ordered);
                }
            }

            // 2. Borrar las líneas antiguas
            $salesOrder->lines()->delete();

            // 3. Actualizar la cabecera de la orden
            $salesOrder->update([
                'so_number' => $validated['so_number'],
                'invoice_number' => $validated['invoice_number'],
                'customer_name' => $validated['customer_name'],
                'order_date' => $validated['delivery_date'],
            ]);

            // 4. Crear las nuevas líneas y comprometer el nuevo stock
            foreach ($validated['lines'] as $line) {
                $palletItem = PalletItem::with('pallet')->findOrFail($line['pallet_item_id']);
                $stock = InventoryStock::where('product_id', $palletItem->product_id)
                    ->where('location_id', $palletItem->pallet->location_id)
                    ->where('quality_id', $palletItem->quality_id)
                    ->first();

                if (!$stock || ($stock->quantity - $stock->committed_quantity) < $line['quantity']) {
                    throw new \Exception("La cantidad para el SKU {$palletItem->product->sku} excede el stock disponible.");
                }
                
                $stock->increment('committed_quantity', $line['quantity']);

                $salesOrder->lines()->create([
                    'product_id' => $palletItem->product_id,
                    'pallet_item_id' => $line['pallet_item_id'],
                    'quantity_ordered' => $line['quantity'],
                ]);
            }

            DB::commit();
            return redirect()->route('wms.sales-orders.show', $salesOrder)->with('success', 'Orden de Venta actualizada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar la orden: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Cancela una Orden de Venta.
     */
    public function cancel(SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== 'Pending') {
            return back()->with('error', 'No se puede cancelar una orden que ya está en proceso de surtido.');
        }

        DB::beginTransaction();
        try {
            // Revertir el stock comprometido
            foreach ($salesOrder->lines as $line) {
                // Aseguramos que palletItem y sus relaciones están cargadas
                $line->load('palletItem.pallet');

                if ($line->palletItem) {
                    $stock = InventoryStock::where('product_id', $line->product_id)
                        ->where('location_id', $line->palletItem->pallet->location_id)
                        ->where('quality_id', $line->palletItem->quality_id)
                        ->first();
                    
                    if ($stock) {
                        $stock->decrement('committed_quantity', $line->quantity_ordered);
                    }
                }
            }

            // Actualizar el estado de la orden
            $salesOrder->status = 'Cancelled';
            $salesOrder->save();

            DB::commit();
            return redirect()->route('wms.sales-orders.index')->with('success', 'La Orden de Venta ha sido cancelada y el inventario ha sido liberado.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al cancelar la orden: ' . $e->getMessage());
        }
    }

}