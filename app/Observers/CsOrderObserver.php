<?php

namespace App\Observers;

use App\Models\CsOrder;
use App\Models\CsPlanning;

class CsOrderObserver
{
    /**
     * Handle the CsOrder "created" event.
     */
    public function created(CsOrder $csOrder): void
    {
        //
    }

    /**
     * Handle the CsOrder "updated" event.
     */
    public function updated(CsOrder $csOrder): void
    {
        // Solo procedemos si un campo relevante del pedido cambió
        if ($csOrder->wasChanged()) {
            $planningRecords = \App\Models\CsPlanning::where('cs_order_id', $csOrder->id)->get();

            // Calculamos los valores correctos una sola vez
            $invoiceValue = $csOrder->invoice_number ?: $csOrder->so_number;
            $razonSocialValue = $csOrder->client_contact ?: $csOrder->customer_name;

            foreach ($planningRecords as $planningRecord) {
                
                // 1. ACTUALIZA PLANIFICACIÓN (Responsabilidad Primaria)
                // Se asegura de que Planificación siempre tenga los datos correctos.
                $planningRecord->update([
                    'factura' => $invoiceValue,
                    'razon_social' => $razonSocialValue,
                    'hora_cita' => $csOrder->schedule,
                    // Aquí puedes añadir otros campos que deban sincronizarse desde Pedidos
                    'fecha_entrega' => $csOrder->delivery_date,
                    'direccion' => $csOrder->shipping_address,
                    'destino' => $csOrder->destination_locality,
                ]);

                // 2. ACTUALIZA ASIGNACIÓN DIRECTAMENTE (Lógica Definitiva)
                // Si ya hay una guía, este observador toma el control total y actualiza la factura final.
                if ($planningRecord->guia_id) {
                    $facturaFinal = \App\Models\Factura::where('cs_planning_id', $planningRecord->id)
                                                      ->orWhere('so', $csOrder->so_number) // Búsqueda de respaldo
                                                      ->first();
                    
                    if ($facturaFinal) {
                        $facturaFinal->update([
                            'numero_factura' => $invoiceValue,
                            'destino' => $razonSocialValue, // También actualizamos el destino
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Handle the CsOrder "deleted" event.
     */
    public function deleted(CsOrder $csOrder): void
    {
        //
    }

    /**
     * Handle the CsOrder "restored" event.
     */
    public function restored(CsOrder $csOrder): void
    {
        //
    }

    /**
     * Handle the CsOrder "force deleted" event.
     */
    public function forceDeleted(CsOrder $csOrder): void
    {
        //
    }
}
