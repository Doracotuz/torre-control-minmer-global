<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cs_orders', function (Blueprint $table) {
            $table->integer('audit_tarimas_cantidad')->nullable()->after('audit_almacen_observaciones');
            $table->string('audit_tarimas_tipo')->nullable()->after('audit_tarimas_cantidad');
            $table->boolean('audit_emplayado_correcto')->nullable()->after('audit_tarimas_tipo');
            $table->boolean('audit_etiquetado_correcto')->nullable()->after('audit_emplayado_correcto');
            $table->boolean('audit_distribucion_correcta')->nullable()->after('audit_etiquetado_correcto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cs_orders', function (Blueprint $table) {
            $table->dropColumn([
                'audit_tarimas_cantidad',
                'audit_tarimas_tipo',
                'audit_emplayado_correcto',
                'audit_etiquetado_correcto',
                'audit_distribucion_correcta',
            ]);
        });
    }
};
