<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('electronic_labels', function (Blueprint $table) {
            $table->id();
            
            $table->string('label_type');
            $table->string('series', 2);
            $table->unsignedBigInteger('consecutive'); 
            $table->string('folio')->unique();
            $table->string('unique_identifier', 52)->unique();
            $table->text('full_url');
            $table->date('elaboration_date');
            $table->string('label_batch');
            $table->string('product_name');
            $table->string('product_type');
            $table->decimal('alcohol_content', 4, 1);
            $table->string('capacity');
            $table->string('origin');
            $table->date('packaging_date');
            $table->string('product_batch');
            $table->string('maker_name');
            $table->string('maker_rfc');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('electronic_labels');
    }
};