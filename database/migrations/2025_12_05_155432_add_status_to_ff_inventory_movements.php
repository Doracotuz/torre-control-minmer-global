<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ff_inventory_movements', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('order_type');
            $table->text('rejection_reason')->nullable()->after('status');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('ff_inventory_movements', function (Blueprint $table) {
            $table->dropColumn(['status', 'rejection_reason', 'approved_by', 'approved_at']);
        });
    }
};