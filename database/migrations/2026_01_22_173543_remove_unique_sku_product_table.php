<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ff_products', function (Blueprint $table) {
            $table->dropUnique('ff_products_sku_unique');
            $table->unique(['sku', 'area_id'], 'ff_products_sku_area_unique');
        });
    }

    public function down()
    {
        Schema::table('ff_products', function (Blueprint $table) {
            $table->dropUnique('ff_products_sku_area_unique');
            $table->unique('sku', 'ff_products_sku_unique');
        });
    }
};
