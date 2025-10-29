<?php
namespace App\Models\WMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Product;
use App\Models\Location;

class InventoryAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'physical_count_task_id', 
        'pallet_item_id',
        'product_id', 
        'location_id', 
        'quantity_before',
        'quantity_after',
        'quantity_difference', 
        'reason', 
        'user_id', 
        'source',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function palletItem()
    {
        return $this->belongsTo(PalletItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}