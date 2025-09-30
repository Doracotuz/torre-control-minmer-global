<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('organigram_members', function (Blueprint $table) {
            // El user_id es nullable (un miembro puede no tener usuario) y único (un usuario solo puede ser un miembro)
            $table->foreignId('user_id')->nullable()->unique()->after('id')->constrained('users')->onDelete('set null');
        });
    }

    public function down(): void {
        Schema::table('organigram_members', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};