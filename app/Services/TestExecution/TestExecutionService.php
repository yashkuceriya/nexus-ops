<?php

declare(strict_types=1);

namespace App\Services\TestExecution;

use App\Events\TestExecutionCompleted;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\Issue;
use App\Models\TestExecution;
use App\Models\TestScript;
use App\Models\TestStep;
use App\Models\TestStepResult;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

/**
 * Orchestrates the FPT execution lifecycle.
 *
 * The service exposes one entry point per state transition — start, record,
 * skip, abort, complete, witness, retest — so every call writes an
 * append-only audit record and keeps cached counters on the parent
 * execution in sync.
 */
final class TestExecutionService
{
    /**
     * Spin up a new execution against an asset, snapshotting every step from
     * the chosen script template at this point in time.
     */
    public function start(
        TestScript $script,
        Asset $asset,
        User $startedBy,
        ?TestExecution $parent = null,
        ?int $cxAgentId = null,
        ?int $witnessId = null,
    ): TestExecution {
        if (! $script->isPublished()) {
            throw new InvalidArgumentException('Only published test scripts can be executed.');
        }

        if ($asset->tenant_id !== $startedBy->tenant_id) {
            throw new InvalidArgumentException('Asset and user must belong to the same tenant.');
        }

        if ($asset->project_id === null) {
            throw new InvalidArgumentException('Asset must be associated with a project before executing an FPT.');
        }

        $steps = $script->steps()->get();

        if ($steps->isEmpty()) {
            throw new InvalidArgumentException('Test script has no steps — cannot execute.');
        }

        return DB::transaction(function () use (
            $script, $asset, $startedBy, $parent, $cxAgentId, $witnessId, $steps,
        ): TestExecution {
            $execution = TestExecution::create([
                'tenant_id' => $asset->tenant_id,
                'test_script_id' => $script->id,
                'test_script_version' => $script->version,
                'test_script_name' => $script->name,
                'cx_level' => $script->cx_level,
                'project_id' => $asset->project_id,
                'asset_id' => $asset->id,
                'status' => TestExecution::STATUS_IN_PROGRESS,
                'started_by' => $startedBy->id,
                'started_at' => Carbon::now(),
                'parent_execution_id' => $parent?->id,
                'cx_agent_id' => $cxAgentId,
                'witness_id' => $witnessId,
                'total_count' => $steps->count(),
                'pending_count' => $steps->count(),
            ]);

            foreach ($steps as $step) {
                $this->createResultSnapshot($execution, $step);
            }

            AuditLog::record(
                action: 'test_execution_started',
                model: $execution,
                newValues: [
                    'script_id' => $script->id,
                    'script_name' => $script->name,
                    'asset_id' => $asset->id,
                    'retest_of' => $parent?->id,
                    'total_steps' => $steps->count(),
                ],
            );

            return $execution->refresh();
        });
    }

    /**
     * Record a result for a step. When a critical step fails we also raise a
     * linked `Issue` so the deficiency is visible everywhere the project
     * tracks open issues.
     */
    public function recordStepResult(
        TestStepResult $result,
        User $recordedBy,
        string $status,
        ?string $measuredValue = null,
        ?float $measuredNumeric = null,
        ?string $notes = null,
        ?string $photoPath = null,
    ): TestStepResult {
        $autoEvaluated = false;
        $step = $result->step;

        // If the underlying step is flagged for auto-evaluation and a numeric
        // measurement is present, we override the caller's proposed status
        // with the deterministic evaluation. The UI can still force a
        // manual fail/skip by passing STATUS_SKIPPED / STATUS_NA.
        if (
            $step !== null
            && $step->auto_evaluate
            && $step->measurement_type === TestStep::TYPE_NUMERIC
            && $measuredNumeric !== null
            && in_array($status, [TestStepResult::STATUS_PASS, TestStepResult::STATUS_FAIL], true)
        ) {
            $status = $step->evaluateNumeric($measuredNumeric);
            $autoEvaluated = true;
        }

        if (! in_array($status, [
            TestStepResult::STATUS_PASS,
            TestStepResult::STATUS_FAIL,
            TestStepResult::STATUS_SKIPPED,
            TestStepResult::STATUS_NA,
        ], true)) {
            throw new InvalidArgumentException("Invalid step result status: {$status}");
        }

        $execution = $result->execution;

        if ($execution === null) {
            throw new RuntimeException('Orphan step result has no execution.');
        }

        if ($execution->tenant_id !== $recordedBy->tenant_id) {
            throw new InvalidArgumentException('Recorder and execution must belong to the same tenant.');
        }

        if (! $execution->isInProgress()) {
            throw new InvalidArgumentException('Cannot record results on an execution that is not in progress.');
        }

        return DB::transaction(function () use (
            $result, $execution, $recordedBy, $status, $measuredValue, $measuredNumeric, $notes, $photoPath, $autoEvaluated,
        ): TestStepResult {
            $previousStatus = $result->status;

            $result->update([
                'status' => $status,
                'measured_value' => $measuredValue,
                'measured_numeric' => $measuredNumeric,
                'auto_evaluated' => $autoEvaluated,
                'notes' => $notes,
                'photo_path' => $photoPath ?? $result->photo_path,
                'recorded_by' => $recordedBy->id,
                'recorded_at' => Carbon::now(),
            ]);

            if ($status === TestStepResult::STATUS_FAIL && $result->issue_id === null) {
                $issue = $this->createDeficiencyIssue($execution, $result, $recordedBy);
                $result->update(['issue_id' => $issue->id]);
            }

            $this->recalculateCounters($execution, $previousStatus, $status);

            AuditLog::record(
                action: 'test_step_recorded',
                model: $result->refresh(),
                oldValues: ['status' => $previousStatus],
                newValues: [
                    'status' => $status,
                    'measured_value' => $measuredValue,
                    'measured_numeric' => $measuredNumeric,
                    'issue_id' => $result->issue_id,
                ],
            );

            return $result->refresh();
        });
    }

    /**
     * Close out an execution. If any steps remain pending or have failed the
     * execution is marked FAILED; otherwise PASSED.
     */
    public function complete(TestExecution $execution, User $completedBy, ?string $overallNotes = null): TestExecution
    {
        if ($execution->tenant_id !== $completedBy->tenant_id) {
            throw new InvalidArgumentException('User and execution must belong to the same tenant.');
        }

        if (! $execution->isInProgress()) {
            throw new InvalidArgumentException('Only in-progress executions can be completed.');
        }

        return DB::transaction(function () use ($execution, $completedBy, $overallNotes): TestExecution {
            $pending = $execution->results()->where('status', TestStepResult::STATUS_PENDING)->count();
            $failed = $execution->results()->where('status', TestStepResult::STATUS_FAIL)->count();

            $finalStatus = ($pending > 0 || $failed > 0)
                ? TestExecution::STATUS_FAILED
                : TestExecution::STATUS_PASSED;

            $execution->update([
                'status' => $finalStatus,
                'completed_at' => Carbon::now(),
                'overall_notes' => $overallNotes ?? $execution->overall_notes,
            ]);

            AuditLog::record(
                action: 'test_execution_completed',
                model: $execution->refresh(),
                newValues: [
                    'final_status' => $finalStatus,
                    'pass_count' => $execution->pass_count,
                    'fail_count' => $execution->fail_count,
                    'completed_by' => $completedBy->id,
                ],
            );

            // Fire once the transaction commits so listeners see the final row.
            DB::afterCommit(fn () => event(new TestExecutionCompleted($execution->refresh(), $completedBy)));

            return $execution;
        });
    }

    /**
     * Witness (owner rep / Cx authority) countersigns a completed execution.
     *
     * Produces a tamper-evident SHA-256 hash over the execution identity,
     * the witness identity, the pass/fail counters, and the captured
     * signature image (if any). The image itself is a base64-encoded
     * data URL captured from a <canvas> signature pad on the client.
     */
    public function witnessSign(
        TestExecution $execution,
        User $witness,
        ?string $signatureImage = null,
        ?Request $request = null,
    ): TestExecution {
        if ($execution->tenant_id !== $witness->tenant_id) {
            throw new InvalidArgumentException('Witness and execution must belong to the same tenant.');
        }

        if (! in_array($execution->status, [TestExecution::STATUS_PASSED, TestExecution::STATUS_FAILED], true)) {
            throw new InvalidArgumentException('Execution must be completed before witness signoff.');
        }

        $signedAt = Carbon::now();
        $imageFingerprint = $signatureImage !== null ? hash('sha256', $signatureImage) : 'no-image';

        $hash = hash('sha256', implode('|', [
            $execution->id,
            $execution->asset_id,
            $execution->test_script_id,
            $execution->test_script_version,
            $witness->id,
            $witness->email,
            $signedAt->toIso8601String(),
            (string) $execution->pass_count,
            (string) $execution->fail_count,
            $imageFingerprint,
        ]));

        $execution->update([
            'witness_id' => $witness->id,
            'witness_signed_at' => $signedAt,
            'witness_signature_hash' => $hash,
            'witness_signature_image' => $signatureImage,
            'witness_signature_ip' => $request?->ip(),
            'witness_signature_user_agent' => Str::limit($request?->userAgent() ?? '', 500, ''),
        ]);

        AuditLog::record(
            action: 'test_execution_witnessed',
            model: $execution->refresh(),
            newValues: [
                'witness_id' => $witness->id,
                'signature_hash' => $hash,
            ],
        );

        return $execution;
    }

    /**
     * Start a retest — a fresh execution chained to the failed parent.
     */
    public function retest(TestExecution $failed, User $startedBy): TestExecution
    {
        if ($failed->status !== TestExecution::STATUS_FAILED) {
            throw new InvalidArgumentException('Only failed executions can be retested.');
        }

        $script = $failed->script;

        if ($script === null) {
            throw new RuntimeException('Cannot retest: underlying script has been deleted.');
        }

        return $this->start(
            script: $script,
            asset: $failed->asset,
            startedBy: $startedBy,
            parent: $failed,
            cxAgentId: $failed->cx_agent_id,
            witnessId: $failed->witness_id,
        );
    }

    public function abort(TestExecution $execution, User $user, string $reason): TestExecution
    {
        if ($execution->tenant_id !== $user->tenant_id) {
            throw new InvalidArgumentException('User and execution must belong to the same tenant.');
        }

        if ($execution->isClosed()) {
            throw new InvalidArgumentException('Cannot abort a closed execution.');
        }

        $execution->update([
            'status' => TestExecution::STATUS_ABORTED,
            'completed_at' => Carbon::now(),
            'overall_notes' => trim(($execution->overall_notes ?? '')."\nAborted: ".$reason),
        ]);

        AuditLog::record(
            action: 'test_execution_aborted',
            model: $execution->refresh(),
            newValues: ['reason' => $reason, 'user_id' => $user->id],
        );

        return $execution;
    }

    /**
     * Verify the witness signature against the stored hash.
     */
    public function verifyWitnessSignature(TestExecution $execution): bool
    {
        if (! $execution->witness_signature_hash || ! $execution->witness_signed_at || ! $execution->witness) {
            return false;
        }

        $imageFingerprint = $execution->witness_signature_image !== null
            ? hash('sha256', $execution->witness_signature_image)
            : 'no-image';

        $expected = hash('sha256', implode('|', [
            $execution->id,
            $execution->asset_id,
            $execution->test_script_id,
            $execution->test_script_version,
            $execution->witness_id,
            $execution->witness->email,
            $execution->witness_signed_at->toIso8601String(),
            (string) $execution->pass_count,
            (string) $execution->fail_count,
            $imageFingerprint,
        ]));

        return hash_equals($expected, $execution->witness_signature_hash);
    }

    /**
     * Duplicate a system (or other-tenant-visible) script into the caller's
     * tenant so it can be customised. The clone preserves the `cloned_from_id`
     * link for provenance and starts as a draft v1 inside the tenant.
     */
    public function cloneToTenant(TestScript $source, User $cloner): TestScript
    {
        $tenantId = $cloner->tenant_id;
        if ($tenantId === null) {
            throw new InvalidArgumentException('Cloner must belong to a tenant.');
        }

        // Only system-level scripts or the tenant's own scripts may be cloned.
        // Cross-tenant cloning would leak another tenant's proprietary IP.
        if ($source->tenant_id !== null && $source->tenant_id !== $tenantId) {
            throw new InvalidArgumentException('Cannot clone a script owned by another tenant.');
        }

        return DB::transaction(function () use ($source, $cloner, $tenantId): TestScript {
            $baseSlug = Str::slug($source->slug ?: Str::slug($source->name));
            $uniqueSlug = $baseSlug.'-copy-'.substr((string) Str::uuid(), 0, 6);

            $clone = TestScript::create([
                'tenant_id' => $tenantId,
                'created_by' => $cloner->id,
                'cloned_from_id' => $source->id,
                'name' => $source->name.' (Custom)',
                'slug' => $uniqueSlug,
                'description' => $source->description,
                'system_type' => $source->system_type,
                'asset_category' => $source->asset_category,
                'cx_level' => $source->cx_level,
                'status' => TestScript::STATUS_DRAFT,
                'version' => 1,
                'is_system' => false,
                'estimated_duration_minutes' => $source->estimated_duration_minutes,
            ]);

            foreach ($source->steps()->get() as $step) {
                TestStep::create([
                    'test_script_id' => $clone->id,
                    'sequence' => $step->sequence,
                    'title' => $step->title,
                    'instruction' => $step->instruction,
                    'expected_behavior' => $step->expected_behavior,
                    'measurement_type' => $step->measurement_type,
                    'expected_value' => $step->expected_value,
                    'expected_numeric' => $step->expected_numeric,
                    'tolerance' => $step->tolerance,
                    'measurement_unit' => $step->measurement_unit,
                    'selection_options' => $step->selection_options,
                    'requires_photo' => $step->requires_photo,
                    'requires_witness' => $step->requires_witness,
                    'is_critical' => $step->is_critical,
                    'sensor_metric_key' => $step->sensor_metric_key,
                    'auto_evaluate' => $step->auto_evaluate,
                    'evaluation_mode' => $step->evaluation_mode,
                    'acceptable_min' => $step->acceptable_min,
                    'acceptable_max' => $step->acceptable_max,
                ]);
            }

            AuditLog::record(
                action: 'test_script_cloned',
                model: $clone,
                newValues: [
                    'cloned_from_id' => $source->id,
                    'cloned_from_name' => $source->name,
                    'step_count' => $source->steps()->count(),
                ],
            );

            return $clone;
        });
    }

    private function createResultSnapshot(TestExecution $execution, TestStep $step): TestStepResult
    {
        return TestStepResult::create([
            'test_execution_id' => $execution->id,
            'test_step_id' => $step->id,
            'step_sequence' => $step->sequence,
            'step_title' => $step->title,
            'step_instruction' => $step->instruction,
            'measurement_type' => $step->measurement_type,
            'expected_value' => $step->expected_value,
            'expected_numeric' => $step->expected_numeric,
            'tolerance' => $step->tolerance,
            'measurement_unit' => $step->measurement_unit,
            'evaluation_mode' => $step->auto_evaluate ? $step->evaluation_mode : null,
            'acceptable_min' => $step->acceptable_min,
            'acceptable_max' => $step->acceptable_max,
            'status' => TestStepResult::STATUS_PENDING,
        ]);
    }

    private function createDeficiencyIssue(TestExecution $execution, TestStepResult $result, User $recordedBy): Issue
    {
        $asset = $execution->asset;

        $title = sprintf(
            'FPT failure: %s — step %d: %s',
            $execution->test_script_name,
            $result->step_sequence,
            $result->step_title,
        );

        $description = trim(implode("\n", array_filter([
            'Auto-generated from a failed Functional Performance Test step.',
            '',
            "Script: {$execution->test_script_name} (v{$execution->test_script_version})",
            "Step {$result->step_sequence}: {$result->step_title}",
            '',
            'Instruction:',
            $result->step_instruction,
            '',
            $result->expected_value
                ? "Expected: {$result->expected_value} ".($result->measurement_unit ?? '')
                : null,
            $result->measured_value
                ? "Measured: {$result->measured_value} ".($result->measurement_unit ?? '')
                : null,
            $result->notes
                ? "\nTechnician notes: {$result->notes}"
                : null,
        ])));

        return Issue::create([
            'tenant_id' => $execution->tenant_id,
            'project_id' => $execution->project_id,
            'asset_id' => $asset?->id,
            'assigned_to' => null,
            'title' => $title,
            'description' => $description,
            'status' => 'open',
            'priority' => 'high',
            'issue_type' => 'deficiency',
            'source_system' => 'fpt',
            'source_id' => (string) $execution->id,
        ]);
    }

    /**
     * Recalculate the cached pass/fail/pending counters on the execution.
     * Called after each step result change so list views can render without
     * running an aggregate.
     */
    private function recalculateCounters(TestExecution $execution, string $previousStatus, string $newStatus): void
    {
        $counters = $execution->results()
            ->selectRaw('status, count(*) as n')
            ->groupBy('status')
            ->pluck('n', 'status')
            ->toArray();

        $execution->update([
            'pass_count' => (int) ($counters[TestStepResult::STATUS_PASS] ?? 0),
            'fail_count' => (int) ($counters[TestStepResult::STATUS_FAIL] ?? 0),
            'pending_count' => (int) ($counters[TestStepResult::STATUS_PENDING] ?? 0),
        ]);
    }
}
