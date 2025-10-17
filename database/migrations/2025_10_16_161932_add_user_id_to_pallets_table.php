<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pallets', function (Blueprint $table) {
            // Añade la columna user_id, la relaciona con la tabla users y permite que sea nula
            $table->foreignId('user_id')->nullable()->after('location_id')->constrained('users');
        });
    }

    public function down(): void
    {
        Schema::table('pallets', function (Blueprint $table) {
            // Esto permite revertir el cambio si es necesario
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};