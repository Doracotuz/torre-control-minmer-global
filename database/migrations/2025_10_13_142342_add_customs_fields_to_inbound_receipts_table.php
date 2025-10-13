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
        Schema::table('inbound_receipts', function (Blueprint $table) {
            $table->string('container_number')->nullable()->after('user_id');
            $table->string('pedimento_a4')->nullable()->after('container_number');
            $table->string('pedimento_g1')->nullable()->after('pedimento_a4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inbound_receipts', function (Blueprint $table) {
            $table->dropColumn(['container_number', 'pedimento_a4', 'pedimento_g1']);
        });
    }
};
