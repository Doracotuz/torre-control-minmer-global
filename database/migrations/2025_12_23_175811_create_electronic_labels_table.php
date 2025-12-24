<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('electronic_labels', function (Blueprint $table) {
            $table->id();
            $table->string('series', 2);
            $table->unsignedBigInteger('consecutive');
            $table->string('folio')->unique();
            $table->string('unique_identifier', 52)->unique();
            $table->text('full_url');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('electronic_labels');
    }
};