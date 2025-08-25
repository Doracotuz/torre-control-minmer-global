<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsOrder extends Model
{
    use HasFactory;

    protected $table = 'cs_orders';
    protected $guarded = []; // Usar guarded en lugar de fillable para simplicidad

    // --- INICIA CORRECCIÓN: Se añade el casting de fechas ---
    protected $casts = [
        'creation_date' => 'date',
        'authorization_date' => 'date',
        'invoice_date' => 'date',
        'delivery_date' => 'date',
        'evidence_reception_date' => 'date',
        'evidence_cutoff_date' => 'date',
    ];
    // --- TERMINA CORRECCIÓN ---

    public function details() { return $this->hasMany(CsOrderDetail::class); }
    public function plan() { return $this->hasOne(CsPlan::class); }
    public function plannings()
    {
        return $this->hasMany(CsPlanning::class, 'cs_order_id');
    }    
    public function events() { return $this->hasMany(CsOrderEvent::class)->orderBy('created_at', 'desc'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function planningEvents()
    {
        return $this->hasManyThrough(
            \App\Models\CsPlanningEvent::class,
            \App\Models\CsPlanning::class,
            'cs_order_id', // Clave foránea en la tabla cs_plannings
            'cs_planning_id', // Clave foránea en la tabla cs_planning_events
            'id', // Clave local en la tabla cs_orders
            'id' // Clave local en la tabla cs_plannings
        );
    }

    /**
     * Obtiene los eventos de la guía (Asignación) a través de su registro de planificación.
     * Una orden TIENE MUCHOS eventos de guía A TRAVÉS DE su planificación.
     */
    public function guiaEvents()
    {
        // Esta relación es más compleja, por lo que la construiremos paso a paso.
        // Primero, obtenemos el ID de la guía asociada a la planificación de esta orden.
        $guiaId = $this->plan()->first()->guia_id ?? null;

        if ($guiaId) {
            // Si existe una guía, devolvemos sus eventos.
            return \App\Models\Evento::where('guia_id', $guiaId)->orderBy('fecha_evento', 'desc');
        }

        // Si no hay guía, devolvemos una relación vacía.
        return \App\Models\Evento::where('guia_id', -1); // ID заведомо неверный
    }    
}
