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
        Schema::table('hardware_assets', function (Blueprint $table) {
            $table->string('photo_1_path')->nullable()->after('notes');
            $table->string('photo_2_path')->nullable()->after('photo_1_path');
            $table->string('photo_3_path')->nullable()->after('photo_2_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hardware_assets', function (Blueprint $table) {
            //
        });
    }
};
