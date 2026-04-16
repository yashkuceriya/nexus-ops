<?php

namespace App\Livewire;

use App\Models\Asset;
use App\Models\MaintenanceSchedule;
use App\Models\Project;
use App\Models\WorkOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ReportsPage extends Component
{
    public int $tenantId;
    public string $dateFrom;
    public string $dateTo;
    public string $projectFilter = '';

    public function mount(): void
    {
        $this->tenantId = auth()->user()?->tenant_id ?? 0;
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function getProjectsProperty()
    {
        return Project::orderBy('name')
            ->get(['id', 'name']);
    }

    public function getKpiSummaryProperty(): array
    {
        $query = WorkOrder::whereBetween('created_at', [$this->dateFrom, Carbon::parse($this->dateTo)->endOfDay()])
            ->when($this->projectFilter, fn ($q) => $q->where('project_id', $this->projectFilter));

        $totalWo = (clone $query)->count();

        $completed = (clone $query)->whereNotNull('completed_at')
            ->whereNotNull('started_at')
            ->get();

        $avgMttr = $completed->count() > 0
            ? round($completed->avg(fn ($wo) => $wo->started_at->diffInMinutes($wo->completed_at)) / 60, 1)
            : 0;

        $totalCost = (clone $query)->sum('actual_cost') ?: 0;

        $totalPm = MaintenanceSchedule::where('is_active', true)->count();
        $completedPm = MaintenanceSchedule::where('is_active', true)
            ->whereNotNull('last_completed_date')
            ->count();
        $pmCompliance = $totalPm > 0 ? round(($completedPm / $totalPm) * 100, 1) : 100;

        return [
            'total_work_orders' => $totalWo,
            'avg_mttr_hours' => $avgMttr,
            'total_cost' => $totalCost,
            'pm_compliance' => $pmCompliance,
        ];
    }

    public function getWorkOrdersByMonthProperty(): array
    {
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $months->push(now()->subMonths($i)->format('Y-m'));
        }

        $query = WorkOrder::where('created_at', '>=', now()->subMonths(12)->startOfMonth())
            ->when($this->projectFilter, fn ($q) => $q->where('project_id', $this->projectFilter));

        $data = (clone $query)
            ->selectRaw("strftime('%Y-%m', created_at) as month, type, COUNT(*) as count")
            ->groupBy('month', 'type')
            ->get();

        $corrective = [];
        $preventive = [];
        $inspection = [];
        $labels = [];

        foreach ($months as $month) {
            $labels[] = Carbon::parse($month . '-01')->format('M Y');
            $corrective[] = $data->where('month', $month)->where('type', 'corrective')->first()?->count ?? 0;
            $preventive[] = $data->where('month', $month)->where('type', 'preventive')->first()?->count ?? 0;
            $inspection[] = $data->where('month', $month)->where('type', 'inspection')->first()?->count ?? 0;
        }

        return [
            'labels' => $labels,
            'corrective' => $corrective,
            'preventive' => $preventive,
            'inspection' => $inspection,
        ];
    }

    public function getWorkOrderAgingProperty(): array
    {
        $openWos = WorkOrder::whereNotIn('status', ['completed', 'verified', 'cancelled'])
            ->when($this->projectFilter, fn ($q) => $q->where('project_id', $this->projectFilter))
            ->select(['id', 'created_at'])
            ->get();

        $buckets = ['0-7 days' => 0, '8-14 days' => 0, '15-30 days' => 0, '30+ days' => 0];

        foreach ($openWos as $wo) {
            $age = $wo->created_at->diffInDays(now());
            if ($age <= 7) {
                $buckets['0-7 days']++;
            } elseif ($age <= 14) {
                $buckets['8-14 days']++;
            } elseif ($age <= 30) {
                $buckets['15-30 days']++;
            } else {
                $buckets['30+ days']++;
            }
        }

        return [
            'labels' => array_keys($buckets),
            'values' => array_values($buckets),
        ];
    }

    public function getTopProblemAssetsProperty(): array
    {
        $assets = Asset::where('assets.tenant_id', $this->tenantId)
            ->join('work_orders', 'work_orders.asset_id', '=', 'assets.id')
            ->when($this->projectFilter, fn ($q) => $q->where('work_orders.project_id', $this->projectFilter))
            ->selectRaw('assets.name, COUNT(work_orders.id) as wo_count, COALESCE(SUM(work_orders.actual_cost), 0) as total_cost')
            ->groupBy('assets.id', 'assets.name')
            ->orderByDesc('wo_count')
            ->limit(10)
            ->get();

        return [
            'labels' => $assets->pluck('name')->toArray(),
            'wo_counts' => $assets->pluck('wo_count')->toArray(),
            'costs' => $assets->pluck('total_cost')->map(fn ($c) => round((float) $c, 2))->toArray(),
        ];
    }

    public function getPmComplianceOverTimeProperty(): array
    {
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $months->push(now()->subMonths($i));
        }

        // Load all active PM schedules once instead of 24 queries (2 per month)
        $allSchedules = MaintenanceSchedule::where('is_active', true)
            ->select(['id', 'created_at', 'last_completed_date'])
            ->get();

        $labels = [];
        $values = [];

        foreach ($months as $month) {
            $labels[] = $month->format('M Y');
            $endOfMonth = $month->copy()->endOfMonth();

            $activeByMonth = $allSchedules->filter(
                fn ($s) => $s->created_at <= $endOfMonth
            );

            $totalPm = $activeByMonth->count();

            $completedPm = $activeByMonth->filter(
                fn ($s) => $s->last_completed_date !== null && $s->last_completed_date <= $endOfMonth
            )->count();

            $values[] = $totalPm > 0 ? round(($completedPm / $totalPm) * 100, 1) : 100;
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    public function getSlaComplianceTrendProperty(): array
    {
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $months->push(now()->subMonths($i));
        }

        // Load all WOs with SLA data once instead of 24 queries (2 per month)
        $slaWorkOrders = WorkOrder::whereNotNull('sla_deadline')
            ->where('created_at', '>=', now()->subMonths(12)->startOfMonth())
            ->when($this->projectFilter, fn ($q) => $q->where('project_id', $this->projectFilter))
            ->select(['id', 'created_at', 'sla_deadline', 'sla_breached'])
            ->get();

        $labels = [];
        $values = [];

        foreach ($months as $month) {
            $labels[] = $month->format('M Y');
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $monthWos = $slaWorkOrders->filter(
                fn ($wo) => $wo->created_at >= $start && $wo->created_at <= $end
            );

            $total = $monthWos->count();
            $breached = $monthWos->where('sla_breached', true)->count();

            $values[] = $total > 0 ? round((($total - $breached) / $total) * 100, 1) : 100;
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    public function getCostByCategoryProperty(): array
    {
        $data = WorkOrder::where('work_orders.tenant_id', $this->tenantId)
            ->join('assets', 'assets.id', '=', 'work_orders.asset_id')
            ->whereBetween('work_orders.created_at', [$this->dateFrom, Carbon::parse($this->dateTo)->endOfDay()])
            ->when($this->projectFilter, fn ($q) => $q->where('work_orders.project_id', $this->projectFilter))
            ->whereNotNull('assets.system_type')
            ->selectRaw('assets.system_type, COALESCE(SUM(work_orders.actual_cost), 0) as total_cost')
            ->groupBy('assets.system_type')
            ->orderByDesc('total_cost')
            ->get();

        return [
            'labels' => $data->pluck('system_type')->map(fn ($s) => str_replace('_', ' ', ucfirst($s)))->toArray(),
            'values' => $data->pluck('total_cost')->map(fn ($c) => round((float) $c, 2))->toArray(),
        ];
    }

    public function render()
    {
        return view('livewire.reports-page')
            ->layout('layouts.app', ['title' => 'Analytics & Reports']);
    }
}
