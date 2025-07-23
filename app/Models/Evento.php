<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Evento extends Model
{
    use HasFactory;

    protected $fillable = [
        'guia_id',
        'factura_id',
        'tipo',
        'subtipo',
        'nota',
        'url_evidencia',
        'latitud',
        'longitud',
        'fecha_evento',
    ];

    protected $casts = [
        'fecha_evento' => 'datetime',
    ];

    public function guia()
    {
        return $this->belongsTo(Guia::class);
    }

    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }

    public function getUrlEvidenciaAttribute($value)
    {
        // Si el valor en la BD existe, genera la URL de S3. Si no, devuelve null.
        return $value ? Storage::disk('s3')->url($value) : null;
    }

}