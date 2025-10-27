<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Añadimos la columna 'is_active'
            // Será booleana, por defecto 'true' (Activo)
            // La colocamos después de 'is_client'
            $table->boolean('is_active')->default(true)->after('is_client');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};