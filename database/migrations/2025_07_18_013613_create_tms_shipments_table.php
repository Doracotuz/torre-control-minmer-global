<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tms_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->nullable()->constrained('tms_routes')->onDelete('set null');
            $table->enum('type', ['Entrega', 'Importacion']);
            $table->string('guide_number')->comment('Guía');
            $table->string('so_number')->nullable()->comment('Sales Order');
            $table->string('pedimento')->nullable();
            $table->string('origin');
            $table->string('destination_type')->nullable()->comment('Cliente final, Traslado');
            $table->text('destination_address')->nullable();
            $table->string('operator')->nullable();
            $table->string('license_plate')->nullable();
            $table->enum('status', ['Por asignar', 'Asignado','Transito', 'Entregado', 'Revisar', 'Cancelado'])->default('Por asignar');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tms_shipments');
    }
};
