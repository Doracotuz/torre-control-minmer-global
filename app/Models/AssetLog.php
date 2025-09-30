<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'hardware_asset_id',
        'user_id',
        'action_type',
        'notes',
        'loggable_id',
        'loggable_type',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function asset() { return $this->belongsTo(HardwareAsset::class, 'hardware_asset_id'); }
    public function loggable() { return $this->morphTo(); }
}