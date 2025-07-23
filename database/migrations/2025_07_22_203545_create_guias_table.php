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
        Schema::create('guias', function (Blueprint $table) {
            $table->id();
            $table->string('guia')->unique();
            $table->foreignId('ruta_id')->nullable()->constrained('rutas')->onDelete('set null');
            $table->string('operador');
            $table->string('placas');
            $table->string('pedimento')->nullable();
            $table->enum('estatus', ['En Espera', 'Planeada', 'En Transito', 'Completada'])->default('En Espera');
            $table->timestamp('fecha_inicio_ruta')->nullable();
            $table->timestamp('fecha_fin_ruta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guias');
    }
};