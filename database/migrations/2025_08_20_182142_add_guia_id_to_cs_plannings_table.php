<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('cs_plannings', function (Blueprint $table) {
            // Columna para relacionar con la guía
            $table->foreignId('guia_id')->nullable()->constrained('guias')->after('cs_order_id');
        });
    }
    public function down(): void {
        Schema::table('cs_plannings', function (Blueprint $table) {
            $table->dropForeign(['guia_id']);
            $table->dropColumn('guia_id');
        });
    }
};