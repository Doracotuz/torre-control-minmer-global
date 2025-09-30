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
            // Hacemos que las dos columnas sean nulables
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
            // Esto revierte los cambios si es necesario
            $table->unsignedBigInteger('loggable_id')->nullable(false)->change();
            $table->string('loggable_type')->nullable(false)->change();
        });
    }
};