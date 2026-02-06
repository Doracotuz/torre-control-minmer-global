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
        Schema::create('sync_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type')->comment('Error type: SKU Mismatch, Missing Brand, etc.');
            $table->text('message');
            $table->json('payload')->nullable()->comment('Context data causing the error');
            $table->boolean('resolved')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_notifications');
    }
};
