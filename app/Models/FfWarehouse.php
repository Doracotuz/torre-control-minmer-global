<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToArea;

class FfWarehouse extends Model
{
    use BelongsToArea;

    protected $table = 'ff_warehouses';

    protected $fillable = [
        'code',
        'description',
        'address',
        'phone',
        'area_id',
        'is_active'
    ];
}