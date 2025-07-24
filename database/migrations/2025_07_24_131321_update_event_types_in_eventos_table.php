<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('eventos')
            ->where('subtipo', 'Percance')
            ->update(['tipo' => 'Incidencias']);

        DB::statement("ALTER TABLE eventos MODIFY COLUMN tipo ENUM('Entrega', 'Notificacion', 'Incidencias', 'Sistema') NOT NULL");
    }

    public function down(): void
    {

        DB::statement("ALTER TABLE eventos MODIFY COLUMN tipo ENUM('Entrega', 'Notificacion', 'Sistema') NOT NULL");

        DB::table('eventos')
            ->where('tipo', 'Incidencias')
            ->where('subtipo', 'Percance')
            ->update(['tipo' => 'Notificacion']);
    }
};