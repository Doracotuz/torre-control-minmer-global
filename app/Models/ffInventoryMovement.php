<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ffInventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'ff_product_id',
        'user_id',
        'quantity',
        'reason',
    ];

    public function product()
    {
        return $this->belongsTo(ffProduct::class, 'ff_product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}