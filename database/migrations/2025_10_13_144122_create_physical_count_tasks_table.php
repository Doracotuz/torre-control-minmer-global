<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('physical_count_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('physical_count_session_id')->constrained('physical_count_sessions')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('location_id')->constrained('locations');
            $table->unsignedInteger('expected_quantity');
            $table->enum('status', ['pending', 'counted', 'discrepancy', 'resolved'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('physical_count_tasks');
    }
};
