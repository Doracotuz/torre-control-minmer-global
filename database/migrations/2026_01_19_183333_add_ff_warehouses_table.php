<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ff_warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('description');
            $table->text('address');
            $table->string('phone');
            $table->foreignId('area_id')->constrained('areas');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['code', 'area_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ff_warehouses');
    }
};