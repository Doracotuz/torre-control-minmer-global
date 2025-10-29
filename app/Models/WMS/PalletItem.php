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
        'committed_quantity',
        'quality_id',
    ];

    public function pallet()
    {
        return $this->belongsTo(Pallet::class);
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    public function quality()
    {
        return $this->belongsTo(\App\Models\WMS\Quality::class);
    }
}