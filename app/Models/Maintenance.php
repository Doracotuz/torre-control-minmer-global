<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    protected $fillable = [
        'hardware_asset_id', 'type', 'supplier', 'start_date', 'end_date',
        'diagnosis', 'actions_taken', 'parts_used', 'cost', 'substitute_asset_id'
    ];

    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];

    public function asset() { return $this->belongsTo(HardwareAsset::class, 'hardware_asset_id'); }
    public function substituteAsset() { return $this->belongsTo(HardwareAsset::class, 'substitute_asset_id'); }
}