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
        Schema::table('asset_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('loggable_id')->nullable()->change();
            $table->string('loggable_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('loggable_id')->nullable(false)->change();
            $table->string('loggable_type')->nullable(false)->change();
        });
    }
};