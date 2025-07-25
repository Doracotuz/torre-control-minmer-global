<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('organigram_member_skill', function (Blueprint $table) {
            $table->foreignId('organigram_member_id')->constrained('organigram_members')->onDelete('cascade');
            $table->foreignId('organigram_skill_id')->constrained('organigram_skills')->onDelete('cascade');
            $table->primary(['organigram_member_id', 'organigram_skill_id']); // Clave primaria compuesta
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organigram_member_skill');
    }
};