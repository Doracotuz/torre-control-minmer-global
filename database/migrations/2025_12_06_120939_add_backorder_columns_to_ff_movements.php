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
            $table->boolean('is_backorder')->default(false);
            $table->boolean('backorder_fulfilled')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ff_movements', function (Blueprint $table) {
            //
        });
    }
};
