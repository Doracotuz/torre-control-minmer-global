<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SoftwareLicense extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'license_key', 'total_seats', 'purchase_date', 'expiry_date'];

    protected $casts = [
        'license_key' => 'encrypted', // Encripta automáticamente la clave
        'purchase_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(SoftwareAssignment::class);
    }

    // Atributo calculado para saber los asientos usados
    public function getUsedSeatsAttribute(): int
    {
        return $this->assignments()->count();
    }
}