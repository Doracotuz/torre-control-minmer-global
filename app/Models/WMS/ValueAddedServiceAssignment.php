<?php

namespace App\Models\WMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValueAddedServiceAssignment extends Model
{
    use HasFactory;

    protected $table = 'wms_value_added_service_assignments';

    protected $fillable = [
        'value_added_service_id',
        'assignable_id',
        'assignable_type',
        'quantity',
        'cost_snapshot',
    ];

    public function service()
    {
        return $this->belongsTo(ValueAddedService::class, 'value_added_service_id');
    }

    public function assignable()
    {
        return $this->morphTo();
    }
}
