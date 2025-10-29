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
        $tables = [
            'rutas' => ['nombre' => 'VARCHAR(255)', 'region' => 'VARCHAR(255)'],
            'paradas' => ['nombre_lugar' => 'VARCHAR(255)'],
            'guias' => ['guia' => 'VARCHAR(255)', 'operador' => 'VARCHAR(255)', 'placas' => 'VARCHAR(255)', 'pedimento' => 'VARCHAR(255)'],
            'facturas' => ['numero_factura' => 'VARCHAR(255)', 'destino' => 'TEXT'],
            'eventos' => ['subtipo' => 'VARCHAR(255)', 'nota' => 'TEXT']
        ];

        foreach ($tables as $tableName => $columns) {
            DB::statement("ALTER TABLE `{$tableName}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            foreach ($columns as $columnName => $columnType) {
                DB::statement("ALTER TABLE `{$tableName}` MODIFY `{$columnName}` {$columnType} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};