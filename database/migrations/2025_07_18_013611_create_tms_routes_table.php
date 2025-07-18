<?php
// En el archivo database/migrations/xxxx_xx_xx_xxxxxx_create_tms_routes_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tms_routes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->longText('polyline')->nullable(); // Para guardar las coordenadas de la ruta
            $table->decimal('total_distance_km', 8, 2)->nullable();
            $table->integer('total_duration_min')->nullable();
            $table->enum('status', ['Planeada', 'Asignada', 'En transito', 'Completada', 'Cancelada'])->default('Planeada');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tms_routes');
    }
};