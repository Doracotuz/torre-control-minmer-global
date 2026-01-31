<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('physical_count_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('physical_count_sessions', 'area_id')) {
                $table->foreignId('area_id')->nullable()->after('warehouse_id')->constrained('areas')->onDelete('set null');
            }
            if (!Schema::hasColumn('physical_count_sessions', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('updated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('physical_count_sessions', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropColumn(['area_id', 'completed_at']);
        });
    }
};