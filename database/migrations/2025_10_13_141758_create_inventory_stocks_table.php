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
        Schema::create('inventory_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('location_id')->constrained('locations');
            
            // Añadimos la columna de calidad directamente aquí
            $table->foreignId('quality_id')->constrained('qualities');

            $table->unsignedBigInteger('quantity');
            $table->timestamps();

            // Creamos el índice unique correcto de 3 columnas desde el principio
            $table->unique(['product_id', 'location_id', 'quality_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_stocks');
    }
};
