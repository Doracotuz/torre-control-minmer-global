<?php

namespace App\Models\WMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\Location;

class PhysicalCountTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'physical_count_session_id',
        'product_id',
        'location_id',
        'expected_quantity',
        'status',
        'pallet_id',
    ];

    public function physicalCountSession()
    {
        return $this->belongsTo(PhysicalCountSession::class);
    }    

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function records()
    {
        return $this->hasMany(PhysicalCountRecord::class);
    }

    public function getStatusInSpanishAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pendiente',
            'discrepancy' => 'Discrepancia',
            'resolved' => 'Resuelto',
            default => ucfirst($this->status),
        };
    }

    public function pallet()
    {
        return $this->belongsTo(\App\Models\WMS\Pallet::class);
    }    

}