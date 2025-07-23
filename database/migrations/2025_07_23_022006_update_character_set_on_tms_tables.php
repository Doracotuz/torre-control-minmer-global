<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Lista de tablas y sus columnas de texto a actualizar
        $tables = [
            'rutas' => ['nombre', 'region'],
            'paradas' => ['nombre_lugar'],
            'guias' => ['guia', 'operador', 'placas', 'pedimento'],
            'facturas' => ['numero_factura', 'destino'],
            'eventos' => ['subtipo', 'nota', 'url_evidencia']
        ];

        // Cambiamos la codificación para cada tabla y columna
        foreach ($tables as $tableName => $columns) {
            // Convierte toda la tabla a utf8mb4
            DB::statement("ALTER TABLE {$tableName} CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Asegura que las columnas de texto también tengan la codificación correcta
            foreach ($columns as $column) {
                DB::statement("ALTER TABLE {$tableName} MODIFY {$column} VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No es necesario revertir estos cambios de codificación
    }
};