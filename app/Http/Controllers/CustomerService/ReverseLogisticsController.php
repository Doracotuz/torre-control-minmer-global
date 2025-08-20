<?php

namespace App\Http\Controllers\CustomerService;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CsOrder;
use App\Models\CsWarehouse;
use App\Models\CsCreditNote; // Asegúrate de crear este modelo
use App\Models\CsOrderEvent;
use Illuminate\Support\Facades\Auth;

class ReverseLogisticsController extends Controller
{
    /**
     * Muestra el formulario para crear una nota de crédito.
     */
    public function create(CsOrder $order)
    {
        // Carga los detalles de la orden para el formulario
        $order->load('details.product');
        // Obtiene la lista de almacenes para el selector
        $warehouses = CsWarehouse::all();

        // Tipos de solicitud y causas para los selectores
        $requestTypes = ['Cancelación', 'Rechazo', 'Recolección', 'Devolución'];
        $causes = [
            "Administración de CS", "Administración de ejecutivo MHMX", "Calidad",
            "Incidencias IT", "Movimiento Virtual", "No entregado picking",
            "No entregado transporte", "Operación del cliente", "Siniestros"
        ];

        return view('customer-service.reverse-logistics.create', compact('order', 'warehouses', 'requestTypes', 'causes'));
    }

    /**
     * Almacena la nueva nota de crédito y realiza las acciones necesarias.
     */
        public function store(Request $request, CsOrder $order)
        {
            // Validación de datos
        $validatedData = $request->validate([
            'request_type' => 'required|string|in:Cancelación,Rechazo,Recolección,Devolución',
            'capture_date' => 'required|date',
            'sku_details' => 'required|array',
            'sku_details.*.quantity_returned' => 'required|integer|min:1',
            // Asegúrate de validar correctamente los IDs de los detalles de la orden
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
        
        // Validar que la cantidad a devolver no sea mayor a la del show
        foreach ($request->input('sku_details') as $orderDetailId => $detailData) {
            $originalQuantity = $order->details()->findOrFail($orderDetailId)->quantity;
            if ($detailData['quantity_returned'] > $originalQuantity) {
                return back()->withErrors(['sku_details.' . $orderDetailId . '.quantity_returned' => 'La cantidad a devolver no puede ser mayor a la cantidad original.'])->withInput();
            }
        }
        
        // Limpiar el campo pedimento
        $validatedData['customs_document'] = preg_replace('/[\s\-\._]/', '', $validatedData['customs_document']);
        
        // Crear la nota de crédito principal
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

        // Guardar los detalles del SKU asociados a la nota de crédito
        foreach ($request->input('sku_details') as $orderDetailId => $detailData) {
            $orderDetail = $order->details()->findOrFail($orderDetailId);
            $creditNote->details()->create([
                'sku' => $orderDetail->sku,
                'quantity_returned' => $detailData['quantity_returned'],
            ]);
        }

        // Cambiar el estado del pedido si la solicitud es 'Cancelación'
        if ($validatedData['request_type'] === 'Cancelación') {
            $order->update(['status' => 'Cancelado']);
        }

        // Crear evento en la línea de tiempo
        CsOrderEvent::create([
            'cs_order_id' => $order->id,
            'user_id' => Auth::id(),
            'description' => 'El usuario ' . Auth::user()->name . ' generó una Nota de Crédito por ' . $validatedData['request_type'] . '.'
        ]);

        return redirect()->route('customer-service.orders.show', $order)->with('success', 'Nota de crédito creada exitosamente.');
    }
}