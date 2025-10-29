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
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hardware_asset_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['Preventivo', 'Reparación']);
            $table->string('supplier')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('diagnosis');
            $table->text('actions_taken')->nullable();
            $table->text('parts_used')->nullable();
            $table->decimal('cost', 8, 2)->nullable();
            $table->foreignId('substitute_asset_id')->nullable()->constrained('hardware_assets')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
