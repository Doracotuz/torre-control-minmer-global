<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ff_cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('ff_product_id')->constrained('ff_products')->onDelete('cascade');
            $table->integer('quantity');
            $table->timestamps();

            $table->unique(['user_id', 'ff_product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ff_cart_items');
    }
};