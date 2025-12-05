<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $tables = ['ff_clients', 'ff_sales_channels', 'ff_transport_lines', 'ff_payment_conditions'];

        foreach ($tables as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ff_payment_conditions');
        Schema::dropIfExists('ff_transport_lines');
        Schema::dropIfExists('ff_sales_channels');
        Schema::dropIfExists('ff_clients');
    }
};