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
        Schema::table('cs_orders', function (Blueprint $table) {
            // Modificamos la columna para que tenga un valor por defecto
            $table->string('audit_status')->default('Pendiente Almacén')->change();
        });
    }

    public function down()
    {
        Schema::table('cs_orders', function (Blueprint $table) {
            // Revertimos el cambio si es necesario
            $table->string('audit_status')->default(null)->change();
        });
    }
};
