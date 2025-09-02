<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditIncidencia extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'guia_id',
        'user_id',
        'tipo_incidencia',
    ];

    /**
     * Obtiene la guía a la que pertenece la incidencia.
     */
    public function guia()
    {
        return $this->belongsTo(Guia::class);
    }

    /**
     * Obtiene el usuario (auditor) que registró la incidencia.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}