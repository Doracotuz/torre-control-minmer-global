<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('location_id')->constrained('locations');
            
            // LPN (PalletItem) afectado, si aplica
            $table->foreignId('pallet_item_id')->nullable()->constrained('pallet_items');

            // Cantidad del movimiento: + para entradas, - para salidas
            $table->integer('quantity'); 
            
            // Tipo de movimiento para filtrado
            $table->string('movement_type'); // Ej: RECEPCION, PICKING, AJUSTE-MANUAL, TRANSFER-IN

            // ID y Tipo del documento que originó el movimiento (PO, PickList, Ajuste, etc.)
            $table->morphs('source'); 

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};