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
        'url_evidencia' => 'array',
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
        if (is_null($value)) {
            return [];
        }

        $paths = json_decode($value, true);

        if (!is_array($paths)) {
            return [];
        }

        return collect($paths)->map(function ($path) {
            if (is_string($path)) {
                return Storage::disk('s3')->url($path);
            }
            return null;
        })->filter()->all();
    }

}