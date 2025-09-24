<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            // Hacemos que la columna user_id pueda ser nula
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // Eliminamos la restricción de clave foránea existente
            // El nombre 'audits_user_id_foreign' lo tomamos del error que recibiste
            $table->dropForeign('audits_user_id_foreign');

            // La volvemos a crear con la regla 'onDelete('set null')'
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users');
        });
    }
};