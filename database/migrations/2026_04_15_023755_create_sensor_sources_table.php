<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensor_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_id')->nullable();
            $table->string('name');
            $table->enum('sensor_type', ['temperature', 'humidity', 'vibration', 'pressure', 'air_quality', 'water_leak', 'power', 'runtime', 'occupancy', 'custom'])->default('custom');
            $table->string('unit')->nullable();
            $table->decimal('threshold_min', 10, 2)->nullable();
            $table->decimal('threshold_max', 10, 2)->nullable();
            $table->decimal('last_value', 10, 2)->nullable();
            $table->timestamp('last_reading_at')->nullable();
            $table->boolean('alert_enabled')->default(true);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'sensor_type']);
            $table->index(['asset_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_sources');
    }
};
