<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ff_cart_items', function (Blueprint $table) {
            $table->unsignedBigInteger('ff_quality_id')->nullable()->after('ff_warehouse_id');
            $table->foreign('ff_quality_id')->references('id')->on('ff_qualities')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('ff_cart_items', function (Blueprint $table) {
            $table->dropForeign(['ff_quality_id']);
            $table->dropColumn('ff_quality_id');
        });
    }
};
