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

    protected $appends = ['full_path'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($folder) {
            $folder->children->each(function ($childFolder) {
                $childFolder->delete();
            });

            $folder->fileLinks->each(function ($fileLink) {
                $fileLink->delete();
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

    public function usersWithAccess(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'folder_user');
    }

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