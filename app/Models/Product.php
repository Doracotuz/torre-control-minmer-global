<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku', 'name', 'description', 'brand_id', 'product_type_id',
        'unit_of_measure', 'length', 'width', 'height', 'weight', 'upc','pieces_per_case'
    ];

    public function getVolumeAttribute()
    {
        if ($this->length > 0 && $this->width > 0 && $this->height > 0) {
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