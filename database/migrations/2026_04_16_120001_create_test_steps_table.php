<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_script_id')->constrained()->cascadeOnDelete();

            $table->unsignedInteger('sequence');
            $table->string('title', 200);
            $table->text('instruction');
            $table->text('expected_behavior')->nullable();

            // Measurement spec for pass/fail evaluation.
            $table->enum('measurement_type', ['numeric', 'boolean', 'selection', 'text', 'none'])
                ->default('none');
            $table->string('expected_value', 120)->nullable();
            $table->decimal('expected_numeric', 14, 4)->nullable();
            $table->decimal('tolerance', 14, 4)->nullable();
            $table->string('measurement_unit', 40)->nullable();
            $table->json('selection_options')->nullable();

            // When true + measurement_type=numeric the service computes
            // pass/fail automatically from the measured value according to
            // `evaluation_mode`. Reduces operator clicks *and* operator-error
            // at the same time — core value prop for Cx teams.
            $table->boolean('auto_evaluate')->default(false);
            $table->enum('evaluation_mode', [
                'within_tolerance', 'greater_than_or_equal', 'less_than_or_equal',
                'between', 'exact',
            ])->default('within_tolerance');
            $table->decimal('acceptable_min', 14, 4)->nullable();
            $table->decimal('acceptable_max', 14, 4)->nullable();

            $table->boolean('requires_photo')->default(false);
            $table->boolean('requires_witness')->default(false);
            $table->boolean('is_critical')->default(false);

            // Hint for auto-filling from BMS / sensor sources
            // (matched against sensor_sources.metric_key at execution time).
            $table->string('sensor_metric_key', 80)->nullable();

            $table->timestamps();

            $table->unique(['test_script_id', 'sequence']);
            $table->index('test_script_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_steps');
    }
};
