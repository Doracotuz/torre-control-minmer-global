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

    /**
     * El usuario que realizó el movimiento.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * El producto afectado.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * La ubicación afectada.
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * El item de LPN afectado (si aplica).
     */
    public function palletItem()
    {
        return $this->belongsTo(PalletItem::class);
    }

    /**
     * El documento que originó el movimiento (PickListItem, InventoryAdjustment, etc.)
     */
    public function source()
    {
        return $this->morphTo();
    }
}