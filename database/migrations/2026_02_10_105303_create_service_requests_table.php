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
        Schema::create('wms_service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('folio')->unique();
            $table->foreignId('area_id')->constrained('areas'); // Cliente
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->foreignId('user_id')->constrained('users'); // Quien creó la solicitud
            $table->enum('status', ['pending', 'completed', 'invoiced', 'cancelled'])->default('pending');
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wms_service_requests');
    }
};
