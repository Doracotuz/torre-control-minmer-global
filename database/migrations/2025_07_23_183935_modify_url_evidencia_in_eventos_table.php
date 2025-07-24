<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Primero, convertimos los datos existentes a un formato JSON válido.
        // Esto toma el texto 'ruta/imagen.jpg' y lo convierte en '["ruta/imagen.jpg"]'
        DB::table('eventos')
            ->whereNotNull('url_evidencia')
            ->where('url_evidencia', '!=', '')
            ->update(['url_evidencia' => DB::raw("JSON_ARRAY(url_evidencia)")]);

        // 2. Ahora que los datos son compatibles, cambiamos el tipo de la columna.
        Schema::table('eventos', function (Blueprint $table) {
            $table->json('url_evidencia')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Esto es por si necesitas revertir la migración en el futuro.
        Schema::table('eventos', function (Blueprint $table) {
            // Nota: La conversión de JSON a string puede no ser perfecta si tienes múltiples imágenes.
            $table->string('url_evidencia')->nullable()->change();
        });
    }
};