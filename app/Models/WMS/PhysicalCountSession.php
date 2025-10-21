<?php

namespace App\Models\WMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Importa el modelo User

class PhysicalCountSession extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'name',
        'type',
        'user_id',
        'status', // Es buena práctica incluir también el status si lo manejas
    ];

    /**
     * Define la relación con el usuario que creó la sesión.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Define la relación con las tareas de conteo de esta sesión.
     */
    public function tasks()
    {
        return $this->hasMany(PhysicalCountTask::class);
    }
}