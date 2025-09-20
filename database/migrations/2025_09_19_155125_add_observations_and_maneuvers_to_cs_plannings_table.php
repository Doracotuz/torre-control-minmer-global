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
            $table->text('observaciones')->nullable()->after('subtotal');
            $table->integer('maniobras')->nullable()->default(0)->after('observaciones');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cs_plannings', function (Blueprint $table) {
            //
        });
    }
};
