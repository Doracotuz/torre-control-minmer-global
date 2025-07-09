<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganigramPosition extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'hierarchy_level'];

    /**
     * Get the organigram members that have this position.
     */
    public function members(): HasMany
    {
        return $this->hasMany(OrganigramMember::class, 'position_id'); // Asegúrate de usar 'position_id' si cambias el campo
    }
}