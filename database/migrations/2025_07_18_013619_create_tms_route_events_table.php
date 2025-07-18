<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tms_route_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('tms_routes')->onDelete('cascade');
            $table->enum('event_type', ['En pension', 'Alimentos', 'Altercado', 'Entrega', 'No Entregado', 'Inicio de Ruta']);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tms_route_events');
    }
};