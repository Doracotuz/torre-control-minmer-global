<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsPlanning extends Model
{
    use HasFactory;

    protected $table = 'cs_plannings';
    protected $guarded = [];

    protected $casts = [
        'fecha_carga' => 'date',
        'fecha_entrega' => 'date',
        'is_scale' => 'boolean',
        'is_direct_route' => 'boolean',
        'maniobras' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(CsOrder::class, 'cs_order_id');
    }

    public function guia()
    {
        return $this->belongsTo(\App\Models\Guia::class, 'guia_id');
    }

    public function events()
    {
        return $this->hasMany(CsPlanningEvent::class, 'cs_planning_id')->orderBy('created_at', 'desc');
    }

}