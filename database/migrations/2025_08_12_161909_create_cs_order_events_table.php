<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cs_order_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cs_order_id')->constrained('cs_orders')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->string('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cs_order_events');
    }
};
