<?php

namespace App\Models\WMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ForceDelete;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Area; // Cliente
use App\Models\Warehouse;
use App\Models\User;
use App\Models\WMS\ValueAddedServiceAssignment;

class ServiceRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'wms_service_requests';

    protected $fillable = [
        'folio',
        'area_id',
        'warehouse_id',
        'user_id',
        'status',
        'requested_at',
        'completed_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function valueAddedServices()
    {
        return $this->morphMany(ValueAddedServiceAssignment::class, 'assignable');
    }
}
