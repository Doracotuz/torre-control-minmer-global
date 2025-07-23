<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruta extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'region',
        'tipo_ruta',
        'distancia_total_km',
        'area_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function paradas()
    {
        return $this->hasMany(Parada::class)->orderBy('secuencia');
    }

    public function guias()
    {
        return $this->hasMany(Guia::class);
    }
}