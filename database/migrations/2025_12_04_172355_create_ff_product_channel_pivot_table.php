<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ff_product_sales_channel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ff_product_id')->constrained('ff_products')->onDelete('cascade');
            $table->foreignId('ff_sales_channel_id')->constrained('ff_sales_channels')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::table('ff_products', function (Blueprint $table) {
            if (Schema::hasColumn('ff_products', 'ff_sales_channel_id')) {
                $table->dropForeign(['ff_sales_channel_id']);
                $table->dropColumn('ff_sales_channel_id');
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('ff_product_sales_channel');
        
        Schema::table('ff_products', function (Blueprint $table) {
            $table->foreignId('ff_sales_channel_id')->nullable()->constrained('ff_sales_channels')->nullOnDelete();
        });
    }
};