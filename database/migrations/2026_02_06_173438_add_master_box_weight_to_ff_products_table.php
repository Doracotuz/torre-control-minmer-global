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
        Schema::table('ff_products', function (Blueprint $table) {
            $table->decimal('master_box_weight', 8, 3)->nullable()->after('unit_price')->comment('Peso de la caja master en Kg');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ff_products', function (Blueprint $table) {
            $table->dropColumn('master_box_weight');
        });
    }
};
