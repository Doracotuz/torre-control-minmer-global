<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsOrder extends Model
{
    use HasFactory;

    protected $table = 'cs_orders';
    protected $guarded = [];

    protected $casts = [
        'creation_date' => 'date',
        'authorization_date' => 'date',
        'invoice_date' => 'date',
        'delivery_date' => 'date',
        'evidence_reception_date' => 'date',
        'evidence_cutoff_date' => 'date',
        'is_oversized' => 'boolean',
    ];

    public function details() { return $this->hasMany(CsOrderDetail::class); }
    public function plan() { return $this->hasOne(CsPlan::class); }
    public function plannings()
    {
        return $this->hasMany(CsPlanning::class, 'cs_order_id');
    }    
    public function events() { return $this->hasMany(CsOrderEvent::class)->orderBy('created_at', 'desc'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by_user_id'); }

    public function facturas()
    {
        return $this->hasMany(Factura::class, 'so', 'so_number');
    }

    public function planningEvents()
    {
        return $this->hasManyThrough(
            \App\Models\CsPlanningEvent::class,
            \App\Models\CsPlanning::class,
            'cs_order_id', 
            'cs_planning_id',
            'id',
            'id'
        );
    }

    public function guiaEvents()
    {
        $guiaId = $this->plan()->first()->guia_id ?? null;

        if ($guiaId) {
            return \App\Models\Evento::where('guia_id', $guiaId)->orderBy('fecha_evento', 'desc');
        }

        return \App\Models\Evento::where('guia_id', -1);
    }    

    public function customer()
    {
        return $this->belongsTo(\App\Models\CsCustomer::class, 'customer_name', 'name');
    }

    public function audits()
    {
        return $this->hasMany(\App\Models\Audit::class, 'cs_order_id');
    }   
    
    public function evidences()
    {
        return $this->hasMany(CsOrderEvidence::class, 'cs_order_id');
    }

    public function creator()
    {

        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }    

}