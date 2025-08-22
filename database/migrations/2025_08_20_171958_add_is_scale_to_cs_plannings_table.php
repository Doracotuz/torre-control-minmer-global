<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cs_plannings', function (Blueprint $table) {
            // Esta columna nos ayudará a identificar las rutas generadas por escalas
            $table->boolean('is_scale')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('cs_plannings', function (Blueprint $table) {
            $table->dropColumn('is_scale');
        });
    }
};