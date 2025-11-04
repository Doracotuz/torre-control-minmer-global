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
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Log;

class WMSSalesOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesOrder::with(['user'])
            ->withCount('lines') 
            ->withSum('lines', 'quantity_ordered');

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
            $query->whereDate('order_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('order_date', '<=', $request->end_date);
        }        

        $salesOrders = $query->latest()->paginate(15)->withQueryString();

        $kpiQuery = SalesOrder::query();
        
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

        return view('wms.sales-orders.index', compact('salesOrders', 'kpis'));
    }

    public function create()
    {
        $availableQuality = \App\Models\WMS\Quality::where('name', 'Disponible')->first();
        $availableQualityId = $availableQuality ? $availableQuality->id : -1;

        $restrictedLocationTypes = ['Receiving', 'Quality Control', 'Shipping'];
        $restrictedLocationIds = \App\Models\Location::whereIn('type', $restrictedLocationTypes)->pluck('id');

        $stockData = \App\Models\WMS\PalletItem::query()
            ->whereRaw('quantity > committed_quantity')
            ->with([
                'product:id,sku,name',
                'quality:id,name',
                'pallet.purchaseOrder:id,po_number,pedimento_a4',
                'pallet.location:id,code,type'
            ])
            ->get()
            ->map(function ($item) use ($availableQualityId, $restrictedLocationIds) {
                
                $item->is_available = true;
                $item->warning_message = null;

                if ($item->quality_id != $availableQualityId) {
                    $item->is_available = false;
                    $item->warning_message = "Calidad No Disponible: " . ($item->quality->name ?? 'N/A');
                } 
                elseif ($restrictedLocationIds->contains($item->pallet->location_id)) {
                    $item->is_available = false;
                    $item->warning_message = "Ubicación No Válida: " . ($item->pallet->location->type ?? 'N/A');
                }

                return $item;
            });
            
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
                'status' => 'Pending',
            ]);

            foreach ($validated['lines'] as $line) {
                $palletItem = PalletItem::with('pallet', 'product')->findOrFail($line['pallet_item_id']);

                $availableQuantityInPallet = $palletItem->quantity - $palletItem->committed_quantity;

                if ($availableQuantityInPallet < $line['quantity']) {
                    throw new \Exception("La cantidad ({$line['quantity']}) para el SKU {$palletItem->product->sku} en el LPN {$palletItem->pallet->lpn} excede el stock disponible ({$availableQuantityInPallet}) en ese lote.");
                }
                
                $palletItem->increment('committed_quantity', $line['quantity']);

                $salesOrder->lines()->create([
                    'product_id' => $palletItem->product_id,
                    'pallet_item_id' => $line['pallet_item_id'],
                    'quantity_ordered' => $line['quantity'],
                    // 'quality_id' => $palletItem->quality_id, // Podrías guardar quality_id aquí también si lo necesitas
                ]);
            }

            DB::commit();
            return redirect()->route('wms.sales-orders.index')->with('success', 'Orden de Venta creada e inventario comprometido exitosamente.');

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
            'lines.product',
            'lines.palletItem.pallet.location',
            'lines.palletItem.pallet.purchaseOrder',
            'lines.palletItem.quality',
            'pickList'
        ]);

        return view('wms.sales-orders.show', compact('salesOrder'));
    }

    public function edit(SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== 'Pending') {
            return redirect()->route('wms.sales-orders.show', $salesOrder)->with('error', 'No se puede editar una orden que ya está en proceso de surtido.');
        }

        $salesOrder->load(
            'lines.palletItem.product',
            'lines.palletItem.pallet.purchaseOrder',
            'lines.palletItem.quality'
        );
        
        $existingItemIds = $salesOrder->lines->pluck('pallet_item_id')->filter()->unique();

        $stockData = PalletItem::query()
            ->where('quantity', '>', 0)
            ->orWhereIn('id', $existingItemIds)
            ->with([
                'product', 
                'quality', 
                'pallet.purchaseOrder'
            ])
            ->get();
            
        return view('wms.sales-orders.edit', compact('salesOrder', 'stockData'));
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
            'lines' => 'required|array|min:1',
            'lines.*.pallet_item_id' => 'required|exists:pallet_items,id',
            'lines.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            foreach ($salesOrder->lines()->with('palletItem')->get() as $oldLine) {
                if ($oldLine->palletItem) {
                    $newCommitted = max(0, $oldLine->palletItem->committed_quantity - $oldLine->quantity_ordered);
                    $oldLine->palletItem->update(['committed_quantity' => $newCommitted]);
                }
            }

            $salesOrder->lines()->delete();

            $salesOrder->update([
                'so_number' => $validated['so_number'],
                'invoice_number' => $validated['invoice_number'],
                'customer_name' => $validated['customer_name'],
                'order_date' => $validated['delivery_date'],
            ]);

            foreach ($validated['lines'] as $line) {
                $palletItem = PalletItem::with('pallet', 'product')->findOrFail($line['pallet_item_id']);
                
                $availableQuantityInPallet = $palletItem->quantity - $palletItem->committed_quantity;

                if ($availableQuantityInPallet < $line['quantity']) {
                    throw new \Exception("La cantidad ({$line['quantity']}) para el SKU {$palletItem->product->sku} en el LPN {$palletItem->pallet->lpn} excede el stock disponible ({$availableQuantityInPallet}) en ese lote.");
                }
                
                $palletItem->increment('committed_quantity', $line['quantity']);

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
            foreach ($salesOrder->lines()->with('palletItem')->get() as $line) {
                if ($line->palletItem) {
                    $newCommitted = max(0, $line->palletItem->committed_quantity - $line->quantity_ordered);
                    $line->palletItem->update(['committed_quantity' => $newCommitted]);
                    // $line->palletItem->decrement('committed_quantity', $line->quantity_ordered); // Alternativa
                }
            }

            $salesOrder->status = 'Cancelled';
            $salesOrder->save();

            DB::commit();
            return redirect()->route('wms.sales-orders.index')->with('success', 'La Orden de Venta ha sido cancelada y el inventario ha sido liberado.');

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
                    'lines.product',
                    'lines.palletItem.pallet.location',
                    'lines.palletItem.pallet.purchaseOrder',
                    'lines.palletItem.quality',
                    'pickList.picker'
                ]);

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
                            '', '', '', '', '', '', '', '', '', '',
                            $so->pickList->id ?? '',
                            $so->pickList->status ?? '',
                            $so->pickList->picked_at ? $so->pickList->picked_at->format('Y-m-d H:i') : '',
                            $so->pickList->picker->name ?? '',
                        ]);
                    } else {
                        foreach ($so->lines as $line) {
                            $location = $line->palletItem->pallet->location ?? null;
                            $locationCode = $location->code ?? '';
                            $locationPhysical = $location ? "{$location->aisle}-{$location->rack}-{$location->shelf}-{$location->bin}" : '';
                            
                            fputcsv($file, [
                                $so->id,
                                $so->so_number,
                                $so->invoice_number ?? '',
                                $so->customer_name,
                                $so->order_date ? $so->order_date->format('Y-m-d') : '',
                                $so->status,
                                $so->user->name ?? 'N/A',
                                $so->created_at ? $so->created_at->format('Y-m-d H:i') : '',
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

        return new StreamedResponse($callback, 200, $headers);
    }

}