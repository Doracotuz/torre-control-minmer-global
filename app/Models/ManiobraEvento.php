<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManiobraEvento extends Model
{
    protected $fillable = ['guia_id', 'numero_empleado', 'evento_tipo', 'latitud', 'longitud', 'municipio', 'evidencia_path'];
}
