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
        Schema::create('physical_count_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('physical_count_task_id')->constrained('physical_count_tasks')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->unsignedTinyInteger('count_number'); // 1, 2, or 3
            $table->unsignedInteger('counted_quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('physical_count_records');
    }
};
