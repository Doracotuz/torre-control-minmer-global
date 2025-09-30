<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Site extends Model {
    use HasFactory;
    protected $fillable = ['name', 'address'];

    public function assets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(HardwareAsset::class);
    }
    
}
