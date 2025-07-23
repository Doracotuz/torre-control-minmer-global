<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parada extends Model
{
    use HasFactory;

    protected $fillable = [
        'ruta_id',
        'secuencia',
        'nombre_lugar',
        'latitud',
        'longitud',
        'distancia_a_siguiente_km',
    ];

    public function ruta()
    {
        return $this->belongsTo(Ruta::class);
    }
}