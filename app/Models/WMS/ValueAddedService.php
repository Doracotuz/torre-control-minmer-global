<?php

namespace App\Models\WMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValueAddedService extends Model
{
    use HasFactory;

    protected $table = 'wms_value_added_services';

    protected $fillable = [
        'code',
        'description',
        'type', // consumable, service
        'cost',
    ];

    public function assignments()
    {
        return $this->hasMany(ValueAddedServiceAssignment::class);
    }
}
