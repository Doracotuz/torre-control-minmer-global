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
            // Se añade después de la columna que indica si presenta maniobra
            $table->integer('audit_patio_maniobra_personas')->unsigned()->nullable()->after('audit_patio_presenta_maniobra');
        });
    }

    public function down()
    {
        Schema::table('guias', function (Blueprint $table) {
            $table->dropColumn('audit_patio_maniobra_personas');
        });
    }
};
