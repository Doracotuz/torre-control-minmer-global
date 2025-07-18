<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tms_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('tms_shipments')->onDelete('cascade');
            $table->string('invoice_number')->comment('Factura');
            $table->integer('box_quantity')->nullable();
            $table->integer('bottle_quantity')->nullable();
            $table->enum('status', ['Pendiente', 'Entregado', 'No entregado'])->default('Pendiente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tms_invoices');
    }
};