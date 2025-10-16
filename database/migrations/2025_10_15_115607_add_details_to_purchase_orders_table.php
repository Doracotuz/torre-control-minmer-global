<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('operator_name')->nullable()->after('status');
            $table->integer('total_pallets')->unsigned()->nullable()->after('operator_name');
            $table->integer('expected_bottles')->unsigned()->nullable()->after('total_pallets');
            $table->integer('received_bottles')->unsigned()->default(0)->after('expected_bottles');
            $table->timestamp('download_start_time')->nullable()->after('received_bottles');
            $table->timestamp('download_end_time')->nullable()->after('download_start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn([
                'operator_name',
                'total_pallets',
                'expected_bottles',
                'received_bottles',
                'download_start_time',
                'download_end_time',
            ]);
        });
    }
};