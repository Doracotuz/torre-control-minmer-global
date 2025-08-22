<?php

namespace App\Observers;

use App\Models\CsOrder;
use App\Models\CsPlanning;

class CsOrderObserver
{
    /**
     * Handle the CsOrder "updated" event.
     */
    public function updated(CsOrder $csOrder): void
    {
        if ($csOrder->wasChanged()) {
            $planningRecords = CsPlanning::where('cs_order_id', $csOrder->id)->get();

            foreach ($planningRecords as $planningRecord) {
                // Preparamos los datos a actualizar en Planificación
                $dataToUpdate = [
                    'fecha_entrega' => $csOrder->delivery_date,
                    'origen' => $csOrder->origin_warehouse,
                    'direccion' => $csOrder->shipping_address,
                    'razon_social' => $csOrder->client_contact ?: $csOrder->customer_name,
                    'hora_cita' => $csOrder->schedule,
                    'factura' => $csOrder->invoice_number ?: $csOrder->so_number,
                    'pzs' => $csOrder->total_bottles,
                    'cajas' => $csOrder->total_boxes,
                    'subtotal' => $csOrder->subtotal,
                    'canal' => $csOrder->channel,
                    'destino' => $csOrder->destination_locality,
                ];

                // Actualizamos Planificación (esto activará el CsPlanningObserver)
                $planningRecord->update($dataToUpdate);
            }
        }
    }
}