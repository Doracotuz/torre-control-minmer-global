<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_types', function (Blueprint $table) {
            $table->foreignId('area_id')
                  ->nullable()
                  ->after('name')
                  ->constrained('areas')
                  ->nullOnDelete();
        });

        Schema::table('qualities', function (Blueprint $table) {
            $table->foreignId('area_id')
                  ->nullable()
                  ->after('name')
                  ->constrained('areas')
                  ->nullOnDelete();
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->foreignId('area_id')
                  ->nullable()
                  ->after('name')
                  ->constrained('areas')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_types', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropColumn('area_id');
        });

        Schema::table('qualities', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropColumn('area_id');
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropColumn('area_id');
        });
    }
};