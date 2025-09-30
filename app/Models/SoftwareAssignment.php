<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoftwareAssignment extends Model
{
    use HasFactory;
    protected $fillable = ['software_license_id', 'hardware_asset_id', 'install_date'];

    public function license(): BelongsTo
    {
        return $this->belongsTo(SoftwareLicense::class, 'software_license_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(HardwareAsset::class, 'hardware_asset_id');
    }
}