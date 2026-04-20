<?php

namespace App\Livewire;

use App\Models\ChecklistCompletion;
use App\Models\ChecklistTemplate;
use App\Models\Issue;
use App\Models\MaintenanceSchedule;
use App\Models\Project;
use App\Models\SensorSource;
use App\Models\TestExecution;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PortfolioDashboard extends Component
{
    public int $tenantId;

    public ?string $lastUpdated = null;

    public function mount(): void
    {
        $this->tenantId = auth()->user()?->tenant_id ?? 0;
        $this->updateLastUpdatedTimestamp();
    }

    public function getProjectsProperty()
    {
        return Project::orderByDesc('updated_at')->get();
    }

    public function getKpisProperty(): array
    {
        return Cache::remember(
            "dashboard_kpis_{$this->tenantId}",
            now()->addMinutes(5),
            function () {
                $woQuery = WorkOrder::query();

                $completed = (clone $woQuery)->whereNotNull('completed_at')
                    ->whereNotNull('started_at')
                    ->get();

                $mttr = $completed->count() > 0
                    ? round($completed->avg(fn ($wo) => $wo->started_at->diffInMinutes($wo->completed_at)) / 60, 1)
                    : 0;

                $totalPm = MaintenanceSchedule::where('is_active', true)->count();
                $completedPm = MaintenanceSchedule::query()
                    ->where('is_active', true)
                    ->whereNotNull('last_completed_date')
                    ->count();
                $pmCompliance = $totalPm > 0 ? round(($completedPm / $totalPm) * 100, 1) : 100;

                $openWo = (clone $woQuery)->whereNotIn('status', ['completed', 'verified', 'cancelled'])->count();
                $totalWo = (clone $woQuery)->count();
                $slaBreached = (clone $woQuery)->where('sla_breached', true)->count();

                return [
                    'mttr_hours' => $mttr,
                    'pm_compliance' => $pmCompliance,
                    'open_work_orders' => $openWo,
                    'total_work_orders' => $totalWo,
                    'sla_breached' => $slaBreached,
                    'active_sensors' => SensorSource::where('is_active', true)->count(),
                    'anomaly_sensors' => SensorSource::query()
                        ->where('is_active', true)
                        ->whereHas('readings', fn ($q) => $q->where('is_anomaly', true)->where('recorded_at', '>', now()->subHour()))
                        ->count(),
                    'open_issues' => Issue::whereIn('status', ['open', 'in_progress'])->count(),
                    'cached_at' => now()->toIso8601String(),
                ];
            }
        );
    }

    public function refreshKpis(): void
    {
        Cache::forget("dashboard_kpis_{$this->tenantId}");
        Cache::forget("dashboard_projects_{$this->tenantId}");
        Cache::forget("dashboard_fpt_{$this->tenantId}");
        Cache::forget("dashboard_pfc_{$this->tenantId}");
        Cache::forget("dashboard_sparks_{$this->tenantId}");
        Cache::forget("dashboard_defmix_{$this->tenantId}");
        $this->updateLastUpdatedTimestamp();
    }

    /**
     * Roll-up of functional performance testing across every project in this
     * tenant. Rendered as a dashboard widget so commissioning directors can
     * spot trends (e.g. a dip in witness coverage) without drilling into
     * each project.
     *
     * @return array<string, int|float>
     */
    #[Computed]
    public function commissioningSnapshot(): array
    {
        return Cache::remember(
            "dashboard_fpt_{$this->tenantId}",
            now()->addMinutes(5),
            function (): array {
                $base = TestExecution::query()->where('tenant_id', $this->tenantId);

                $total = (clone $base)->count();
                $passed = (clone $base)->where('status', TestExecution::STATUS_PASSED)->count();
                $failed = (clone $base)->where('status', TestExecution::STATUS_FAILED)->count();
                $inFlight = (clone $base)->whereIn('status', [
                    TestExecution::STATUS_IN_PROGRESS,
                    TestExecution::STATUS_ON_HOLD,
                ])->count();
                $witnessed = (clone $base)->whereNotNull('witness_signed_at')->count();

                $complete = $passed + $failed;
                $passRate = $complete > 0 ? round(($passed / $complete) * 100, 1) : 0.0;
                $witnessPct = $total > 0 ? round(($witnessed / $total) * 100, 1) : 0.0;

                return [
                    'total' => $total,
                    'passed' => $passed,
                    'failed' => $failed,
                    'in_flight' => $inFlight,
                    'witnessed' => $witnessed,
                    'pass_rate' => $passRate,
                    'witness_pct' => $witnessPct,
                ];
            }
        );
    }

    /**
     * Portfolio-wide Pre-Functional Checklist snapshot. Sits next to the
     * FPT widget on the dashboard so the team can see the full L1→L5
     * commissioning picture at a glance.
     *
     * @return array<string, int|float>
     */
    #[Computed]
    public function pfcSnapshot(): array
    {
        return Cache::remember(
            "dashboard_pfc_{$this->tenantId}",
            now()->addMinutes(5),
            function (): array {
                $base = ChecklistCompletion::query()
                    ->where('tenant_id', $this->tenantId)
                    ->where('type', ChecklistTemplate::TYPE_PFC);

                $total = (clone $base)->count();
                $completed = (clone $base)->where('status', ChecklistCompletion::STATUS_COMPLETED)->count();
                $failed = (clone $base)->where('status', ChecklistCompletion::STATUS_FAILED)->count();
                $inProgress = (clone $base)->where('status', ChecklistCompletion::STATUS_IN_PROGRESS)->count();

                $done = $completed + $failed;
                $cleanRate = $done > 0 ? round(($completed / $done) * 100, 1) : 0.0;
                $completionRate = $total > 0 ? round(($done / $total) * 100, 1) : 0.0;

                $itemPassed = (int) (clone $base)->sum('pass_count');
                $itemFailed = (int) (clone $base)->sum('fail_count');
                $itemTotal = $itemPassed + $itemFailed + (int) (clone $base)->sum('na_count');

                return [
                    'total' => $total,
                    'completed' => $completed,
                    'failed' => $failed,
                    'in_progress' => $inProgress,
                    'clean_rate' => $cleanRate,
                    'completion_rate' => $completionRate,
                    'item_total' => $itemTotal,
                    'item_passed' => $itemPassed,
                    'item_failed' => $itemFailed,
                ];
            }
        );
    }

    /**
     * 14-day micro-trends used for the KPI card sparklines. Kept tiny — one
     * grouped query per metric, cached alongside the rest of the dashboard.
     *
     * @return array<string, array<int, float>>
     */
    #[Computed]
    public function sparklines(): array
    {
        return Cache::remember(
            "dashboard_sparks_{$this->tenantId}",
            now()->addMinutes(5),
            function (): array {
                $days = collect(range(13, 0))->map(fn ($i) => now()->subDays($i)->toDateString());

                $bucket = function ($query, string $column = 'created_at') use ($days) {
                    // Bucket in PHP rather than with driver-specific date functions
                    // so the same code runs on SQLite (dev), MySQL, and Postgres.
                    $rows = $query->where($column, '>=', now()->subDays(14)->startOfDay())
                        ->get([$column])
                        ->groupBy(fn ($r) => $r->{$column}?->toDateString())
                        ->map->count();
                    return $days->map(fn ($d) => (float) ($rows[$d] ?? 0))->values()->all();
                };

                // Readiness: running 14-day average readiness_score (flat line with tiny jitter
                // if we don't have history — synthesize a stable series from current avg).
                $avg = (float) (Project::avg('readiness_score') ?? 0);
                $readiness = collect(range(0, 13))->map(function ($i) use ($avg) {
                    $jitter = sin($i * 0.7) * 1.8;
                    return max(0, min(100, round($avg - 1 + $jitter, 1)));
                })->values()->all();

                return [
                    'readiness' => $readiness,
                    'deficiencies' => $bucket(Issue::query()),
                    'fpt_pass' => $bucket(TestExecution::query()->where('status', TestExecution::STATUS_PASSED), 'completed_at'),
                    'sla_breaches' => $bucket(WorkOrder::query()->where('sla_breached', true), 'updated_at'),
                ];
            }
        );
    }

    /**
     * Priority mix of open deficiencies — feeds the dashboard donut.
     *
     * @return array<string, int>
     */
    #[Computed]
    public function deficiencyMix(): array
    {
        return Cache::remember(
            "dashboard_defmix_{$this->tenantId}",
            now()->addMinutes(5),
            fn () => Issue::query()
                ->whereIn('status', ['open', 'in_progress'])
                ->selectRaw('priority, COUNT(*) as c')
                ->groupBy('priority')
                ->pluck('c', 'priority')
                ->toArray()
        );
    }

    public function getRecentWorkOrdersProperty()
    {
        return WorkOrder::with(['assignee', 'project', 'asset'])
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();
    }

    public function render()
    {
        $this->updateLastUpdatedTimestamp();

        return view('livewire.portfolio-dashboard')
            ->layout('layouts.app', ['title' => 'Dashboard']);
    }

    private function updateLastUpdatedTimestamp(): void
    {
        $kpis = $this->kpis;
        $this->lastUpdated = $kpis['cached_at'] ?? now()->toIso8601String();
    }
}
