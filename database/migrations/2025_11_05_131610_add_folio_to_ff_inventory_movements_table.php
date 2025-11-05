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
        Schema::table('ff_inventory_movements', function (Blueprint $table) {
            $table->unsignedBigInteger('folio')->default(0)->after('surtidor_name');
            $table->index('folio'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ff_inventory_movements', function (Blueprint $table) {
            $table->dropColumn('folio');
        });
    }
};