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
        Schema::table('cs_customers', function (Blueprint $table) {
            $table->json('delivery_specifications')->nullable()->after('channel');
        });
    }

    public function down()
    {
        Schema::table('cs_customers', function (Blueprint $table) {
            $table->dropColumn('delivery_specifications');
        });
    }
};
