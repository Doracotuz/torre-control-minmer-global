<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->string('photo_1_path')->nullable()->after('cost');
            $table->string('photo_2_path')->nullable()->after('photo_1_path');
            $table->string('photo_3_path')->nullable()->after('photo_2_path');
        });
    }

    public function down()
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropColumn(['photo_1_path', 'photo_2_path', 'photo_3_path']);
        });
    }
};
