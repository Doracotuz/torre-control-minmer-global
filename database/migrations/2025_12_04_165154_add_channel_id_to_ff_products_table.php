<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ff_products', function (Blueprint $table) {
            $table->foreignId('ff_sales_channel_id')
                  ->nullable()
                  ->constrained('ff_sales_channels')
                  ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('ff_products', function (Blueprint $table) {
            $table->dropForeign(['ff_sales_channel_id']);
            $table->dropColumn('ff_sales_channel_id');
        });
    }
};