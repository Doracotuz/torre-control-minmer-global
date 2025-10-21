<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('physical_count_tasks', function (Blueprint $table) {
            $table->foreignId('pallet_id')->nullable()->after('location_id')->constrained('pallets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('physical_count_tasks', function (Blueprint $table) {
            //
        });
    }
};
