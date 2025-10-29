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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('so_number')->unique()->comment('Sales Order Number');
            $table->string('invoice_number')->nullable()->unique()->comment('Invoice Number');
            $table->string('customer_name');
            $table->foreignId('user_id')->comment('User who created the SO')->constrained('users');
            $table->date('order_date');
            $table->enum('status', ['Pending', 'Picking', 'Packed', 'Shipped', 'Cancelled'])->default('Pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
