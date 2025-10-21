<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\WMS\Pallet;

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

    public function pallets()
    {
        return $this->hasMany(Pallet::class);
    }

    public function getTranslatedTypeAttribute(): string
    {
        $types = [
            'storage' => 'Almacenamiento',
            'picking' => 'Picking',
            'receiving' => 'Recepción',
            'shipping' => 'Embarque',
            'quality_control' => 'Control de Calidad',
        ];
        return $types[$this->type] ?? ucfirst($this->type);
    }

}