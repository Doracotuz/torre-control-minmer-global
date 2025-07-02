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
        Schema::create('folder_user', function (Blueprint $table) {
            // Definimos las claves foráneas a las tablas 'folders' y 'users'
            $table->foreignId('folder_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Establecemos una clave primaria compuesta para asegurar unicidad
            $table->primary(['folder_id', 'user_id']);

            $table->timestamps(); // Opcional, para saber cuándo se concedió el permiso
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folder_user');
    }
};
