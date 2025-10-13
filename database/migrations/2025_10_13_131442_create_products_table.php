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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->text('description')->nullable();

            // --- NUEVOS CAMPOS RELACIONADOS ---
            $table->foreignId('brand_id')->nullable()->constrained('brands');
            $table->foreignId('product_type_id')->nullable()->constrained('product_types');

            // --- NUEVOS CAMPOS DE UNIDAD Y DIMENSIONES ---
            $table->string('unit_of_measure'); // Ej: 'caja', 'pieza', 'pallet'
            $table->decimal('length', 8, 2)->nullable(); // Largo en cm
            $table->decimal('width', 8, 2)->nullable();  // Ancho en cm
            $table->decimal('height', 8, 2)->nullable(); // Alto en cm
            $table->decimal('weight', 8, 2)->nullable(); // en kg

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
