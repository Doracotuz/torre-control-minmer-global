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
        Schema::create('wms_value_added_services', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('description');
            $table->enum('type', ['consumable', 'service']);
            $table->decimal('cost', 10, 2);
            $table->timestamps();
        });

        Schema::create('wms_value_added_service_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('value_added_service_id')
                  ->constrained('wms_value_added_services', 'id', 'wms_vas_assignments_vas_id_fk')
                  ->onDelete('cascade');
            $table->string('assignable_type');
            $table->unsignedBigInteger('assignable_id');
            $table->index(['assignable_type', 'assignable_id'], 'wms_vas_assign_index');
            $table->integer('quantity')->default(1);
            $table->decimal('cost_snapshot', 10, 2); // To keep history if service price changes
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wms_value_added_service_assignments');
        Schema::dropIfExists('wms_value_added_services');
    }
};
