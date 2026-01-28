<?php

namespace App\Models\WMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class PickList extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_order_id', 
        'user_id',
        'picker_id',
        'status',
        'picked_at'
    ];

    protected $casts = [
        'picked_at' => 'datetime',
    ];

    public function salesOrder() 
    { 
        return $this->belongsTo(SalesOrder::class); 
    }
    
    public function items() 
    { 
        return $this->hasMany(PickListItem::class); 
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function picker()
    {
        return $this->belongsTo(User::class, 'picker_id');
    }
}