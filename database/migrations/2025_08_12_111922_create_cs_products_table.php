<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cs_products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->text('description');
            $table->integer('packaging_factor');
            $table->foreignId('cs_brand_id')->constrained('cs_brands')->onDelete('cascade');
            $table->enum('type', ['Producto', 'Promocional']);
            $table->foreignId('created_by_user_id')->constrained('users');
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cs_products');
    }
};
