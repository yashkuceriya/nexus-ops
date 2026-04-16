<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\ChecklistCompletion;
use App\Models\ChecklistTemplate;
use App\Models\Issue;
use App\Models\Project;
use App\Models\TestExecution;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Executive-facing analytics for the commissioning programme.
 *
 * Where the Cx Test Matrix answers "did this asset pass this test?", this
 * page answers the questions an owner / director asks at a steering
 * committee — "is our pass rate improving?", "which scripts fail most?",
 * "how much of our programme has witness attestation?", "how old is our
 * oldest open deficiency?".
 *
 * All computations live in public computed properties so the view stays
 * declarative and the page can be reused inside PDFs or emails later.
 */
class CommissioningAnalytics extends Component
{
    #[Url(history: true)]
    public ?int $projectFilter = null;

    #[Url(history: true)]
    public int $lookbackMonths = 6;

    public function mount(): void
    {
        $this->lookbackMonths = max(3, min(12, $this->lookbackMonths));
    }

    #[Computed]
    public function projects()
    {
        return Project::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function baseExecutions()
    {
        return TestExecution::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->when($this->projectFilter, fn ($q) => $q->where('project_id', $this->projectFilter));
    }

    /**
     * Headline numbers shown at the top of the page.
     *
     * @return array<string, int|float>
     */
    #[Computed]
    public function headline(): array
    {
        $base = $this->baseExecutions();

        $total = (clone $base)->count();
        $passed = (clone $base)->where('status', TestExecution::STATUS_PASSED)->count();
        $failed = (clone $base)->where('status', TestExecution::STATUS_FAILED)->count();
        $witnessed = (clone $base)->whereNotNull('witness_signed_at')->count();
        $retests = (clone $base)->whereNotNull('parent_execution_id')->count();

        $completed = $passed + $failed;

        return [
            'total' => $total,
            'passed' => $passed,
            'failed' => $failed,
            'witnessed' => $witnessed,
            'retests' => $retests,
            'pass_rate' => $completed > 0 ? round(($passed / $completed) * 100, 1) : 0.0,
            'witness_coverage' => $total > 0 ? round(($witnessed / $total) * 100, 1) : 0.0,
        ];
    }

    /**
     * Monthly trend: pass rate, volume, and witness coverage over the lookback
     * window. Consumed by Chart.js on the client.
     *
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function monthlyTrend(): array
    {
        $cursor = Carbon::now()->startOfMonth()->subMonths($this->lookbackMonths - 1);
        $buckets = [];

        for ($i = 0; $i < $this->lookbackMonths; $i++) {
            $start = $cursor->copy();
            $end = $cursor->copy()->endOfMonth();

            $monthly = $this->baseExecutions()
                ->whereBetween('started_at', [$start, $end])
                ->get(['status', 'witness_signed_at']);

            $total = $monthly->count();
            $passed = $monthly->where('status', TestExecution::STATUS_PASSED)->count();
            $failed = $monthly->where('status', TestExecution::STATUS_FAILED)->count();
            $witnessed = $monthly->whereNotNull('witness_signed_at')->count();
            $complete = $passed + $failed;

            $buckets[] = [
                'label' => $start->format('M Y'),
                'iso' => $start->format('Y-m'),
                'total' => $total,
                'passed' => $passed,
                'failed' => $failed,
                'pass_rate' => $complete > 0 ? round(($passed / $complete) * 100, 1) : null,
                'witness_coverage' => $total > 0 ? round(($witnessed / $total) * 100, 1) : null,
            ];

            $cursor->addMonth();
        }

        return $buckets;
    }

    /**
     * Five scripts with the worst pass rates across the tenant's execution
     * log. "Most failed" means most deficiency opportunities — these scripts
     * are the best signal for where design / construction issues cluster.
     *
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function topFailingScripts(): array
    {
        $executions = $this->baseExecutions()
            ->whereIn('status', [TestExecution::STATUS_PASSED, TestExecution::STATUS_FAILED])
            ->with('script:id,name,system_type,cx_level')
            ->get(['id', 'test_script_id', 'test_script_name', 'status']);

        return $executions
            ->groupBy('test_script_id')
            ->map(function ($group) {
                $first = $group->first();
                $total = $group->count();
                $passed = $group->where('status', TestExecution::STATUS_PASSED)->count();
                $failed = $group->where('status', TestExecution::STATUS_FAILED)->count();

                return [
                    'script_id' => $first->test_script_id,
                    'name' => $first->script?->name ?? $first->test_script_name,
                    'system_type' => $first->script?->system_type,
                    'cx_level' => $first->script?->cx_level,
                    'runs' => $total,
                    'failed' => $failed,
                    'fail_rate' => $total > 0 ? round(($failed / $total) * 100, 1) : 0.0,
                ];
            })
            ->filter(fn ($row) => $row['runs'] >= 1 && $row['failed'] > 0)
            ->sortByDesc('fail_rate')
            ->take(5)
            ->values()
            ->all();
    }

    /**
     * Distribution of executions across ASHRAE Guideline 0 Cx levels — reveals
     * commissioning-programme maturity (heavy L3+ = deep Cx).
     *
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function byCxLevel(): array
    {
        return $this->baseExecutions()
            ->selectRaw('cx_level, COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as passed,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as failed', [
                TestExecution::STATUS_PASSED,
                TestExecution::STATUS_FAILED,
            ])
            ->groupBy('cx_level')
            ->orderBy('cx_level')
            ->get()
            ->map(fn ($r) => [
                'level' => $r->cx_level ?: '—',
                'total' => (int) $r->total,
                'passed' => (int) $r->passed,
                'failed' => (int) $r->failed,
            ])
            ->all();
    }

    /**
     * Age-bucketed open deficiencies. Aging is the single best predictor of
     * handover slippage; this chart is what steering committees look at
     * first.
     *
     * @return array<int, array<string, int|string>>
     */
    #[Computed]
    public function deficiencyAging(): array
    {
        $issues = Issue::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->when($this->projectFilter, fn ($q) => $q->where('project_id', $this->projectFilter))
            ->whereIn('status', ['open', 'in_progress'])
            ->get(['id', 'created_at', 'priority']);

        $buckets = [
            '0-7d' => ['label' => '0–7 days', 'count' => 0, 'critical' => 0],
            '8-30d' => ['label' => '8–30 days', 'count' => 0, 'critical' => 0],
            '31-90d' => ['label' => '31–90 days', 'count' => 0, 'critical' => 0],
            '90d+' => ['label' => '90+ days', 'count' => 0, 'critical' => 0],
        ];

        $now = Carbon::now();
        foreach ($issues as $i) {
            // Carbon v3 `diffInDays` can return a signed float depending on
            // argument order, so normalise to an absolute integer so the
            // bucket boundaries remain intuitive.
            $age = (int) abs($now->diffInDays($i->created_at));
            $key = match (true) {
                $age <= 7 => '0-7d',
                $age <= 30 => '8-30d',
                $age <= 90 => '31-90d',
                default => '90d+',
            };
            $buckets[$key]['count']++;
            if (in_array($i->priority, ['critical', 'emergency', 'high'], true)) {
                $buckets[$key]['critical']++;
            }
        }

        return array_values($buckets);
    }

    /**
     * Pre-functional checklist roll-up for the same filter as FPT analytics.
     * Surfaces L1/L2 readiness alongside functional testing so steering
     * committees see the full ASHRAE G0 stack in one place.
     *
     * @return array<string, int|float|array<int, array<string, float|int|string>>>
     */
    #[Computed]
    public function pfcSnapshot(): array
    {
        $base = ChecklistCompletion::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('type', ChecklistTemplate::TYPE_PFC)
            ->when($this->projectFilter, fn ($q) => $q->where('project_id', $this->projectFilter));

        $total = (clone $base)->count();
        $completed = (clone $base)->where('status', ChecklistCompletion::STATUS_COMPLETED)->count();
        $failed = (clone $base)->where('status', ChecklistCompletion::STATUS_FAILED)->count();
        $inProgress = (clone $base)->where('status', ChecklistCompletion::STATUS_IN_PROGRESS)->count();

        $done = $completed + $failed;
        $cleanRate = $done > 0 ? round(($completed / $done) * 100, 1) : 0.0;
        $completionRate = $total > 0 ? round(($done / $total) * 100, 1) : 0.0;

        $itemPassed = (int) (clone $base)->sum('pass_count');
        $itemFailed = (int) (clone $base)->sum('fail_count');
        $itemNa = (int) (clone $base)->sum('na_count');
        $itemTotal = $itemPassed + $itemFailed + $itemNa;
        $itemPassRate = $itemTotal > 0 ? round(($itemPassed / $itemTotal) * 100, 1) : 0.0;

        $byLevel = ChecklistCompletion::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('type', ChecklistTemplate::TYPE_PFC)
            ->when($this->projectFilter, fn ($q) => $q->where('project_id', $this->projectFilter))
            ->with('template:id,cx_level')
            ->get()
            ->groupBy(fn (ChecklistCompletion $c) => $c->template?->cx_level ?: '—')
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
            })
            ->sortKeys()
            ->values()
            ->all();

        return [
            'total' => $total,
            'completed' => $completed,
            'failed' => $failed,
            'in_progress' => $inProgress,
            'clean_rate' => $cleanRate,
            'completion_rate' => $completionRate,
            'item_passed' => $itemPassed,
            'item_failed' => $itemFailed,
            'item_na' => $itemNa,
            'item_total' => $itemTotal,
            'item_pass_rate' => $itemPassRate,
            'by_level' => $byLevel,
        ];
    }

    public function render()
    {
        return view('livewire.commissioning-analytics')
            ->layout('layouts.app', [
                'title' => 'Commissioning Analytics',
                'subtitle' => 'Programme-wide performance, trends, and deficiency aging',
            ]);
    }
}
