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
        Schema::create('organigram_trajectories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organigram_member_id')->constrained('organigram_members')->onDelete('cascade'); // A qué miembro pertenece
            $table->string('title'); // Título del puesto o rol (ej. "Especialista en Aduanas")
            $table->text('description')->nullable(); // Descripción de las responsabilidades
            $table->date('start_date')->nullable(); // Fecha de inicio
            $table->date('end_date')->nullable(); // Fecha de fin (nulo si es actual)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organigram_trajectories');
    }
};
