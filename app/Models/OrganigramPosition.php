<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganigramPosition extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'hierarchy_level'];

    public function members(): HasMany
    {
        return $this->hasMany(OrganigramMember::class, 'position_id');
    }
}