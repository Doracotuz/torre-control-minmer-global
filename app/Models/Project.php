<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'start_date',
        'due_date',
        'budget',
        'leader_id',
    ];

    /**
     * Un proyecto tiene muchas tareas.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Un proyecto es liderado por un usuario.
     */
    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function comments(): HasMany
    {
        // Ordenamos los comentarios para que los más recientes aparezcan primero.
        return $this->hasMany(ProjectComment::class)->latest();
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProjectFile::class)->latest();
    }    

}