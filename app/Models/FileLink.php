<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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
                if (Storage::disk('public')->exists($fileLink->path)) {
                    Storage::disk('public')->delete($fileLink->path);
                    \Log::info("Archivo físico eliminado: " . $fileLink->path);
                } else {
                    \Log::warning("Archivo físico no encontrado para eliminar: " . $fileLink->path);
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

    /**
     * Accessor para obtener el nombre del archivo con su extensión original.
     */
    public function getNameWithExtensionAttribute(): string // Mantiene el tipo de retorno string
    {
        $name = $this->name ?? ''; // <-- Asegura que $name nunca sea null
        if ($this->type === 'file' && $this->path) {
            $extension = pathinfo($this->path, PATHINFO_EXTENSION);
            if (!empty($extension) && !Str::endsWith(strtolower($name), '.' . strtolower($extension))) {
                return $name . '.' . $extension;
            }
        }
        return $name; // Siempre retorna un string (vacío si $this->name era null)
    }

    /**
     * Accessor para obtener la ruta completa del archivo/enlace, incluyendo la ruta de la carpeta.
     */
    public function getFullPathAttribute(): string
    {
        // Asegúrate de que $this->folder no sea null si la relación no se cargó correctamente
        // y que getNameWithExtensionAttribute siempre retorne string.
        if ($this->folder) {
            // El problema original estaba en getNameWithExtensionAttribute,
            // si eso devuelve un string, esto funcionará.
            return $this->folder->full_path . '/' . $this->name_with_extension;
        }
        return 'Raíz/' . $this->name_with_extension; // Asegúrate de que name_with_extension sea siempre string
    }
}