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
        Schema::table('tickets', function (Blueprint $table) {
            // 1. Eliminar la restricción de clave foránea incorrecta.
            // Laravel por defecto la nombra: tickets_category_id_foreign
            $table->dropForeign('tickets_category_id_foreign');

            // 2. Añadir la nueva restricción correcta.
            // Ahora apunta desde 'ticket_sub_category_id' a la tabla 'ticket_sub_categories'.
            $table->foreign('ticket_sub_category_id')
                  ->references('id')
                  ->on('ticket_sub_categories')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Esto revierte los cambios si es necesario.
            $table->dropForeign(['ticket_sub_category_id']);

            $table->foreign('ticket_sub_category_id', 'tickets_category_id_foreign')
                  ->references('id')
                  ->on('ticket_categories')
                  ->onDelete('set null');
        });
    }
};