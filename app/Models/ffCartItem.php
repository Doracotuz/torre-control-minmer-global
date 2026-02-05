<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ffCartItem extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'ff_product_id', 'quantity', 'ff_warehouse_id', 'ff_quality_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(ffProduct::class, 'ff_product_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(FfWarehouse::class, 'ff_warehouse_id');
    }

}