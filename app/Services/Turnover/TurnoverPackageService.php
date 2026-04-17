<?php

declare(strict_types=1);

namespace App\Services\Turnover;

use App\Models\ChecklistCompletion;
use App\Models\ChecklistTemplate;
use App\Models\Project;
use App\Models\TestExecution;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;

/**
 * Assembles an "Accelerated Turnover Package" PDF for a project — a flagship
 * feature of modern commissioning platforms.
 *
 * The package consolidates every artifact an O&M team needs on Day 1:
 *   - Project overview & handover readiness score
 *   - Asset inventory (with QR codes, warranty data, manufacturer info)
 *   - Commissioning test results and outstanding issues
 *   - Closeout document checklist and status
 *   - Maintenance schedules for each asset
 *   - Electronic sign-off record (signatures, dates, roles)
 *
 * Generation is deliberately side-effect free and portable: the PDF bytes are
 * returned to the caller, who can stream them, store to S3, or attach to a
 * notification. A `TurnoverPackage` row is recorded so auditors can see
 * exactly what was handed over, when, and to whom.
 */
final class TurnoverPackageService
{
    /**
     * Build the data payload used by the PDF template. Separated from
     * rendering so it can be unit-tested without spinning up dompdf.
     *
     * @return array<string, mixed>
     */
    public function buildPayload(Project $project): array
    {
        $project->loadMissing([
            'tenant:id,name',
            'locations',
            'assets' => fn ($q) => $q->orderBy('system_type')->orderBy('name'),
            'assets.location:id,name,type',
            'assets.maintenanceSchedules' => fn ($q) => $q->where('is_active', true),
            'closeoutRequirements.document',
            'documents',
            'issues' => fn ($q) => $q->latest()->limit(500),
            'workOrders' => fn ($q) => $q->whereIn('status', ['completed', 'verified'])->latest('completed_at'),
        ]);

        $readiness = $project->readinessScore();

        $assets = $project->assets->map(fn ($asset) => [
            'id' => $asset->id,
            'name' => $asset->name,
            'asset_tag' => $asset->asset_tag,
            'qr_code' => $asset->qr_code ?: $asset->generateQrCode(),
            'system_type' => $asset->system_type,
            'category' => $asset->category,
            'location' => $asset->location?->name,
            'manufacturer' => $asset->manufacturer,
            'model_number' => $asset->model_number,
            'serial_number' => $asset->serial_number,
            'install_date' => $asset->install_date?->format('M d, Y'),
            'warranty_expiry' => $asset->warranty_expiry?->format('M d, Y'),
            'warranty_active' => $asset->isWarrantyActive(),
            'expected_life' => $asset->expected_life_years,
            'replacement_cost' => $asset->replacement_cost,
            'commissioning_status' => $asset->commissioning_status,
            'pm_schedules' => $asset->maintenanceSchedules->map(fn ($s) => [
                'name' => $s->name,
                'frequency' => $s->frequency,
                'next_due' => $s->next_due_date?->format('M d, Y'),
            ])->values()->all(),
        ])->values()->all();

        $closeoutByCategory = $project->closeoutRequirements
            ->groupBy('category')
            ->map(fn ($items, $category) => [
                'category' => $category ?: 'Uncategorized',
                'total' => $items->count(),
                'completed' => $items->where('status', 'completed')->count(),
                'items' => $items->map(fn ($req) => [
                    'name' => $req->name,
                    'status' => $req->status,
                    'due_date' => $req->due_date?->format('M d, Y'),
                    'document' => $req->document?->name,
                ])->values()->all(),
            ])
            ->values()
            ->all();

        $outstandingIssues = $project->issues
            ->filter(fn ($i) => $i->isOpen())
            ->values()
            ->map(fn ($issue) => [
                'id' => $issue->id,
                'title' => $issue->title,
                'priority' => $issue->priority,
                'status' => $issue->status,
                'due_date' => $issue->due_date?->format('M d, Y'),
            ])
            ->all();

        $completedTests = $project->workOrders
            ->where('type', 'inspection')
            ->map(fn ($wo) => [
                'wo_number' => $wo->wo_number,
                'title' => $wo->title,
                'completed_at' => $wo->completed_at?->format('M d, Y'),
                'verified_at' => $wo->verified_at?->format('M d, Y'),
            ])
            ->values()
            ->all();

        $fpt = $this->buildFptPayload($project);
        $pfc = $this->buildPfcPayload($project);

        return [
            'project' => $project,
            'tenant' => $project->tenant,
            'readiness_score' => $readiness->calculate(),
            'readiness_grade' => $readiness->grade(),
            'handover_blockers' => $project->getHandoverBlockers(),
            'assets' => $assets,
            'asset_count' => count($assets),
            'closeout_by_category' => $closeoutByCategory,
            'outstanding_issues' => $outstandingIssues,
            'completed_tests' => $completedTests,
            'documents' => $project->documents->map(fn ($d) => [
                'name' => $d->name,
                'category' => $d->category ?? 'General',
                'uploaded_at' => $d->created_at->format('M d, Y'),
            ])->all(),
            'fpt' => $fpt,
            'pfc' => $pfc,
            'generated_at' => now()->format('M d, Y \a\t g:i A T'),
            'generated_at_iso' => Carbon::now()->toIso8601String(),
        ];
    }

    /**
     * Build the Functional Performance Test scorecard that backs the
     * "Commissioning Performance" section of the handover package. This is
     * often the single most-scrutinised part of the document in an owner
     * acceptance meeting — it has to be complete, signed, and reconcilable
     * against the raw step results.
     *
     * @return array<string, mixed>
     */
    private function buildFptPayload(Project $project): array
    {
        $executions = TestExecution::query()
            ->where('tenant_id', $project->tenant_id)
            ->where('project_id', $project->id)
            ->with([
                'asset:id,name,asset_tag',
                'script:id,name,version,cx_level,system_type',
                'witness:id,name',
                'starter:id,name',
            ])
            ->orderBy('started_at')
            ->get();

        $completed = $executions->whereIn('status', [TestExecution::STATUS_PASSED, TestExecution::STATUS_FAILED]);
        $passed = $executions->where('status', TestExecution::STATUS_PASSED)->count();
        $failed = $executions->where('status', TestExecution::STATUS_FAILED)->count();
        $inFlight = $executions->whereIn('status', [TestExecution::STATUS_IN_PROGRESS, TestExecution::STATUS_ON_HOLD])->count();
        $witnessed = $executions->whereNotNull('witness_signed_at')->count();

        $totalSteps = (int) $executions->sum('total_count');
        $passedSteps = (int) $executions->sum('pass_count');
        $failedSteps = (int) $executions->sum('fail_count');

        $executionPassRate = $completed->count() > 0
            ? round(($passed / $completed->count()) * 100, 1)
            : 0.0;

        $byLevel = $executions->groupBy('cx_level')->map(function ($group, $level) {
            $complete = $group->whereIn('status', [TestExecution::STATUS_PASSED, TestExecution::STATUS_FAILED]);
            $pass = $group->where('status', TestExecution::STATUS_PASSED)->count();

            return [
                'level' => $level ?: '—',
                'total' => $group->count(),
                'passed' => $pass,
                'failed' => $group->where('status', TestExecution::STATUS_FAILED)->count(),
                'pass_rate' => $complete->count() > 0 ? round(($pass / $complete->count()) * 100, 1) : 0.0,
            ];
        })->sortKeys()->values()->all();

        $executionRows = $executions->map(fn (TestExecution $e) => [
            'id' => $e->id,
            'script' => $e->test_script_name ?: $e->script?->name,
            'version' => $e->test_script_version,
            'asset' => $e->asset?->name,
            'asset_tag' => $e->asset?->asset_tag,
            'cx_level' => $e->cx_level,
            'status' => $e->status,
            'pass_count' => (int) $e->pass_count,
            'fail_count' => (int) $e->fail_count,
            'total_count' => (int) $e->total_count,
            'started_at' => $e->started_at?->format('M d, Y'),
            'completed_at' => $e->completed_at?->format('M d, Y'),
            'witnessed' => $e->witness_signed_at !== null,
            'witness_name' => $e->witness?->name,
            'witness_signed_at' => $e->witness_signed_at?->format('M d, Y g:i A'),
            'starter' => $e->starter?->name,
            'is_retest' => $e->parent_execution_id !== null,
        ])->values()->all();

        return [
            'executions_total' => $executions->count(),
            'executions_passed' => $passed,
            'executions_failed' => $failed,
            'executions_in_flight' => $inFlight,
            'executions_witnessed' => $witnessed,
            'execution_pass_rate' => $executionPassRate,
            'step_total' => $totalSteps,
            'step_passed' => $passedSteps,
            'step_failed' => $failedSteps,
            'step_pass_rate' => $totalSteps > 0 ? round(($passedSteps / $totalSteps) * 100, 1) : 0.0,
            'by_level' => $byLevel,
            'rows' => $executionRows,
        ];
    }

    /**
     * Render the turnover package PDF and return its raw bytes.
     */
    public function render(Project $project): string
    {
        $payload = $this->buildPayload($project);

        $pdf = Pdf::loadView('turnover.package', $payload)
            ->setPaper('a4', 'portrait')
            ->setOption([
                'defaultFont' => 'sans-serif',
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
            ]);

        return $pdf->output();
    }

    /**
     * Build the Pre-Functional Checklist scorecard. Mirrors the FPT
     * scorecard so the turnover narrative runs L1/L2 (PFC) → L3+ (FPT)
     * in a single consistent structure.
     *
     * @return array<string, mixed>
     */
    private function buildPfcPayload(Project $project): array
    {
        $completions = ChecklistCompletion::query()
            ->where('tenant_id', $project->tenant_id)
            ->where('project_id', $project->id)
            ->where('type', ChecklistTemplate::TYPE_PFC)
            ->with(['template:id,name,cx_level', 'asset:id,name,asset_tag'])
            ->orderBy('created_at')
            ->get();

        $total = $completions->count();
        $completed = $completions->where('status', ChecklistCompletion::STATUS_COMPLETED)->count();
        $failed = $completions->where('status', ChecklistCompletion::STATUS_FAILED)->count();
        $inProgress = $completions->where('status', ChecklistCompletion::STATUS_IN_PROGRESS)->count();

        $totalItems = (int) $completions->sum(fn ($c) => $c->pass_count + $c->fail_count + $c->na_count);
        $passedItems = (int) $completions->sum('pass_count');
        $failedItems = (int) $completions->sum('fail_count');

        $completionRate = $total > 0
            ? round((($completed + $failed) / $total) * 100, 1)
            : 0.0;

        $cleanRate = ($completed + $failed) > 0
            ? round(($completed / ($completed + $failed)) * 100, 1)
            : 0.0;

        $byLevel = $completions->groupBy(fn (ChecklistCompletion $c) => $c->template?->cx_level ?: '—')
            ->map(function ($group, $level) {
                $total = $group->count();
                $clean = $group->filter(fn (ChecklistCompletion $c) => $c->isCleanPfc())->count();

                return [
                    'level' => $level,
                    'total' => $total,
                    'clean' => $clean,
                    'failed' => $group->where('status', ChecklistCompletion::STATUS_FAILED)->count(),
                    'clean_rate' => $total > 0 ? round(($clean / $total) * 100, 1) : 0.0,
                ];
            })->sortKeys()->values()->all();

        return [
            'total' => $total,
            'completed' => $completed,
            'failed' => $failed,
            'in_progress' => $inProgress,
            'completion_rate' => $completionRate,
            'clean_rate' => $cleanRate,
            'item_total' => $totalItems,
            'item_passed' => $passedItems,
            'item_failed' => $failedItems,
            'item_pass_rate' => $totalItems > 0 ? round(($passedItems / $totalItems) * 100, 1) : 0.0,
            'by_level' => $byLevel,
        ];
    }

    /**
     * Compute a filename safe for Content-Disposition headers.
     */
    public function filenameFor(Project $project): string
    {
        $slug = preg_replace('/[^A-Za-z0-9_-]+/', '_', $project->name) ?? 'project';

        return sprintf(
            'Turnover_Package_%s_%s.pdf',
            trim($slug, '_'),
            Carbon::now()->format('Y-m-d'),
        );
    }
}
