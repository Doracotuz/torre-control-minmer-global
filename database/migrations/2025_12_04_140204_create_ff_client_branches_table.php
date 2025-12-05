<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ff_client_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ff_client_id')->constrained('ff_clients')->onDelete('cascade');
            $table->string('name');
            $table->string('address');
            $table->string('schedule');
            $table->string('phone');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ff_client_branches');
    }
};