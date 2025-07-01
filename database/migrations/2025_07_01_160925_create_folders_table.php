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
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre de la carpeta
            $table->foreignId('parent_id')->nullable()->constrained('folders')->onDelete('cascade'); // Para subcarpetas
            $table->foreignId('area_id')->constrained()->onDelete('cascade'); // A qué área pertenece la carpeta
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Quién creó la carpeta
            $table->timestamps();

            // Asegura que no haya dos carpetas con el mismo nombre en el mismo padre y la misma área
            $table->unique(['name', 'parent_id', 'area_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folders');
    }
};