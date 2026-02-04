<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToArea;

class FfQuality extends Model
{
    use BelongsToArea;

    protected $table = 'ff_qualities';

    protected $fillable = [
        'name', 
        'is_active', 
        'area_id'
    ];
}