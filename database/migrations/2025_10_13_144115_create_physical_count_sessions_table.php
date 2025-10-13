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
        Schema::create('physical_count_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['cycle', 'full']);
            $table->enum('status', ['pending', 'in_progress', 'completed', 'closed'])->default('pending');
            $table->foreignId('user_id')->comment('User who created the session')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('physical_count_sessions');
    }
};
