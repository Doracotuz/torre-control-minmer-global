<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HardwareModel extends Model {
    use HasFactory;
    protected $fillable = ['name', 'manufacturer_id', 'hardware_category_id'];

    public function manufacturer(): BelongsTo {
        return $this->belongsTo(Manufacturer::class);
    }

    public function category(): BelongsTo {
        return $this->belongsTo(HardwareCategory::class, 'hardware_category_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(HardwareAsset::class);
    }

}