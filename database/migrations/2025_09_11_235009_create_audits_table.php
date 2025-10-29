<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cs_order_id')->constrained('cs_orders')->onDelete('cascade');
            $table->foreignId('guia_id')->nullable()->constrained('guias')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users');
            
            $table->string('location');
            $table->string('status')->default('Pendiente Almacén');
            
            $table->json('warehouse_audit_data')->nullable();
            $table->json('patio_audit_data')->nullable();
            $table->json('loading_audit_data')->nullable();
            
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};
