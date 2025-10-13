<?php

namespace App\Models\WMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'location_id',
        'quantity',
    ];

    /**
     * Un registro de stock se refiere a un Producto.
     */
    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    /**
     * Un registro de stock pertenece a una Ubicación.
     */
    public function location()
    {
        return $this->belongsTo(\App\Models\Location::class);
    }
}