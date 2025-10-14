<?php

namespace App\Models\WMS;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PalletItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pallet_id',
        'product_id',
        'quantity',
        'quality_id',
    ];

    /**
     * An item belongs to a Pallet.
     */
    public function pallet()
    {
        return $this->belongsTo(Pallet::class);
    }

    /**
     * An item refers to a Product.
     */
    public function product()
    {
        // Use the fully qualified class name for clarity
        return $this->belongsTo(\App\Models\Product::class);
    }

    /**
     * An item has a Quality status.
     */
    public function quality()
    {
        // Use the fully qualified class name for clarity
        return $this->belongsTo(\App\Models\WMS\Quality::class);
    }
}