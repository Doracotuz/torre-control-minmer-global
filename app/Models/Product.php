<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// Quitamos 'Attribute' porque ya no lo usaremos en esta sintaxis
// use Illuminate\Database\Eloquent\Casts\Attribute; 

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku', 'name', 'description', 'brand_id', 'product_type_id',
        'unit_of_measure', 'length', 'width', 'height', 'weight', 'upc'
    ];

    // --- INICIO: CÁLCULO DE VOLUMEN CON SINTAXIS CLÁSICA ---
    /**
     * Calcula el volumen automáticamente a partir de las dimensiones.
     * El resultado está en metros cúbicos (m³).
     */
    public function getVolumeAttribute()
    {
        if ($this->length > 0 && $this->width > 0 && $this->height > 0) {
            // Convierte cm³ a m³ dividiendo por 1,000,000
            return ($this->length * $this->width * $this->height) / 1000000;
        }
        return 0;
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function productType()
    {
        return $this->belongsTo(ProductType::class);
    }
}