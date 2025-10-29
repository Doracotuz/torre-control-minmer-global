<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('cs_orders', function (Blueprint $table) {
            $table->text('audit_almacen_observaciones')->nullable()->after('status');
        });

        Schema::table('cs_order_details', function (Blueprint $table) {
            $table->boolean('audit_sku_validado')->default(false)->after('quantity');
            $table->boolean('audit_piezas_validadas')->default(false)->after('audit_sku_validado');
            $table->boolean('audit_upc_validado')->default(false)->after('audit_piezas_validadas');
            $table->string('audit_calidad')->nullable()->after('audit_upc_validado');
        });

        Schema::table('guias', function (Blueprint $table) {
            $table->dateTime('audit_patio_arribo')->nullable()->after('estatus');
            $table->string('audit_patio_caja_estado')->nullable()->after('audit_patio_arribo');
            $table->string('audit_patio_llantas_estado')->nullable()->after('audit_patio_caja_estado');
            $table->string('audit_patio_combustible_nivel')->nullable()->after('audit_patio_llantas_estado');
            $table->boolean('audit_patio_presenta_maniobra')->default(false)->after('audit_patio_combustible_nivel');
            $table->string('audit_patio_equipo_sujecion')->nullable()->after('audit_patio_presenta_maniobra');
            $table->json('audit_patio_fotos')->nullable()->after('audit_patio_equipo_sujecion');
        });
    }

};