<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $tables = [
            'ff_products', 
            'ff_clients', 
            'ff_inventory_movements', 
            'ff_sales_channels', 
            'ff_transport_lines', 
            'ff_payment_conditions'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'area_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('area_id')
                          ->nullable()
                          ->after('id')
                          ->constrained('areas')
                          ->onDelete('cascade');
                });
            }
        }
    }

    public function down()
    {
        $tables = [
            'ff_products', 'ff_clients', 'ff_inventory_movements', 
            'ff_sales_channels', 'ff_transport_lines', 'ff_payment_conditions'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'area_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['area_id']);
                    $table->dropColumn('area_id');
                });
            }
        }
    }
};