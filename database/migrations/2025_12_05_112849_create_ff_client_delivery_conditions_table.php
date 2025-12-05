<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ff_client_delivery_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ff_client_id')->constrained('ff_clients')->onDelete('cascade');
            
            $table->boolean('revision_upc')->default(false);
            $table->boolean('distribucion_tienda')->default(false);
            $table->boolean('re_etiquetado')->default(false);
            $table->boolean('colocacion_sensor')->default(false);
            $table->boolean('preparado_especial')->default(false);
            $table->boolean('tipo_unidad_aceptada')->default(false);
            $table->boolean('equipo_seguridad')->default(false);
            $table->boolean('registro_patronal')->default(false);
            $table->boolean('entrega_otros_pedidos')->default(false);
            $table->boolean('insumos_herramientas')->default(false);
            $table->boolean('maniobra')->default(false);
            $table->boolean('identificaciones')->default(false);
            $table->boolean('etiqueta_fragil')->default(false);
            $table->boolean('tarima_chep')->default(false);
            $table->boolean('granel')->default(false);
            $table->boolean('tarima_estandar')->default(false);

            $table->boolean('doc_factura')->default(false);
            $table->boolean('doc_do')->default(false);
            $table->boolean('doc_carta_maniobra')->default(false);
            $table->boolean('doc_carta_poder')->default(false);
            $table->boolean('doc_orden_compra')->default(false);
            $table->boolean('doc_carta_confianza')->default(false);
            $table->boolean('doc_confirmacion_cita')->default(false);
            $table->boolean('doc_carta_caja_cerrada')->default(false);
            $table->boolean('doc_confirmacion_facturas')->default(false);
            $table->boolean('doc_caratula_entrega')->default(false);
            $table->boolean('doc_pase_vehicular')->default(false);

            $table->boolean('evid_folio_recibo')->default(false);
            $table->boolean('evid_factura_sellada')->default(false);
            $table->boolean('evid_sello_tarima')->default(false);
            $table->boolean('evid_etiqueta_recibo')->default(false);
            $table->boolean('evid_acuse_oc')->default(false);
            $table->boolean('evid_hoja_rechazo')->default(false);
            $table->boolean('evid_anotacion_rechazo')->default(false);
            $table->boolean('evid_contrarrecibo')->default(false);
            $table->boolean('evid_formato_reparto')->default(false);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ff_client_delivery_conditions');
    }
};