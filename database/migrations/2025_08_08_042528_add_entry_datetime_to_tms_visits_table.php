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
        Schema::table('tms_visits', function (Blueprint $table) {
            // Añadimos la nueva columna después de 'visit_datetime'
            // Es 'nullable' porque estará vacía hasta que el visitante ingrese.
            $table->dateTime('entry_datetime')->nullable()->after('visit_datetime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tms_visits', function (Blueprint $table) {
            //
        });
    }
};
