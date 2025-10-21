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
        Schema::table('pick_list_items', function (Blueprint $table) {
            // Guardaremos de qué pallet específico se debe surtir
            $table->foreignId('pallet_id')->nullable()->after('location_id')->constrained('pallets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pick_list_items', function (Blueprint $table) {
            //
        });
    }
};
