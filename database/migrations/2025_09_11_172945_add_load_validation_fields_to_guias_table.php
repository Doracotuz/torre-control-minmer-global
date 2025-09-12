<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('guias', function (Blueprint $table) {
            // Añadimos las nuevas columnas booleanas al final de la tabla
            $table->boolean('audit_carga_emplayado_correcto')->default(false)->after('audit_carga_fotos');
            $table->boolean('audit_carga_etiquetado_correcto')->default(false)->after('audit_carga_emplayado_correcto');
            $table->boolean('audit_carga_distribucion_correcta')->default(false)->after('audit_carga_etiquetado_correcto');
        });
    }

    public function down()
    {
        Schema::table('guias', function (Blueprint $table) {
            $table->dropColumn([
                'audit_carga_emplayado_correcto',
                'audit_carga_etiquetado_correcto',
                'audit_carga_distribucion_correcta'
            ]);
        });
    }
};
