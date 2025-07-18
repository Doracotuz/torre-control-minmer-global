<?php

namespace App\Models\Tms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;
    protected $table = 'tms_shipments';
    protected $fillable = [
        'route_id', 'type', 'guide_number', 'so_number', 'pedimento', 'origin',
        'destination_type', 'destination_address', 'operator', 'license_plate', 'status'
    ];

    // Un embarque (guia) tiene muchas facturas
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // Un embarque pertenece a una ruta
    public function route()
    {
        return $this->belongsTo(Route::class);
    }
}
