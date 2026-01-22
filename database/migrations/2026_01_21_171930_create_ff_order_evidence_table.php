<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ff_order_evidences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('folio')->index();
            $table->string('filename');
            $table->string('path');
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ff_order_evidence');
    }
};
