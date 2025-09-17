<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('guias', function (Blueprint $table) {
            $table->string('transporte')->nullable()->after('placas');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guias_and_plannings_tables', function (Blueprint $table) {
            //
        });
    }
};
