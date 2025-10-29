<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditIncidencia extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'guia_id',
        'user_id',
        'tipo_incidencia',
    ];

    public function guia()
    {
        return $this->belongsTo(Guia::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}