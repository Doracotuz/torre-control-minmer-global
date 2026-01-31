<?php

namespace App\Models\WMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Area;

class Quality extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'description',
        'area_id'
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}