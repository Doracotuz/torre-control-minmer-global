<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturaEvidencia extends Model
{
    protected $fillable = [
        'factura_id', 
        'numero_empleado', 
        'evidencia_path',
        'latitud',
        'longitud',
        'municipio'
    ];
}
