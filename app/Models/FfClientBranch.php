<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FfClientBranch extends Model
{
    protected $table = 'ff_client_branches';
    
    protected $fillable = [
        'ff_client_id',
        'name',
        'address',
        'schedule',
        'phone',
        'is_active'
    ];

    public function client()
    {
        return $this->belongsTo(FfClient::class, 'ff_client_id');
    }
}