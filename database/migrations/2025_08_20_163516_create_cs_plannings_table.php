<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cs_plannings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cs_order_id')->constrained('cs_orders')->onDelete('cascade');
            $table->date('fecha_carga')->nullable();
            $table->time('hora_carga')->nullable();
            $table->date('fecha_entrega')->nullable();
            $table->string('origen');
            $table->text('direccion');
            $table->string('razon_social');
            $table->string('hora_cita')->nullable();
            $table->string('so_number');
            $table->string('factura')->nullable();
            $table->integer('pzs');
            $table->integer('cajas');
            $table->decimal('subtotal', 10, 2);
            $table->string('canal');
            $table->enum('capacidad', ['1 Ton', '1.5 Ton', '3.5 Ton', '4.5 Ton', 'Torthon', 'Rabón', 'Mudancero', 'Trailer 48"', 'Trailer 53"', 'Automovil', 'Motocicleta', 'Paqueteria', 'Contenedor 20"', 'Contenedor 40"', 'Contenedor 48"', 'Contenedor 53"'])->nullable();
            $table->string('transporte')->nullable();
            $table->string('destino');
            $table->enum('estado', ['Aguascalientes', 'Baja California', 'Baja California Sur', 'Campeche', 'Chiapas', 'Chihuahua', 'Ciudad de México', 'Coahuila', 'Colima', 'Durango', 'Estado de México', 'Guanajuato', 'Guerrero', 'Hidalgo', 'Jalisco', 'Michoacán', 'Morelos', 'Nayarit', 'Nuevo León', 'Oaxaca', 'Puebla', 'Querétaro', 'Quintana Roo', 'San Luis Potosí', 'Sinaloa', 'Sonora', 'Tabasco', 'Tamaulipas', 'Tlaxcala', 'Veracruz', 'Yucatán', 'Zacatecas'])->nullable();
            $table->enum('servicio', ['Local', 'Foraneo', 'Ejecutivo'])->nullable();
            $table->enum('region', ['Bajio', 'Centro', 'Noreste', 'Pacifico', 'Sureste'])->nullable();
            $table->enum('tipo_ruta', ['Consolidado', 'Dedicado', 'Directo'])->nullable();
            $table->enum('devolucion', ['Si', 'No'])->nullable();
            $table->enum('custodia', ['Sepsa', 'Planus', 'Ninguna'])->nullable();
            $table->string('operador')->nullable();
            $table->string('placas')->nullable();
            $table->string('telefono')->nullable();
            $table->string('estatus_de_entrega')->nullable();
            $table->enum('urgente', ['Si', 'No'])->nullable();
            $table->string('status')->default('En Espera');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cs_plannings');
    }
};