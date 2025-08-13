<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cs_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cs_order_id')->unique()->constrained('cs_orders')->onDelete('cascade');
            $table->string('status')->default('Pendiente');
            $table->foreignId('planned_by_user_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cs_plans');
    }
};
