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
        Schema::create('organigram_skills', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Nombre de la habilidad (ej. "SAP", "Liderazgo")
            $table->text('description')->nullable(); // Descripción de la habilidad
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organigram_skills');
    }
};