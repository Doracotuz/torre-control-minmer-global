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
        Schema::table('guias', function (Blueprint $table) {
            // Añadimos el nuevo campo de tipo fecha, puede ser nulo
            $table->date('fecha_asignacion')->nullable()->after('origen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guias', function (Blueprint $table) {
            $table->dropColumn('fecha_asignacion');
        });
    }
};