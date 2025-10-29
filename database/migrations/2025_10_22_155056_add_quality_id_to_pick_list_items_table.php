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
            $table->foreignId('quality_id')->nullable()->after('location_id')->constrained('qualities')->onDelete('set null'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pick_list_items', function (Blueprint $table) {
            $table->dropForeign(['quality_id']); 
            $table->dropColumn('quality_id');
        });
    }
};
