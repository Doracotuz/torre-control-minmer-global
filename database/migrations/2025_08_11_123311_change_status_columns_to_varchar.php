<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Asegúrate de que esta línea esté presente

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // --- INICIA CAMBIO: Se usa un comando SQL directo para evitar la dependencia ---

        // Cambia la columna 'estatus' en la tabla 'guias' a VARCHAR(50)
        DB::statement("ALTER TABLE guias MODIFY COLUMN estatus VARCHAR(50) NOT NULL DEFAULT 'En Espera'");

        // Cambia la columna 'estatus_entrega' en la tabla 'facturas' a VARCHAR(50)
        DB::statement("ALTER TABLE facturas MODIFY COLUMN estatus_entrega VARCHAR(50) NOT NULL DEFAULT 'Pendiente'");
        
        // --- TERMINA CAMBIO ---
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // El método para revertir los cambios no cambia
        DB::statement("ALTER TABLE guias MODIFY estatus ENUM('En Espera', 'Planeada', 'En Transito', 'Completada')");
        DB::statement("ALTER TABLE facturas MODIFY estatus_entrega ENUM('Pendiente', 'Entregada', 'No Entregada')");
    }
};