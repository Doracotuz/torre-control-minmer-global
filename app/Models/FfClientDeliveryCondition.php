<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FfClientDeliveryCondition extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'revision_upc' => 'boolean', 'distribucion_tienda' => 'boolean', 're_etiquetado' => 'boolean',
        'colocacion_sensor' => 'boolean', 'preparado_especial' => 'boolean', 'tipo_unidad_aceptada' => 'boolean',
        'equipo_seguridad' => 'boolean', 'registro_patronal' => 'boolean', 'entrega_otros_pedidos' => 'boolean',
        'insumos_herramientas' => 'boolean', 'maniobra' => 'boolean', 'identificaciones' => 'boolean',
        'etiqueta_fragil' => 'boolean', 'tarima_chep' => 'boolean', 'granel' => 'boolean', 'tarima_estandar' => 'boolean',
        'doc_factura' => 'boolean', 'doc_do' => 'boolean', 'doc_carta_maniobra' => 'boolean', 'doc_carta_poder' => 'boolean',
        'doc_orden_compra' => 'boolean', 'doc_carta_confianza' => 'boolean', 'doc_confirmacion_cita' => 'boolean',
        'doc_carta_caja_cerrada' => 'boolean', 'doc_confirmacion_facturas' => 'boolean', 'doc_caratula_entrega' => 'boolean',
        'doc_pase_vehicular' => 'boolean', 'evid_folio_recibo' => 'boolean', 'evid_factura_sellada' => 'boolean',
        'evid_sello_tarima' => 'boolean', 'evid_etiqueta_recibo' => 'boolean', 'evid_acuse_oc' => 'boolean',
        'evid_hoja_rechazo' => 'boolean', 'evid_anotacion_rechazo' => 'boolean', 'evid_contrarrecibo' => 'boolean',
        'evid_formato_reparto' => 'boolean',
    ];

    public function getImageUrl($column)
    {
        if ($this->$column) {
            return Storage::disk('s3')->url($this->$column);
        }
        return null;
    }

    public function client()
    {
        return $this->belongsTo(FfClient::class, 'ff_client_id');
    }
}