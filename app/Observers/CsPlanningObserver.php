<?php

namespace App\Observers;

use App\Models\CsPlanning;
use App\Models\Guia;
use App\Models\Audit; 

class CsPlanningObserver
{
    public function updated(CsPlanning $csPlanning): void
    {
        if ($csPlanning->wasChanged('guia_id') && !is_null($csPlanning->guia_id)) {
            if ($order = $csPlanning->order) {
                
                $audit = \App\Models\Audit::where('cs_order_id', $order->id)
                            ->where('location', $csPlanning->origen)
                            ->whereIn('status', ['Pendiente Almacén', 'Pendiente Patio'])
                            ->first();
                
                if ($audit) {
                    $audit->update(['guia_id' => $csPlanning->guia_id]);
                }
            }
        }

        if ($csPlanning->guia_id) {
            $guia = Guia::find($csPlanning->guia_id);
            
            if ($guia) {
                $guia->update([
                    'custodia'         => $csPlanning->custodia,
                    'hora_planeada'    => $csPlanning->hora_carga,
                    'origen'           => $csPlanning->origen,
                    'fecha_asignacion' => $csPlanning->fecha_carga,
                ]);

                $facturaEnGuia = $guia->facturas()->where('cs_planning_id', $csPlanning->id)->first();

                if (!$facturaEnGuia && $csPlanning->so_number) {
                    $facturaEnGuia = $guia->facturas()->where('so', $csPlanning->so_number)->first();
                }
                
                if ($facturaEnGuia) {
                    $facturaEnGuia->update([
                        'numero_factura' => $csPlanning->factura,
                        'destino'        => $csPlanning->razon_social,
                        'cajas'          => $csPlanning->cajas,
                        'botellas'       => $csPlanning->pzs,
                        'hora_cita'      => $csPlanning->hora_cita,
                        'so'             => $csPlanning->so_number,
                        'fecha_entrega'  => $csPlanning->fecha_entrega,
                    ]);
                }
            }
        }
    }
}