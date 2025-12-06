<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ff_inventory_movements', function (Blueprint $table) {
            $table->string('order_type')->default('normal');
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->boolean('is_loan_returned')->default(false);
            $table->timestamp('loan_returned_at')->nullable();
            $table->string('evidence_path_1')->nullable();
            $table->string('evidence_path_2')->nullable();
            $table->string('evidence_path_3')->nullable();
        });
    }

    public function down()
    {
        Schema::table('ff_inventory_movements', function (Blueprint $table) {
            $table->dropColumn([
                'order_type', 
                'discount_percentage', 
                'is_loan_returned', 
                'loan_returned_at',
                'evidence_path_1', 
                'evidence_path_2', 
                'evidence_path_3'
            ]);
        });
    }
};