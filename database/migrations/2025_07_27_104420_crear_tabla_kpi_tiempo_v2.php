<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kpi_tiempos', function (Blueprint $table) {
            $table->id();
            $table->integer('ano')->nullable();
            $table->string('zona')->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('area')->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('mes')->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('concepto')->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->decimal('porcentaje', 15, 2)->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('kpi_tiempos');
    }
};