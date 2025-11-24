<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ffProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'description',
        'unit_price',
        'brand',
        'type',
        'pieces_per_box',
        'length',
        'width',
        'height',
        'upc',
        'photo_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'unit_price' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'pieces_per_box' => 'integer',
    ];

    protected $appends = ['photo_url'];

    public function getPhotoUrlAttribute()
    {
        if ($this->photo_path) {
            return Storage::disk('s3')->url($this->photo_path);
        }
        
        return 'https://placehold.co/100x100/e0e0e0/909090?text=' . $this->sku;
    }

    protected static function booted(): void
    {
        static::deleted(function (ffProduct $product) {
            if ($product->photo_path) {
                Storage::disk('s3')->delete($product->photo_path);
            }
        });
    }

    public function movements()
    {
        return $this->hasMany(ffInventoryMovement::class);
    }

    public function cartItems()
    {
        return $this->hasMany(ffCartItem::class, 'ff_product_id');
    }    
}