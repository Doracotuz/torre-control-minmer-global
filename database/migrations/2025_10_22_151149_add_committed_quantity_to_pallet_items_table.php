<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pallet_items', function (Blueprint $table) {
            $table->integer('committed_quantity')->default(0)->after('quantity');
        });
    }

    public function down()
    {
        Schema::table('pallet_items', function (Blueprint $table) {
            $table->dropColumn('committed_quantity');
        });
    }
};
