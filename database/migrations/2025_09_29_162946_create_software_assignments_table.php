<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('software_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('software_license_id')->constrained('software_licenses')->onDelete('cascade');
            $table->foreignId('hardware_asset_id')->constrained('hardware_assets')->onDelete('cascade');
            $table->date('install_date');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('software_assignments');
    }
};