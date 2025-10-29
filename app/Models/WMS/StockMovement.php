<?php

namespace App\Models\WMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Product;
use App\Models\Location;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'location_id',
        'pallet_item_id',
        'quantity',
        'movement_type',
        'source_id',
        'source_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function palletItem()
    {
        return $this->belongsTo(PalletItem::class);
    }

    public function source()
    {
        return $this->morphTo();
    }
}