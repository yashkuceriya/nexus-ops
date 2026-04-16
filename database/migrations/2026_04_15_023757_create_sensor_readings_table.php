<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sensor_source_id')->constrained()->cascadeOnDelete();
            $table->decimal('value', 10, 2);
            $table->boolean('is_anomaly')->default(false);
            $table->string('anomaly_type')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['sensor_source_id', 'recorded_at']);
            $table->index(['is_anomaly', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};
