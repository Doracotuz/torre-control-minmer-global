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
        Schema::create('ff_products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique()->comment('SKU único del producto');
            $table->string('description', 500);
            $table->string('type')->nullable()->comment('Ej: Playera, Taza, Gorra');
            $table->string('brand')->nullable()->comment('Ej: Minmer, Nike, etc.');
            $table->decimal('price', 10, 2)->default(0.00);
            $table->string('photo_path')->nullable()->comment('Ruta de la imagen en S3');
            $table->boolean('is_active')->default(true)->comment('Define si el producto está visible/disponible');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ff_products');
    }
};
