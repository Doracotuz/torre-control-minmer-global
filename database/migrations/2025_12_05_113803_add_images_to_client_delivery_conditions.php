<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ff_client_delivery_conditions', function (Blueprint $table) {
            $table->string('prep_img_1')->nullable();
            $table->string('prep_img_2')->nullable();
            $table->string('prep_img_3')->nullable();

            $table->string('doc_img_1')->nullable();
            $table->string('doc_img_2')->nullable();
            $table->string('doc_img_3')->nullable();

            $table->string('evid_img_1')->nullable();
            $table->string('evid_img_2')->nullable();
            $table->string('evid_img_3')->nullable();
        });
    }

    public function down()
    {
        Schema::table('ff_client_delivery_conditions', function (Blueprint $table) {
            $table->dropColumn([
                'prep_img_1', 'prep_img_2', 'prep_img_3',
                'doc_img_1', 'doc_img_2', 'doc_img_3',
                'evid_img_1', 'evid_img_2', 'evid_img_3'
            ]);
        });
    }
};