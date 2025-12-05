<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FfClient extends Model
{
    protected $table = 'ff_clients';
    protected $fillable = ['name', 'is_active'];

    public function branches()
    {
        return $this->hasMany(FfClientBranch::class, 'ff_client_id');
    }

    public function deliveryConditions()
    {
        return $this->hasOne(FfClientDeliveryCondition::class, 'ff_client_id');
    }
}