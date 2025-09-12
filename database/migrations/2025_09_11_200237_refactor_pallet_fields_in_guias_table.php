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
            // Primero, eliminamos las columnas antiguas si existen
            if (Schema::hasColumn('guias', 'audit_carga_tarimas_cantidad')) {
                $table->dropColumn('audit_carga_tarimas_cantidad');
            }
            if (Schema::hasColumn('guias', 'audit_carga_tarimas_tipo')) {
                $table->dropColumn('audit_carga_tarimas_tipo');
            }

            // Ahora, añadimos las nuevas columnas
            $table->boolean('audit_carga_incluye_tarimas')->default(false)->after('audit_carga_distribucion_correcta');
            $table->integer('audit_carga_tarimas_chep')->unsigned()->nullable()->after('audit_carga_incluye_tarimas');
            $table->integer('audit_carga_tarimas_estandar')->unsigned()->nullable()->after('audit_carga_tarimas_chep');
        });
    }

    public function down()
    {
        Schema::table('guias', function (Blueprint $table) {
            // Revertimos los cambios
            $table->dropColumn([
                'audit_carga_incluye_tarimas',
                'audit_carga_tarimas_chep',
                'audit_carga_tarimas_estandar'
            ]);
            // Opcional: Volvemos a añadir las columnas viejas si necesitas poder revertir
            $table->integer('audit_carga_tarimas_cantidad')->unsigned()->nullable();
            $table->string('audit_carga_tarimas_tipo')->nullable();
        });
    }
};
