<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

try {
    if (Schema::hasTable('wms_service_requests')) {
        Schema::drop('wms_service_requests');
        echo "Dropped existing table.\n";
    }

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
    echo "Table created successfully.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
