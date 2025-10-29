<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guias', function (Blueprint $table) {
            $table->string('marchamo_numero')->nullable()->after('audit_patio_fotos');
            $table->boolean('lleva_custodia')->default(false)->after('marchamo_numero');
            $table->json('audit_carga_fotos')->nullable()->after('lleva_custodia');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('guias', function (Blueprint $table) {
            $table->dropColumn(['marchamo_numero', 'lleva_custodia', 'audit_carga_fotos']);
        });
    }
};