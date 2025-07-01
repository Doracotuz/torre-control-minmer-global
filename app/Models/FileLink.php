<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /**
     * Get the folder that the file/link belongs to.
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Get the user who created the file/link.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
