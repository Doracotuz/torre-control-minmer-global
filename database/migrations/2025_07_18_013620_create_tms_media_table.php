<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tms_media', function (Blueprint $table) {
            $table->id();
            $table->morphs('model'); // Crea model_id y model_type
            $table->string('file_path');
            $table->string('collection_name')->default('default'); // Para agrupar fotos
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tms_media');
    }
};