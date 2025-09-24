<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_incidencias', function (Blueprint $table) {
            // Primero, hacemos que la columna user_id pueda ser nula
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // Luego, eliminamos la restricción de clave foránea existente
            $table->dropForeign('audit_incidencias_user_id_foreign');

            // Finalmente, la volvemos a crear con la regla 'onDelete('set null')'
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null'); // <-- La magia está aquí
        });
    }

    public function down(): void
    {
        // Esto revierte los cambios si es necesario
        Schema::table('audit_incidencias', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict'); // O la regla que tenías por defecto
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};