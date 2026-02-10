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
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'document_invoice')) {
                $table->string('document_invoice')->nullable()->after('container_number');
            }
            if (!Schema::hasColumn('purchase_orders', 'pedimento_a4')) {
                $table->string('pedimento_a4')->nullable()->after('document_invoice');
            }
            if (!Schema::hasColumn('purchase_orders', 'pedimento_g1')) {
                $table->string('pedimento_g1')->nullable()->after('pedimento_a4');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down needed as we are ensuring existence
    }
};
