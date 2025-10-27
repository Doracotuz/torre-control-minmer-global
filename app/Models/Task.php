<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'status',
        'priority',
        'due_date',
        'assignee_id',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'datetime', // O 'date' si solo guardas la fecha
        ];
    }    

    /**
     * Una tarea pertenece a un proyecto.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Una tarea está asignada a un usuario.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }
}