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
        Schema::table('cs_plannings', function (Blueprint $table) {
            $table->string('destino')->nullable()->change();
            $table->date('fecha_entrega')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cs_plannings', function (Blueprint $table) {
            $table->string('destino')->nullable(false)->change();
            $table->date('fecha_entrega')->nullable(false)->change();
        });
    }
};