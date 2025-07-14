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
        Schema::table('users', function (Blueprint $table) {
            // Modifica la columna area_id para que sea nullable
            $table->unsignedBigInteger('area_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revierte el cambio para hacerla de nuevo no nullable si lo necesitas
            // CUIDADO: Esto podría fallar si hay usuarios con area_id nulo
            $table->unsignedBigInteger('area_id')->nullable(false)->change();
        });
    }
};