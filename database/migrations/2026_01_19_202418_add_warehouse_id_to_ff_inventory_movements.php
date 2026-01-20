<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('ff_inventory_movements', function (Blueprint $table) {
            $table->unsignedBigInteger('ff_warehouse_id')->nullable()->after('area_id');

            $table->foreign('ff_warehouse_id')->references('id')->on('ff_warehouses')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ff_inventory_movements', function (Blueprint $table) {
            //
        });
    }
};
