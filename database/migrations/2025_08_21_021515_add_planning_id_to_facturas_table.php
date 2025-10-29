<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('facturas', function (Blueprint $table) {
            $table->foreignId('cs_planning_id')->nullable()->constrained('cs_plannings')->after('guia_id');
        });
    }
    public function down(): void {
        Schema::table('facturas', function (Blueprint $table) {
            $table->dropForeign(['cs_planning_id']);
            $table->dropColumn('cs_planning_id');
        });
    }
};