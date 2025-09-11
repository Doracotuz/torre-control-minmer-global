<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('cs_customers', function (Blueprint $table) {
            // Columna para guardar las especificaciones como un objeto JSON
            $table->json('delivery_specifications')->nullable()->after('channel');
        });
    }

    // Asegúrate de que el método down() pueda revertirlo
    public function down()
    {
        Schema::table('cs_customers', function (Blueprint $table) {
            $table->dropColumn('delivery_specifications');
        });
    }
};
