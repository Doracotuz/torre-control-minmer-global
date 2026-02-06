<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncNotification extends Model
{
    protected $fillable = ['type', 'message', 'payload', 'resolved'];

    protected $casts = [
        'payload' => 'array',
        'resolved' => 'boolean',
    ];
}
