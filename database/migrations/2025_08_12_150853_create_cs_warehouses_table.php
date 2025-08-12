<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cs_warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('warehouse_id')->unique();
            $table->string('name');
            
            $table->foreignId('created_by_user_id')->constrained('users');
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cs_warehouses');
    }
};
