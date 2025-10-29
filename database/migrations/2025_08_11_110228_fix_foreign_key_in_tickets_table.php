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
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign('tickets_category_id_foreign');
            $table->foreign('ticket_sub_category_id')
                  ->references('id')
                  ->on('ticket_sub_categories')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['ticket_sub_category_id']);

            $table->foreign('ticket_sub_category_id', 'tickets_category_id_foreign')
                  ->references('id')
                  ->on('ticket_categories')
                  ->onDelete('set null');
        });
    }
};