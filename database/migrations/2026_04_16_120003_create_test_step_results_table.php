<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_step_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_execution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('test_step_id')->nullable()->constrained()->nullOnDelete();

            // Step snapshot so historical results never change even if the
            // underlying template step is later edited or deleted.
            $table->unsignedInteger('step_sequence');
            $table->string('step_title', 200);
            $table->text('step_instruction');
            $table->enum('measurement_type', ['numeric', 'boolean', 'selection', 'text', 'none'])
                ->default('none');
            $table->string('expected_value', 120)->nullable();
            $table->decimal('expected_numeric', 14, 4)->nullable();
            $table->decimal('tolerance', 14, 4)->nullable();
            $table->string('measurement_unit', 40)->nullable();

            // Evaluation spec is snapshotted so the ruleset that decided
            // pass/fail is frozen on the record, not re-evaluated from a
            // later template edit.
            $table->boolean('auto_evaluated')->default(false);
            $table->enum('evaluation_mode', [
                'within_tolerance', 'greater_than_or_equal', 'less_than_or_equal',
                'between', 'exact',
            ])->nullable();
            $table->decimal('acceptable_min', 14, 4)->nullable();
            $table->decimal('acceptable_max', 14, 4)->nullable();

            $table->enum('status', ['pending', 'pass', 'fail', 'skipped', 'na'])->default('pending');

            $table->string('measured_value', 500)->nullable();
            $table->decimal('measured_numeric', 14, 4)->nullable();
            $table->text('notes')->nullable();
            $table->string('photo_path', 500)->nullable();

            // When a step fails we auto-generate an Issue and link it here so
            // the deficiency tracking workflow has a clear anchor.
            $table->foreignId('issue_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('recorded_at')->nullable();

            $table->timestamps();

            $table->unique(['test_execution_id', 'step_sequence']);
            $table->index(['test_execution_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_step_results');
    }
};
