<?php

namespace App\Observers;

use App\Models\CsPlanning;
use App\Models\Guia;

class CsPlanningObserver
{
    /**
     * Handle the CsPlanning "updated" event.
     */
    public function updated(CsPlanning $csPlanning): void
    {
        // SIN 'wasChanged' para que siempre se ejecute y garantice la sincronización.
        if ($csPlanning->guia_id) {
            $guia = Guia::find($csPlanning->guia_id);
            
            if ($guia) {
                // 1. Sincroniza los datos de la Guía principal
                $guia->update([
                    'custodia'         => $csPlanning->custodia,
                    'hora_planeada'    => $csPlanning->hora_carga,
                    'origen'           => $csPlanning->origen,
                    'fecha_asignacion' => $csPlanning->fecha_carga,
                ]);

                // 2. Lógica robusta para encontrar y actualizar la Factura
                $facturaEnGuia = $guia->facturas()->where('cs_planning_id', $csPlanning->id)->first();

                // Plan B: Si no se encontró por el ID, buscar por SO.
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