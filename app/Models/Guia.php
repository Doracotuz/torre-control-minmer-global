<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guia extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Campos originales
        'guia',
        'ruta_id',
        'operador',
        'placas',
        'transporte',
        'telefono',
        'pedimento',
        'estatus',
        'fecha_inicio_ruta',
        'fecha_fin_ruta',
        'custodia',
        'hora_planeada',
        'fecha_asignacion',
        'origen',

        // Campos de Auditoría de Patio
        'audit_patio_arribo',
        'audit_patio_caja_estado',
        'audit_patio_llantas_estado',
        'audit_patio_combustible_nivel',
        'audit_patio_presenta_maniobra',
        'audit_patio_equipo_sujecion',
        'audit_patio_fotos',
        
        // Campos de Auditoría de Carga
        'marchamo_numero',
        'lleva_custodia',
        'audit_carga_fotos',

        'audit_carga_emplayado_correcto',
        'audit_carga_etiquetado_correcto',
        'audit_carga_distribucion_correcta',     
        'audit_carga_incluye_tarimas',
        'audit_carga_tarimas_chep',
        'audit_carga_tarimas_estandar',       
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_inicio_ruta' => 'datetime',
        'fecha_fin_ruta' => 'datetime',
        'fecha_asignacion' => 'date:Y-m-d',
        'audit_patio_arribo' => 'datetime',
        'lleva_custodia' => 'boolean',
        'audit_patio_presenta_maniobra' => 'boolean',
        'audit_patio_fotos' => 'array',
        'audit_carga_fotos' => 'array',
    ];

    /**
     * Get the route associated with the Guia.
     */
    public function ruta()
    {
        return $this->belongsTo(Ruta::class);
    }

    /**
     * Get the facturas for the Guia.
     */
    public function facturas()
    {
        return $this->hasMany(Factura::class);
    }

    /**
     * Get the eventos for the Guia.
     */
    public function eventos()
    {
        return $this->hasMany(Evento::class)->orderBy('fecha_evento');
    }

    /**
     * Get the maniobra eventos for the Guia.
     */
    public function maniobraEventos() {
        return $this->hasMany(\App\Models\ManiobraEvento::class);
    }

    /**
     * Get the plannings for the Guia.
     */
    public function plannings()
    {
        return $this->hasMany(\App\Models\CsPlanning::class, 'guia_id');
    }    

    public function incidencias()
    {
        return $this->hasMany(\App\Models\AuditIncidencia::class);
    }    

}