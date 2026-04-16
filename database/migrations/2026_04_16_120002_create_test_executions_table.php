<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('test_script_id')->constrained()->cascadeOnDelete();

            // Snapshot of the script version at the time this execution started —
            // this is the audit anchor so later edits to the script template
            // don't rewrite history.
            $table->unsignedInteger('test_script_version')->default(1);
            $table->string('test_script_name', 255);

            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();

            $table->enum('status', ['draft', 'in_progress', 'passed', 'failed', 'aborted', 'on_hold'])
                ->default('draft');

            $table->foreignId('started_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Optional retest chain — if this execution is a retest of a failed
            // earlier run, we link back so the history is preserved.
            $table->foreignId('parent_execution_id')->nullable()
                ->constrained('test_executions')->nullOnDelete();

            // Commissioning agent (the person running the test) may differ from
            // the witness (e.g. owner rep / Cx authority signing off).
            $table->foreignId('cx_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('witness_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('witness_signature_hash', 128)->nullable();
            // Rendered signature — captured from a canvas pad as a
            // base64-encoded PNG data URL. Stored separately so the hash
            // anchor stays small and the image can be omitted from API
            // responses when not needed.
            $table->mediumText('witness_signature_image')->nullable();
            $table->string('witness_signature_ip', 45)->nullable();
            $table->string('witness_signature_user_agent', 500)->nullable();
            $table->timestamp('witness_signed_at')->nullable();

            // Snapshotted cx_level — so renamed/deleted scripts still carry
            // the commissioning level on the audit record.
            $table->enum('cx_level', ['L1', 'L2', 'L3', 'L4', 'L5'])->nullable();

            $table->text('overall_notes')->nullable();

            // Cached counters so list views don't need an aggregate query.
            $table->unsignedInteger('pass_count')->default(0);
            $table->unsignedInteger('fail_count')->default(0);
            $table->unsignedInteger('pending_count')->default(0);
            $table->unsignedInteger('total_count')->default(0);

            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['project_id', 'status']);
            $table->index(['project_id', 'cx_level']);
            $table->index(['asset_id', 'status']);
            $table->index('parent_execution_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_executions');
    }
};
