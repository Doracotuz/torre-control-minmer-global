<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE hardware_assets MODIFY COLUMN status VARCHAR(191) NOT NULL DEFAULT 'En Almacén'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE hardware_assets MODIFY COLUMN status ENUM('En Almacén', 'Asignado', 'En Reparación', 'Prestado', 'De Baja', 'En Mantenimiento') NOT NULL DEFAULT 'En Almacén'");
    }
};