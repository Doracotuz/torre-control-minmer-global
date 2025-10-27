<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon_path', // ¡Añade esta línea!
    ];

    /**
     * Get the users for the Area.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the folders for the Area.
     */
    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    public function accessibleByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_accessible_areas');
    }    

}