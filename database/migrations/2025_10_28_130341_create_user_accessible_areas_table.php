<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Esta tabla almacenará las áreas *adicionales* a las que un usuario tiene acceso
        Schema::create('user_accessible_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('area_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Evitar duplicados
            $table->unique(['user_id', 'area_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_accessible_areas');
    }
};