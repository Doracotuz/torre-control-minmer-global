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
        Schema::table('pick_list_items', function (Blueprint $table) {
            // Añade la columna quality_id (puede ser nullable o requerir un foreign key)
            // Asegúrate que el tipo de dato coincida con tu tabla 'qualities'
            $table->foreignId('quality_id')->nullable()->after('location_id')->constrained('qualities')->onDelete('set null'); 
            // O si prefieres que sea obligatoria y borrar en cascada (cuidado):
            // $table->foreignId('quality_id')->after('location_id')->constrained('qualities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pick_list_items', function (Blueprint $table) {
            // Asegúrate de quitar la llave foránea antes de la columna
            $table->dropForeign(['quality_id']); 
            $table->dropColumn('quality_id');
        });
    }
};
