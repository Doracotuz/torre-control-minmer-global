<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ff_products', function (Blueprint $table) {
            $table->renameColumn('price', 'unit_price');
            $table->dropColumn('regular_price');
            
            $table->integer('pieces_per_box')->nullable()->after('type');
            $table->decimal('length', 10, 2)->nullable()->after('pieces_per_box');
            $table->decimal('width', 10, 2)->nullable()->after('length');
            $table->decimal('height', 10, 2)->nullable()->after('width');
            $table->string('upc')->nullable()->after('height');
        });
    }

    public function down(): void
    {
        Schema::table('ff_products', function (Blueprint $table) {
            $table->renameColumn('unit_price', 'price');
            $table->decimal('regular_price', 10, 2)->nullable();
            
            $table->dropColumn(['pieces_per_box', 'length', 'width', 'height', 'upc']);
        });
    }
};