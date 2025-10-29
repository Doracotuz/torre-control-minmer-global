<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('eventos')
            ->whereNotNull('url_evidencia')
            ->where('url_evidencia', '!=', '')
            ->update(['url_evidencia' => DB::raw("JSON_ARRAY(url_evidencia)")]);

        Schema::table('eventos', function (Blueprint $table) {
            $table->json('url_evidencia')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->string('url_evidencia')->nullable()->change();
        });
    }
};