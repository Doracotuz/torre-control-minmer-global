<?php

namespace App\Models\Tms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stop extends Model
{
    use HasFactory;
    protected $table = 'tms_stops';
    protected $fillable = ['route_id', 'name', 'latitude', 'longitude', 'order'];

    // Una parada pertenece a una ruta
    public function route()
    {
        return $this->belongsTo(Route::class);
    }
}
