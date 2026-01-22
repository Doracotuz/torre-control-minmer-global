<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ff_cart_items', function (Blueprint $table) {
            $table->unsignedBigInteger('ff_warehouse_id')->nullable()->after('ff_product_id');
            
            $table->foreign('ff_warehouse_id')
                ->references('id')
                ->on('ff_warehouses')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('ff_cart_items', function (Blueprint $table) {
            $table->dropForeign(['ff_warehouse_id']);
            $table->dropColumn('ff_warehouse_id');
        });
    }
};
