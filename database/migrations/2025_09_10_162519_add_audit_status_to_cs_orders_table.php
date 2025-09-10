<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cs_orders', function (Blueprint $table) {
            $table->string('audit_status')->default('Pendiente')->after('status');
        });
    }

    public function down()
    {
        Schema::table('cs_orders', function (Blueprint $table) {
            $table->dropColumn('audit_status');
        });
    }
};