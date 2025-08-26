<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('audit_incidencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guia_id')->constrained('guias')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->string('tipo_incidencia');
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('audit_incidencias');
    }
};