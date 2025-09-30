<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HardwareAsset extends Model {
    use HasFactory;
    protected $fillable = [
        'asset_tag', 'serial_number', 'hardware_model_id', 'site_id', 'status', 
        'purchase_date', 'warranty_end_date', 'cpu', 'ram', 'storage', 
        'mac_address', 'phone_plan_type', 'phone_number', 'notes'
    ];

    public function model(): BelongsTo {
        return $this->belongsTo(HardwareModel::class, 'hardware_model_id');
    }

    public function site(): BelongsTo {
        return $this->belongsTo(Site::class);
    }

    public function assignments(): HasMany {
        return $this->hasMany(Assignment::class);
    }

    public function currentAssignment() {
        return $this->hasOne(Assignment::class)->whereNull('actual_return_date')->latestOfMany();
    }

    public function softwareAssignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SoftwareAssignment::class);
    }

}