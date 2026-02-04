<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ff_inventory_movements', function (Blueprint $table) {
            
            $table->foreignId('ff_quality_id')
                  ->nullable()
                  ->after('ff_warehouse_id') 
                  ->constrained('ff_qualities')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ff_inventory_movements', function (Blueprint $table) {
            $table->dropForeign(['ff_quality_id']);
            $table->dropColumn('ff_quality_id');
        });
    }
};