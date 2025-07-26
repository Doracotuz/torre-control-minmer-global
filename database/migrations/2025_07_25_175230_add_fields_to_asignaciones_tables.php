<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guias', function (Blueprint $table) {
            $table->string('custodia')->nullable()->after('pedimento');
            $table->string('hora_planeada')->nullable()->after('custodia');
            $table->string('origen', 3)->nullable()->after('hora_planeada');
        });

        Schema::table('facturas', function (Blueprint $table) {
            $table->string('hora_cita')->nullable()->after('botellas');
        });
    }

    public function down(): void
    {
        Schema::table('guias', function (Blueprint $table) {
            $table->dropColumn(['custodia', 'hora_planeada', 'origen']);
        });

        Schema::table('facturas', function (Blueprint $table) {
            $table->dropColumn('hora_cita');
        });
    }
};
