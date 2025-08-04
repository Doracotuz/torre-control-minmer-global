<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tms_visits', function (Blueprint $table) {
            $table->dateTime('exit_datetime')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('tms_visits', function (Blueprint $table) {
            $table->dropColumn('exit_datetime');
        });
    }
};