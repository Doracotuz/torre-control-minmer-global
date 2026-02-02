<?php

namespace App\Models\WMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Area;

class SalesOrder extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'so_number', 
        'invoice_number', 
        'customer_name', 
        'user_id',
        'area_id',
        'warehouse_id',
        'order_date', 
        'status', 
        'notes'
    ];

    protected $casts = [
        'order_date' => 'datetime',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }    

    public function lines()
    {
        return $this->hasMany(SalesOrderLine::class);
    }
    
    public function pickList()
    {
        return $this->hasOne(PickList::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}