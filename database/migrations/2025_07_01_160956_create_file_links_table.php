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
        Schema::create('file_links', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre del archivo o enlace
            $table->string('type'); // 'file' o 'link'
            $table->string('path')->nullable(); // Ruta del archivo si es 'file'
            $table->string('url')->nullable(); // URL si es 'link'
            $table->foreignId('folder_id')->constrained()->onDelete('cascade'); // A qué carpeta pertenece
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Quién subió/creó
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_links');
    }
};