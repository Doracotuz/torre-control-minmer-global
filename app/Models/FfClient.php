<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToArea;

class FfClient extends Model
{
    use BelongsToArea;
    protected $table = 'ff_clients';
    protected $fillable = ['name', 'is_active', 'area_id'];

    public function branches()
    {
        return $this->hasMany(FfClientBranch::class, 'ff_client_id');
    }

    public function deliveryConditions()
    {
        return $this->hasOne(FfClientDeliveryCondition::class, 'ff_client_id');
    }

    protected static function booted(): void
    {
        static::deleting(function (FfClient $client) {
            if ($client->deliveryConditions) {
                $client->deliveryConditions->delete();
            }
            
            $client->branches()->delete();
        });
    }    
}