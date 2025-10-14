<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'warehouse_id',
        'code',
        'aisle',
        'rack',
        'shelf',
        'bin',
        'type',
        'pick_sequence',
    ];

    /**
     * Una ubicación pertenece a un almacén.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}