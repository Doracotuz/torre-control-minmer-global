<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cs_order_events', function (Blueprint $table) {
            // Permitir que user_id sea nulo
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // Eliminar la restricción de clave foránea existente
            $table->dropForeign('cs_order_events_user_id_foreign');

            // Volver a crear la restricción con la nueva regla
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('cs_order_events', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users');
        });
    }
};