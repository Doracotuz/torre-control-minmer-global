<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pallets', function (Blueprint $table) {
            // Aumentamos el tamaño de la columna a 50 para tener espacio suficiente
            $table->string('status', 50)->change();
        });
    }

    public function down(): void
    {
        // Opcional: Para poder revertir la migración si es necesario
        Schema::table('pallets', function (Blueprint $table) {
            // Aquí podrías definir el tamaño original si lo recuerdas
        });
    }
};