<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('software_licenses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('license_key')->nullable(); // Encriptado
            $table->integer('total_seats'); // Número de licencias compradas
            $table->date('purchase_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('software_licenses');
    }
};