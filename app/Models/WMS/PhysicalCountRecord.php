<?php

namespace App\Models\WMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Importa el modelo User principal

class PhysicalCountRecord extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     * Estos son los campos que se guardan cuando un operario registra un conteo.
     */
    protected $fillable = [
        'physical_count_task_id',
        'user_id',
        'count_number',
        'counted_quantity',
    ];

    /**
     * Define la relación con el usuario que realizó este conteo específico.
     * Un registro de conteo pertenece a un Usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Define la relación con la tarea de conteo a la que pertenece este registro.
     * Un registro de conteo pertenece a una Tarea de Conteo Físico.
     */
    public function physicalCountTask()
    {
        return $this->belongsTo(PhysicalCountTask::class);
    }
}