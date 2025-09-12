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
            // Añadimos las nuevas columnas después de las de validación
            $table->integer('audit_carga_tarimas_cantidad')->unsigned()->nullable()->after('audit_carga_distribucion_correcta');
            $table->string('audit_carga_tarimas_tipo')->nullable()->after('audit_carga_tarimas_cantidad');
        });
    }

    public function down()
    {
        Schema::table('guias', function (Blueprint $table) {
            $table->dropColumn([
                'audit_carga_tarimas_cantidad',
                'audit_carga_tarimas_tipo'
            ]);
        });
    }
};
