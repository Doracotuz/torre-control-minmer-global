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
        Schema::table('cs_plannings', function (Blueprint $table) {
            // Hacemos que las columnas que pueden estar vacías al momento de planificar sean "nullable"
            $table->string('destino')->nullable()->change();
            $table->date('fecha_entrega')->nullable()->change();
            // Puedes añadir más campos aquí si también necesitas que sean opcionales, por ejemplo:
            // $table->string('hora_cita')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cs_plannings', function (Blueprint $table) {
            // Esto revierte los cambios si alguna vez necesitas deshacer la migración
            $table->string('destino')->nullable(false)->change();
            $table->date('fecha_entrega')->nullable(false)->change();
        });
    }
};