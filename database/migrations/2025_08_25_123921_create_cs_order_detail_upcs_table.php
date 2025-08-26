<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cs_order_detail_upcs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cs_order_detail_id')->constrained('cs_order_details')->onDelete('cascade');
            $table->string('upc');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('cs_order_detail_upcs');
    }
};