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
        Schema::table('cs_credit_notes', function (Blueprint $table) {
            $table->string('customer_name')->after('cs_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cs_credit_notes', function (Blueprint $table) {
            $table->dropColumn('customer_name');
        });
    }
};