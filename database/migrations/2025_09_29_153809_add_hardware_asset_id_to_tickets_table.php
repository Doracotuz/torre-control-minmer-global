<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('hardware_asset_id')
                  ->nullable()
                  ->after('ticket_sub_category_id') // Lo colocamos después de la categoría
                  ->constrained('hardware_assets')
                  ->onDelete('set null'); // Si se borra el activo, el ticket no se borra
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['hardware_asset_id']);
            $table->dropColumn('hardware_asset_id');
        });
    }
};