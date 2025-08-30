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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guias', function (Blueprint $table) {
            $table->dropColumn([
                'audit_patio_arribo',
                'audit_patio_caja_estado',
                'audit_patio_llantas_estado',
                'audit_patio_combustible_nivel',
                'audit_patio_presenta_maniobra',
                'audit_patio_equipo_sujecion',
                'audit_patio_fotos',
            ]);
        });
    }
};
