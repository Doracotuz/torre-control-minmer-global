<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsBrand extends Model
{
    use HasFactory;
    
    protected $table = 'cs_brands';

    protected $fillable = ['name'];

    public function products()
    {
        return $this->hasMany(CsProduct::class, 'cs_brand_id');
    }
}
