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
        Schema::create('organigram_activities', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre de la actividad (ej. "Gestión de Proyectos Logísticos")
            $table->text('description')->nullable(); // Descripción de la actividad
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organigram_activities');
    }
};