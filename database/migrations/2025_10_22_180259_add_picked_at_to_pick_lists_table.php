<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('pick_lists', function (Blueprint $table) {
            $table->timestamp('picked_at')->nullable()->after('status'); 
        });
    }
    public function down(): void {
        Schema::table('pick_lists', function (Blueprint $table) {
            $table->dropColumn('picked_at');
        });
    }
};
