<?php

namespace App\Observers;

use App\Models\CsPlanning;
use App\Models\Factura;
use App\Models\Guia;

class CsPlanningObserver
{
    /**
     * Handle the CsPlanning "created" event.
     */
    public function created(CsPlanning $csPlanning): void
    {
        //
    }

    /**
     * Handle the CsPlanning "updated" event.
     */
    public function updated(CsPlanning $csPlanning): void
    {
        // SIN 'wasChanged'. Se ejecuta siempre que se actualice Planificación manualmente.
        
        if ($csPlanning->guia_id) {
            $guia = \App\Models\Guia::find($csPlanning->guia_id);
            
            if ($guia) {
                // 1. Sincroniza los datos de la Guía
                $guia->update([
                    'custodia'         => $csPlanning->custodia,
                    'hora_planeada'    => $csPlanning->hora_carga,
                    'origen'           => $csPlanning->origen,
                    'fecha_asignacion' => $csPlanning->fecha_carga,
                ]);

                // 2. Sincroniza los datos de la Factura
                $facturaEnGuia = $guia->facturas()->where('cs_planning_id', $csPlanning->id)->first();
                
                if ($facturaEnGuia) {
                    $facturaEnGuia->update([
                        'numero_factura' => $csPlanning->factura,
                        'destino'        => $csPlanning->razon_social,
                        // Aquí puedes añadir otros campos que deban sincronizarse desde Planificación
                    ]);
                }
            }
        }
    }

    /**
     * Handle the CsPlanning "deleted" event.
     */
    public function deleted(CsPlanning $csPlanning): void
    {
        //
    }

    /**
     * Handle the CsPlanning "restored" event.
     */
    public function restored(CsPlanning $csPlanning): void
    {
        //
    }

    /**
     * Handle the CsPlanning "force deleted" event.
     */
    public function forceDeleted(CsPlanning $csPlanning): void
    {
        //
    }
}
