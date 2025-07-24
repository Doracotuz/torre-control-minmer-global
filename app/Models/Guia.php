<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guia extends Model
{
    use HasFactory;

    protected $fillable = [
        'guia',
        'ruta_id',
        'operador',
        'placas',
        'pedimento',
        'estatus',
        'fecha_inicio_ruta',
        'fecha_fin_ruta',
    ];

    protected $casts = [
        'fecha_inicio_ruta' => 'datetime',
        'fecha_fin_ruta' => 'datetime',
    ];

    public function ruta()
    {
        return $this->belongsTo(Ruta::class);
    }

    public function facturas()
    {
        return $this->hasMany(Factura::class);
    }

    public function eventos()
    {
        return $this->hasMany(Evento::class)->orderBy('fecha_evento');
    }

    // public function getRouteKeyName()
    // {
    //     return 'guia'; // Indica a Laravel que use la columna 'guia' para la resolución de rutas
    // }

}