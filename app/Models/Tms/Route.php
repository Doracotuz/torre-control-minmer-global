<?php

namespace App\Models\Tms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;
    protected $table = 'tms_routes';
    protected $fillable = ['name', 'polyline', 'total_distance_km', 'total_duration_min', 'status'];
    
    // Una ruta tiene muchas paradas
    public function stops()
    {
        return $this->hasMany(Stop::class)->orderBy('order');
    }

    // Una ruta tiene muchos eventos
    public function events()
    {
        return $this->hasMany(RouteEvent::class);
    }

    // Una ruta tiene muchos embarques (guias)
    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
}