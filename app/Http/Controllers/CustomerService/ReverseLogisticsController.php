<?php

namespace App\Http\Controllers\CustomerService;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CsOrder;
use App\Models\CsWarehouse;
use App\Models\CsCreditNote;
use App\Models\CsOrderEvent;
use Illuminate\Support\Facades\Auth;

class ReverseLogisticsController extends Controller
{
    public function create(CsOrder $order)
    {
        $order->load('details.product');
        $warehouses = CsWarehouse::all();

        $requestTypes = ['Cancelación', 'Rechazo', 'Recolección', 'Devolución'];
        $causes = [
            "Administración de CS", "Administración de ejecutivo MHMX", "Calidad",
            "Incidencias IT", "Movimiento Virtual", "No entregado picking",
            "No entregado transporte", "Operación del cliente", "Siniestros"
        ];

        return view('customer-service.reverse-logistics.create', compact('order', 'warehouses', 'requestTypes', 'causes'));
    }

    public function store(Request $request, CsOrder $order)
        {
        $validatedData = $request->validate([
            'request_type' => 'required|string|in:Cancelación,Rechazo,Recolección,Devolución',
            'capture_date' => 'required|date',
            'sku_details' => 'required|array',
            'sku_details.*.quantity_returned' => 'required|integer|min:1',
            'sku_details.*' => 'required|array',
            'warehouse_id' => 'required|exists:cs_warehouses,id',
            'customs_document' => 'required|string',
            'cause' => 'required|string|in:Administración de CS,Administración de ejecutivo MHMX,Calidad,Incidencias IT,Movimiento Virtual,No entregado picking,No entregado transporte,Operación del cliente,Siniestros',
            'cause_desc' => 'required|string',
            'credit_note_date' => 'nullable|date',
            'credit_note' => 'nullable|string',
            'arrival_date' => 'nullable|date',
            'asn_close_date' => 'nullable|date',
            'asn' => 'nullable|string',
            'observations' => 'nullable|string',
        ]);
        
        foreach ($request->input('sku_details') as $orderDetailId => $detailData) {
            $originalQuantity = $order->details()->findOrFail($orderDetailId)->quantity;
            if ($detailData['quantity_returned'] > $originalQuantity) {
                return back()->withErrors(['sku_details.' . $orderDetailId . '.quantity_returned' => 'La cantidad a devolver no puede ser mayor a la cantidad original.'])->withInput();
            }
        }
        
        $validatedData['customs_document'] = preg_replace('/[\s\-\._]/', '', $validatedData['customs_document']);
        
        $creditNote = CsCreditNote::create([
            'cs_order_id' => $order->id,
            'request_type' => $validatedData['request_type'],
            'capture_date' => $validatedData['capture_date'],
            'customer_name' => $order->customer_name,
            'invoice' => $order->invoice_number,
            'warehouse_id' => $validatedData['warehouse_id'],
            'customs_document' => $validatedData['customs_document'],
            'cause' => $validatedData['cause'],
            'cause_description' => $validatedData['cause_desc'],
            'credit_note_date' => $validatedData['credit_note_date'],
            'credit_note' => $validatedData['credit_note'],
            'arrival_date' => $validatedData['arrival_date'],
            'asn_close_date' => $validatedData['asn_close_date'],
            'asn' => $validatedData['asn'],
            'observations' => $validatedData['observations'],
            'created_by_user_id' => Auth::id(),
        ]);

        foreach ($request->input('sku_details') as $orderDetailId => $detailData) {
            $orderDetail = $order->details()->findOrFail($orderDetailId);
            $creditNote->details()->create([
                'sku' => $orderDetail->sku,
                'quantity_returned' => $detailData['quantity_returned'],
            ]);
        }

        if ($validatedData['request_type'] === 'Cancelación') {
            $order->update(['status' => 'Cancelado']);
        }

        CsOrderEvent::create([
            'cs_order_id' => $order->id,
            'user_id' => Auth::id(),
            'description' => 'El usuario ' . Auth::user()->name . ' generó una Nota de Crédito por ' . $validatedData['request_type'] . '.'
        ]);

        return redirect()->route('customer-service.orders.show', $order)->with('success', 'Nota de crédito creada exitosamente.');
    }
}