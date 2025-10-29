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
        Schema::create('tms_visits', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_name');
            $table->string('visitor_last_name');
            $table->string('company')->nullable();
            $table->string('email');
            $table->string('vehicle_make')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->string('license_plate')->nullable();
            $table->dateTime('visit_datetime');
            $table->text('reason');
            $table->json('companions')->nullable();
            $table->string('qr_code_token')->unique();
            $table->enum('status', ['Programada', 'Ingresado', 'No ingresado', 'Cancelada'])->default('Programada');
            $table->foreignId('created_by_user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
