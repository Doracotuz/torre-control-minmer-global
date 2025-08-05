<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    use HasFactory;

    protected $fillable = [
        'guia_id',
        'numero_factura',
        'destino',
        'cajas',
        'botellas',
        'estatus_entrega',
        'hora_cita',
    ];

    public function guia()
    {
        return $this->belongsTo(Guia::class);
    }

    public function eventos()
    {
        return $this->hasMany(Evento::class);
    }

    public function evidenciasManiobra() {
        return $this->hasMany(\App\Models\FacturaEvidencia::class);
    }

}