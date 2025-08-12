<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsProduct extends Model
{
    use HasFactory;

    protected $table = 'cs_products';

    protected $fillable = [
        'sku',
        'description',
        'packaging_factor',
        'cs_brand_id',
        'type',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    public function brand()
    {
        return $this->belongsTo(CsBrand::class, 'cs_brand_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
