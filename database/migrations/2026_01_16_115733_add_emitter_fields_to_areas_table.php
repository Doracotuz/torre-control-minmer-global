<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->string('emitter_name')->nullable()->after('icon_path');
            $table->string('emitter_phone')->nullable()->after('emitter_name');
            $table->string('emitter_address')->nullable()->after('emitter_phone');
            $table->string('emitter_colonia')->nullable()->after('emitter_address');
            $table->string('emitter_cp')->nullable()->after('emitter_colonia');
            $table->boolean('is_client')->default(false)->after('emitter_cp');
        });
    }

    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->dropColumn([
                'emitter_name', 
                'emitter_phone', 
                'emitter_address', 
                'emitter_colonia', 
                'emitter_cp', 
                'is_client'
            ]);
        });
    }
};
