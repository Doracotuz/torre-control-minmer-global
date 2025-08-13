<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cs_orders', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_order')->nullable()->comment('Orden de compra (BP Reference No.)');
            $table->string('bt_oc')->nullable();
            $table->date('creation_date')->comment('Fecha de Creacion (Posting Date)');
            $table->unsignedBigInteger('so_number')->unique()->comment('SO (Document Number)');
            $table->date('authorization_date')->comment('Fecha en que se ejecuta el proceso');
            $table->string('invoice_number')->nullable()->comment('Factura');
            $table->date('invoice_date')->nullable()->comment('Fecha factura');
            $table->string('customer_name')->comment('Razon social (Customer/Vendor Name)');
            $table->string('origin_warehouse')->comment('Almacen origen');
            $table->integer('total_bottles')->comment('No. Botellas');
            $table->decimal('total_boxes', 8, 2)->comment('No. Cajas');
            $table->decimal('subtotal', 15, 2);
            $table->string('channel')->comment('Canal');
            $table->date('delivery_date')->nullable()->comment('Fecha de entrega');
            $table->string('schedule')->nullable()->comment('Horario');
            $table->string('client_contact')->nullable()->comment('Cliente');
            $table->text('shipping_address')->nullable()->comment('Dirección (Ship To)');
            $table->string('destination_locality')->nullable()->comment('Localidad Destino');
            $table->string('executive')->nullable()->comment('Ejecutivo');
            $table->string('status')->default('Pendiente');
            $table->text('observations')->nullable();
            $table->date('evidence_reception_date')->nullable();
            $table->date('evidence_cutoff_date')->nullable();
            $table->foreignId('created_by_user_id')->constrained('users');
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cs_orders');
    }
};
