<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ff_inventory_movements', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('client_name');
            $table->string('client_phone')->nullable()->after('company_name');
            $table->text('address')->nullable()->after('client_phone');
            $table->string('locality')->nullable()->after('address');
            $table->dateTime('delivery_date')->nullable()->after('locality');
        });
    }

    public function down(): void
    {
        Schema::table('ff_inventory_movements', function (Blueprint $table) {
            $table->dropColumn(['company_name', 'client_phone', 'address', 'locality', 'delivery_date']);
        });
    }
};