<?php

namespace App\Models\WMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

use Illuminate\Database\Eloquent\SoftDeletes;

class Pallet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lpn',
        'location_id',
        'status',
        'purchase_order_id',
        'user_id',
        'last_action',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function location()
    {
        return $this->belongsTo(\App\Models\Location::class);
    }

    public function items()
    {
        return $this->hasMany(PalletItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }   
     
}