<?php

namespace App\Models\WMS;

use App\Models\Location;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'location_id',
        'quantity',
        'quality_id',
    ];

    /**
     * A stock record refers to a Product.
     */
    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    /**
     * A stock record belongs to a Location.
     */
    public function location()
    {
        return $this->belongsTo(\App\Models\Location::class);
    }

    /**
     * A stock record has a Quality status.
     */
    public function quality()
    {
        return $this->belongsTo(\App\Models\WMS\Quality::class);
    }

    public function pallet()
    {
        return $this->belongsTo(Pallet::class);
    }

}