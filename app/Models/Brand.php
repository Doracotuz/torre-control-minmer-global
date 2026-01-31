<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'area_id'
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
    
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}