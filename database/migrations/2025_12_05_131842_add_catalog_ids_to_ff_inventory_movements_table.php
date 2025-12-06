<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('ff_inventory_movements', function (Blueprint $table) {
            $table->foreignId('ff_client_id')->nullable()->constrained('ff_clients')->nullOnDelete();
            $table->foreignId('ff_client_branch_id')->nullable()->constrained('ff_client_branches')->nullOnDelete();
            $table->foreignId('ff_sales_channel_id')->nullable()->constrained('ff_sales_channels')->nullOnDelete();
            $table->foreignId('ff_transport_line_id')->nullable()->constrained('ff_transport_lines')->nullOnDelete();
            $table->foreignId('ff_payment_condition_id')->nullable()->constrained('ff_payment_conditions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ff_inventory_movements', function (Blueprint $table) {
            //
        });
    }
};
