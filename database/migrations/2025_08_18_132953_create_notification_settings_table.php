<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('event_name'); // Ej: 'Creó una carpeta', 'Subió un archivo'
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Para evitar duplicados (mismo usuario para el mismo evento)
            $table->unique(['event_name', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};