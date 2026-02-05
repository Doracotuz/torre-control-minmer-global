<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ff_cart_items', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['ff_product_id']);
            $table->dropUnique('ff_cart_items_user_id_ff_product_id_unique');
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
                  
            $table->foreign('ff_product_id')
                  ->references('id')->on('ff_products')
                  ->onDelete('cascade');
            $table->unique(['user_id', 'ff_product_id', 'ff_quality_id'], 'cart_item_unique_quality');
        });
    }

    public function down(): void
    {
        Schema::table('ff_cart_items', function (Blueprint $table) {
            $table->dropUnique('cart_item_unique_quality');
        });
    }
};