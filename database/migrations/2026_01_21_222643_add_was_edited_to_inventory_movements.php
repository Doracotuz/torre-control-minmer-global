<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ff_inventory_movements', function (Blueprint $table) {
            $table->boolean('was_edited')->default(false)->after('status');
        });
    }

    public function down()
    {
        Schema::table('ff_inventory_movements', function (Blueprint $table) {
            $table->dropColumn('was_edited');
        });
    }
};
