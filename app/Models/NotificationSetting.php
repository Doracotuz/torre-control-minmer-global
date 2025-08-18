<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_name',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}