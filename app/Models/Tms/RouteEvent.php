<?php

namespace App\Models\Tms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouteEvent extends Model
{
    use HasFactory;
    protected $table = 'tms_route_events';
    protected $fillable = ['route_id', 'event_type', 'latitude', 'longitude', 'notes'];

    // Un evento pertenece a una ruta
    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    // Un evento puede tener muchas fotos (polimórfico)
    public function media()
    {
        return $this->morphMany(Media::class, 'model');
    }
}
