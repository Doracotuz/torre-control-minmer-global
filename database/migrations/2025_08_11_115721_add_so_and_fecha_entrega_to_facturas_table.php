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
        Schema::table('facturas', function (Blueprint $table) {
            // Se añaden las dos nuevas columnas a la tabla 'facturas'
            $table->integer('so')->nullable()->after('hora_cita');
            $table->date('fecha_entrega')->nullable()->after('so');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            // Esto permite revertir los cambios si es necesario
            $table->dropColumn(['so', 'fecha_entrega']);
        });
    }
};