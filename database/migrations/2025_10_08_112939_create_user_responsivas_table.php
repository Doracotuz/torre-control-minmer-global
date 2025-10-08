<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_responsivas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organigram_member_id')->constrained('organigram_members')->onDelete('cascade');
            $table->string('file_path');
            $table->date('generated_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_responsivas');
    }
};