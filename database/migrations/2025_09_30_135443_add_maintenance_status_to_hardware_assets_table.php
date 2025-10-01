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
            $table->enum('status', [
                'En Almacén',
                'Asignado',
                'En Reparación',
                'Prestado',
                'De Baja',
                'En Mantenimiento'
            ])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hardware_assets', function (Blueprint $table) {
             $table->enum('status', [
                'En Almacén',
                'Asignado',
                'En Reparación',
                'Prestado',
                'De Baja'
            ])->change();
        });
    }
};