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
        Schema::create('organigram_members', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre del miembro
            $table->string('email')->nullable(); // Correo electrónico (puede no ser el de login)
            $table->string('cell_phone')->nullable(); // Número de celular
            $table->string('position'); // Posición en la empresa (ej. "Gerente de Logística")
            $table->string('profile_photo_path')->nullable(); // Ruta de la foto de perfil

            // Relación con el área de la empresa (un miembro pertenece a un área)
            // Usamos 'areas' de la aplicación principal
            $table->foreignId('area_id')->constrained('areas')->onDelete('cascade');

            // Relación jerárquica (manager/jefe)
            // Se referencia a sí misma, es nullable (los CEOs no tienen manager)
            $table->foreignId('manager_id')->nullable()->constrained('organigram_members')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organigram_members');
    }
};
