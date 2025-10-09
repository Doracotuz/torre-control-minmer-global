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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            // Relación con el proyecto al que pertenece
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');

            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['Pendiente', 'En Progreso', 'Completada'])->default('Pendiente');
            $table->enum('priority', ['Baja', 'Media', 'Alta'])->default('Media');
            $table->date('due_date')->nullable();

            // Relación con el usuario asignado a la tarea
            $table->foreignId('assignee_id')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
