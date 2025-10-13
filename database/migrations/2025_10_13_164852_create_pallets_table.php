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
        Schema::create('pallets', function (Blueprint $table) {
            $table->id();
            $table->string('lpn')->unique()->comment('License Plate Number - ID Único de la Tarima');
            $table->foreignId('location_id')->nullable()->constrained('locations');
            $table->enum('status', ['Receiving', 'Stored', 'In Transit', 'Picking', 'Shipped'])->default('Receiving');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pallets');
    }
};
