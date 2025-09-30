<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assignment extends Model {
    use HasFactory;
    protected $fillable = ['hardware_asset_id', 'organigram_member_id', 'assignment_date', 'expected_return_date', 'actual_return_date'];

    public function asset(): BelongsTo {
        return $this->belongsTo(HardwareAsset::class, 'hardware_asset_id');
    }

    public function member(): BelongsTo {
        return $this->belongsTo(OrganigramMember::class, 'organigram_member_id');
    }
}