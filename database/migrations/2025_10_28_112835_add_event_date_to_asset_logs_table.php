<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_logs', function (Blueprint $table) {
            $table->dateTime('event_date')->nullable()->after('loggable_type');
        });

        \Illuminate\Support\Facades\DB::statement('UPDATE asset_logs SET event_date = created_at');

        Schema::table('asset_logs', function (Blueprint $table) {
            $table->dateTime('event_date')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('asset_logs', function (Blueprint $table) {
            $table->dropColumn('event_date');
        });
    }
};