<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FileLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'path',
        'url',
        'folder_id',
        'user_id',
    ];

    protected $appends = ['full_path', 'name_with_extension'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($fileLink) {
            if ($fileLink->type === 'file' && $fileLink->path) {
                if (Storage::disk('s3')->exists($fileLink->path)) {
                    Storage::disk('s3')->delete($fileLink->path);
                    Log::info("Archivo físico eliminado: " . $fileLink->path);
                } else {
                    Log::warning("Archivo físico no encontrado para eliminar: " . $fileLink->path);
                }
            }
        });
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getNameWithExtensionAttribute(): string
    {
        $name = $this->name ?? '';
        if ($this->type === 'file' && $this->path) {
            $extension = pathinfo($this->path, PATHINFO_EXTENSION);
            if (!empty($extension) && !Str::endsWith(strtolower($name), '.' . strtolower($extension))) {
                return $name . '.' . $extension;
            }
        }
        return $name;
    }

    public function getFullPathAttribute(): string
    {
        if ($this->folder) {
            return $this->folder->full_path . '/' . $this->name_with_extension;
        }
        return 'Raíz/' . $this->name_with_extension;
    }
}