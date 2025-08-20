<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cs_credit_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cs_order_id')->constrained('cs_orders')->onDelete('cascade');
            $table->string('request_type');
            $table->date('capture_date');
            $table->string('invoice')->nullable();
            $table->string('customer_id')->nullable();
            $table->foreignId('warehouse_id')->constrained('cs_warehouses');
            $table->string('customs_document');
            $table->string('cause');
            $table->text('cause_description');
            $table->date('credit_note_date')->nullable();
            $table->string('credit_note')->nullable();
            $table->date('delivery_date')->nullable();
            $table->date('arrival_date')->nullable();
            $table->date('asn_close_date')->nullable();
            $table->string('asn')->nullable();
            $table->text('observations')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cs_credit_notes');
    }
};