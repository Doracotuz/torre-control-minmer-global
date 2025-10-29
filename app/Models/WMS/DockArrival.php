<?php

namespace App\Models\WMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\WMS\PurchaseOrder;

class DockArrival extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'dock_arrivals';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'purchase_order_id',
        'truck_plate',
        'driver_name',
        'arrival_time',
        'departure_time',
        'status',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'arrival_time' => 'datetime',
        'departure_time' => 'datetime',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}