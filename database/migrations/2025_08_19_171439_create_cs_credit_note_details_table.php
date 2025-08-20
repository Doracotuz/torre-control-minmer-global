<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cs_credit_note_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cs_credit_note_id')->constrained('cs_credit_notes')->onDelete('cascade');
            $table->string('sku');
            $table->integer('quantity_returned');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cs_credit_note_details');
    }
};