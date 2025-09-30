<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class HardwareCategory extends Model {
    use HasFactory;
    protected $fillable = ['name'];

    public function models(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(HardwareModel::class);
    }

    public function assets()
    {
        return $this->hasManyThrough(HardwareAsset::class, HardwareModel::class);
    }    

}