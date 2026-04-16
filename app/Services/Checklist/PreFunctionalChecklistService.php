<?php

declare(strict_types=1);

namespace App\Services\Checklist;

use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\ChecklistCompletion;
use App\Models\ChecklistTemplate;
use App\Models\Issue;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Orchestrates the Pre-Functional Checklist (PFC) lifecycle — the L1/L2
 * commissioning deliverable that must be clean before an FPT is started.
 *
 * Unlike the facility-operations checklist (which belongs to a work
 * order), a PFC completion is owned by a *project+asset* pair and its
 * outcome feeds both the readiness score and the turnover package. A
 * failed PFC item auto-opens a deficiency `Issue` the same way a failed
 * FPT step does — keeping the whole punch-list story in one model.
 */
final class PreFunctionalChecklistService
{
    /**
     * Kick off a PFC for the given asset. If one is already in progress
     * we return the open completion so the caller can resume — PFCs are
     * long-running, multi-session deliverables in the field.
     */
    public function start(ChecklistTemplate $template, Asset $asset, User $startedBy): ChecklistCompletion
    {
        if ($template->type !== ChecklistTemplate::TYPE_PFC) {
            throw new InvalidArgumentException('Template is not a pre-functional checklist.');
        }

        if ($asset->project_id === null) {
            throw new InvalidArgumentException('Asset must belong to a project to run a PFC.');
        }

        if ($template->tenant_id !== null && $template->tenant_id !== $asset->tenant_id) {
            throw new InvalidArgumentException('PFC template tenant does not match asset tenant.');
        }

        return DB::transaction(function () use ($template, $asset, $startedBy): ChecklistCompletion {
            $existing = ChecklistCompletion::query()
                ->where('tenant_id', $asset->tenant_id)
                ->where('asset_id', $asset->id)
                ->where('checklist_template_id', $template->id)
                ->where('type', ChecklistTemplate::TYPE_PFC)
                ->where('status', ChecklistCompletion::STATUS_IN_PROGRESS)
                ->first();

            if ($existing) {
                return $existing;
            }

            $completion = ChecklistCompletion::create([
                'tenant_id' => $asset->tenant_id,
                'project_id' => $asset->project_id,
                'asset_id' => $asset->id,
                'work_order_id' => null,
                'checklist_template_id' => $template->id,
                'completed_by' => $startedBy->id,
                'type' => ChecklistTemplate::TYPE_PFC,
                'responses' => [],
                'status' => ChecklistCompletion::STATUS_IN_PROGRESS,
                'pass_count' => 0,
                'fail_count' => 0,
                'na_count' => 0,
            ]);

            AuditLog::record(
                action: 'pfc_started',
                model: $completion,
                newValues: [
                    'template_id' => $template->id,
                    'asset_id' => $asset->id,
                ],
            );

            return $completion;
        });
    }

    /**
     * Record a single item response on an in-progress PFC. `$status` is
     * one of `pass|fail|na`; `$notes` is optional free-text the field
     * technician added (e.g. "awaiting balance report").
     */
    public function recordResponse(
        ChecklistCompletion $completion,
        int $stepOrder,
        string $status,
        ?string $value = null,
        ?string $notes = null,
    ): ChecklistCompletion {
        if (! in_array($status, ['pass', 'fail', 'na'], true)) {
            throw new InvalidArgumentException('Invalid PFC response status: '.$status);
        }

        if ($completion->status !== ChecklistCompletion::STATUS_IN_PROGRESS) {
            throw new InvalidArgumentException('Cannot record a response on a '.$completion->status.' PFC.');
        }

        $responses = collect($completion->responses ?? [])
            ->reject(fn (array $r) => ($r['step_order'] ?? null) === $stepOrder)
            ->values()
            ->push([
                'step_order' => $stepOrder,
                'status' => $status,
                'value' => $value,
                'notes' => $notes,
                'answered_at' => now()->toIso8601String(),
            ])
            ->all();

        $completion->update([
            'responses' => $responses,
            'pass_count' => collect($responses)->where('status', 'pass')->count(),
            'fail_count' => collect($responses)->where('status', 'fail')->count(),
            'na_count' => collect($responses)->where('status', 'na')->count(),
        ]);

        return $completion->refresh();
    }

    /**
     * Finalise a PFC. Any response flagged `fail` triggers a deficiency
     * issue on the asset so the failed items flow through the same punch
     * list as FPT-generated deficiencies.
     */
    public function complete(ChecklistCompletion $completion, User $completedBy): ChecklistCompletion
    {
        if ($completion->status !== ChecklistCompletion::STATUS_IN_PROGRESS) {
            return $completion;
        }

        return DB::transaction(function () use ($completion, $completedBy): ChecklistCompletion {
            $failed = collect($completion->responses ?? [])->where('status', 'fail');

            $completion->update([
                'status' => $failed->count() > 0
                    ? ChecklistCompletion::STATUS_FAILED
                    : ChecklistCompletion::STATUS_COMPLETED,
                'completed_at' => now(),
                'completed_by' => $completedBy->id,
            ]);

            foreach ($failed as $response) {
                $step = $this->findStep($completion, (int) ($response['step_order'] ?? 0));
                Issue::create([
                    'tenant_id' => $completion->tenant_id,
                    'project_id' => $completion->project_id,
                    'asset_id' => $completion->asset_id,
                    'title' => sprintf(
                        'PFC item failed: %s',
                        $step['title'] ?? ('Step '.$response['step_order'])
                    ),
                    'description' => trim(sprintf(
                        "Pre-Functional Checklist '%s' failed on step %s.\nField notes: %s",
                        $completion->template?->name ?? 'PFC',
                        $step['title'] ?? $response['step_order'],
                        $response['notes'] ?? '(none)',
                    )),
                    'status' => 'open',
                    'priority' => $step['priority'] ?? 'medium',
                    'issue_type' => 'deficiency',
                    'source_system' => 'pfc',
                    'source_id' => (string) $completion->id,
                ]);
            }

            AuditLog::record(
                action: 'pfc_completed',
                model: $completion,
                newValues: [
                    'status' => $completion->status,
                    'pass_count' => $completion->pass_count,
                    'fail_count' => $completion->fail_count,
                    'na_count' => $completion->na_count,
                ],
            );

            return $completion->refresh();
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findStep(ChecklistCompletion $completion, int $stepOrder): ?array
    {
        $steps = $completion->template?->steps ?? [];
        foreach ($steps as $step) {
            if ((int) ($step['order'] ?? 0) === $stepOrder) {
                return $step;
            }
        }

        return null;
    }
}
