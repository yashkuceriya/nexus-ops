<?php

namespace App\Livewire;

use App\Models\Issue;
use App\Models\MaintenanceSchedule;
use App\Models\Project;
use App\Models\SensorSource;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Cache;
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
        return Cache::remember(
            "dashboard_projects_{$this->tenantId}",
            now()->addMinutes(5),
            fn () => Project::orderByDesc('updated_at')->get()
        );
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
        $this->updateLastUpdatedTimestamp();
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
