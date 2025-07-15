<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Folder extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_id',
        'area_id',
        'user_id',
    ];

    protected $appends = ['full_path']; // Asegúrate de mantener esto

    /**
     * El método "boot" de la clase.
     * Aquí registramos los eventos del modelo.
     */
    protected static function boot()
    {
        parent::boot();

        // Cuando una carpeta está a punto de ser eliminada...
        static::deleting(function ($folder) {
            // Eliminar recursivamente subcarpetas
            $folder->children->each(function ($childFolder) {
                $childFolder->delete(); // Esto disparará recursivamente el evento 'deleting' para las subcarpetas
            });

            // Eliminar todos los fileLinks asociados a esta carpeta
            $folder->fileLinks->each(function ($fileLink) {
                $fileLink->delete(); // <-- Esto ahora DISPARARÁ el evento 'deleting' en FileLink
            });
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fileLinks(): HasMany
    {
        return $this->hasMany(FileLink::class);
    }

    /**
     * Get the users who have access to this folder.
     */
    public function usersWithAccess(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'folder_user');
    }

    /**
     * Accessor para obtener la ruta completa de la carpeta.
     */
    public function getFullPathAttribute(): string
    {
        $path = $this->name;
        $current = $this;
        while ($current->parent) {
            $current = $current->parent;
            $path = $current->name . '/' . $path;
        }
        return 'Raíz/' . $path;
    }

    public function childrenRecursive(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_id')->with('childrenRecursive');
    }
}