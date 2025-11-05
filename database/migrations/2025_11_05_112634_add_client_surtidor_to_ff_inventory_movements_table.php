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
            $table->string('client_name')->nullable()->after('user_id');
            $table->string('surtidor_name')->nullable()->after('client_name');
        });
    }

    public function down(): void
    {
        Schema::table('ff_inventory_movements', function (Blueprint $table) {
            $table->dropColumn('client_name');
            $table->dropColumn('surtidor_name');
        });
    }
};