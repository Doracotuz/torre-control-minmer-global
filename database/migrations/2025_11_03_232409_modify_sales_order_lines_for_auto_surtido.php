<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_order_lines', function (Blueprint $table) {
            
            $table->foreignId('quality_id')
                  ->nullable()
                  ->after('product_id')
                  ->constrained('qualities')
                  ->onDelete('set null');

            $table->unsignedBigInteger('pallet_item_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sales_order_lines', function (Blueprint $table) {
            $table->dropForeign(['quality_id']);
            $table->dropColumn('quality_id');
            $table->unsignedBigInteger('pallet_item_id')->nullable(false)->change();
        });
    }
};