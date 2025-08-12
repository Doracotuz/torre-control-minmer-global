<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cs_customers', function (Blueprint $table) {
            $table->id();
            $table->string('client_id'); // ID Alfanumérico del Cliente
            $table->string('name');
            $table->enum('channel', ['Corporate', 'Especialista', 'Moderno', 'On', 'On trade', 'POSM', 'Private']);
            
            $table->foreignId('created_by_user_id')->constrained('users');
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users');
            $table->timestamps();

            // Un cliente se puede repetir si el canal es diferente
            $table->unique(['client_id', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cs_customers');
    }
};
