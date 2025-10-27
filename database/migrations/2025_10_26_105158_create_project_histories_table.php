<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('project_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action_type'); // 'status_change', 'comment', 'created'
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            $table->text('comment_body')->nullable(); // Para duplicar el comentario aquí
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('project_histories');
    }
};