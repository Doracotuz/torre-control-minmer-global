<?php

namespace App\Models\WMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PalletItem extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'pallet_id',
        'product_id',
        'quantity',
    ];

    /**
     * Un item pertenece a una Tarima.
     */
    public function pallet()
    {
        return $this->belongsTo(Pallet::class);
    }

    /**
     * Un item se refiere a un Producto.
     */
    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }
}