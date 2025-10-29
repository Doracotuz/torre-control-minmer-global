<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('hardware_assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_tag')->unique();
            $table->string('serial_number')->unique();
            $table->foreignId('hardware_model_id')->constrained('hardware_models');
            $table->foreignId('site_id')->constrained('sites');
            $table->enum('status', ['En Almacén', 'Asignado', 'En Reparación', 'Prestado', 'De Baja'])->default('En Almacén');
            $table->date('purchase_date')->nullable();
            $table->date('warranty_end_date')->nullable();
            
            $table->string('cpu')->nullable();
            $table->string('ram')->nullable();
            $table->string('storage')->nullable();
            $table->string('mac_address')->nullable()->unique();
            
            $table->enum('phone_plan_type', ['Prepago', 'Plan'])->nullable();
            $table->string('phone_number')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('hardware_assets');
    }
};