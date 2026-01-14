<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $tables = ['ff_clients', 'ff_sales_channels', 'ff_transport_lines', 'ff_payment_conditions'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'area_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->foreignId('area_id')->nullable()->constrained('areas')->onDelete('cascade');
                });
            }
        }
    }

    public function down()
    {
        $tables = ['ff_clients', 'ff_sales_channels', 'ff_transport_lines', 'ff_payment_conditions'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'area_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropForeign(['area_id']);
                    $table->dropColumn('area_id');
                });
            }
        }
    }
};