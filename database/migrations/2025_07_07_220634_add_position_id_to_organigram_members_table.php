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
        Schema::table('organigram_members', function (Blueprint $table) {
            // Eliminar la columna 'position' si existe y no tiene datos importantes
            // Si tienes datos existentes en 'position' y quieres migrarlos a 'organigram_positions',
            // el proceso es más complejo y requeriría un seeder o script de migración de datos.
            // Por ahora, asumimos que puedes eliminarla o que no hay datos que mantener.
            $table->dropColumn('position'); // Elimina la columna de texto libre

            // Añadir la nueva columna de clave foránea
            $table->foreignId('position_id')
                  ->nullable() // Permite nulos temporalmente si es necesario, o hazlo required si siempre debe tener posición
                  ->constrained('organigram_positions') // Relación con la tabla 'organigram_positions'
                  ->onDelete('set null'); // O 'restrict', 'cascade' según tu lógica
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organigram_members', function (Blueprint $table) {
            $table->dropForeign(['position_id']); // Elimina la clave foránea
            $table->dropColumn('position_id'); // Elimina la nueva columna

            // Si eliminaste 'position' en 'up', recréala si es necesario en 'down' para rollback
            $table->string('position')->nullable(); // Regresa la columna de texto libre
        });
    }
};