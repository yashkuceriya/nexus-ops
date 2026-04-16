<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\SensorSource;
use App\Models\WorkOrder;
use Illuminate\Support\Collection;

final class DashboardService
{
    /**
     * Aggregate statistics across all projects for a tenant.
     *
     * @return array{total_projects: int, active_projects: int, average_readiness: float, total_assets: int, total_work_orders: int, open_work_orders: int, total_issues: int, open_issues: int, projects: array}
     */
    public function getPortfolioSummary(int $tenantId): array
    {
        $projects = Project::where('tenant_id', $tenantId)->get();

        $assetCount = Asset::where('tenant_id', $tenantId)->count();

        $woStats = WorkOrder::where('tenant_id', $tenantId)
            ->selectRaw(
                'COUNT(*) as total_work_orders, '.
                "SUM(CASE WHEN status NOT IN ('completed', 'verified', 'cancelled') THEN 1 ELSE 0 END) as open_work_orders",
            )->first();

        // Projects table uses status values: planning|commissioning|closeout|
        // operational|archived. "Active" encompasses everything except archived
        // (i.e. projects that still need operational attention).
        $activeStatuses = ['planning', 'commissioning', 'closeout', 'operational'];

        return [
            'total_projects' => $projects->count(),
            'active_projects' => $projects->whereIn('status', $activeStatuses)->count(),
            'average_readiness' => round($projects->avg('readiness_score') ?? 0, 2),
            'total_assets' => $assetCount,
            'total_work_orders' => (int) $woStats->total_work_orders,
            'open_work_orders' => (int) $woStats->open_work_orders,
            'total_issues' => (int) $projects->sum('total_issues'),
            'open_issues' => (int) $projects->sum('open_issues'),
            'projects' => $projects->map(fn (Project $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'status' => $p->status,
                'readiness_score' => $p->readiness_score,
                'open_issues' => $p->open_issues,
                'target_handover_date' => $p->target_handover_date?->toDateString(),
            ])->all(),
        ];
    }

    /**
     * Project readiness score breakdown with blockers.
     *
     * @return array{id: int, name: string, readiness_score: float, calculated_score: float, blockers: array, issue_completion: float, test_completion: float, doc_completion: float, target_handover_date: ?string}|null
     */
    public function getProjectReadiness(int $projectId): ?array
    {
        $project = Project::find($projectId);

        if (! $project) {
            return null;
        }

        return [
            'id' => $project->id,
            'name' => $project->name,
            'readiness_score' => (float) $project->readiness_score,
            'calculated_score' => $project->calculateReadinessScore(),
            'blockers' => $project->getHandoverBlockers(),
            'issue_completion' => $project->total_issues > 0
                ? round((($project->total_issues - $project->open_issues) / $project->total_issues) * 100, 2)
                : 100.0,
            'test_completion' => $project->total_tests > 0
                ? round(($project->completed_tests / $project->total_tests) * 100, 2)
                : 100.0,
            'doc_completion' => $project->total_closeout_docs > 0
                ? round(($project->completed_closeout_docs / $project->total_closeout_docs) * 100, 2)
                : 100.0,
            'total_issues' => $project->total_issues,
            'open_issues' => $project->open_issues,
            'total_tests' => $project->total_tests,
            'completed_tests' => $project->completed_tests,
            'total_closeout_docs' => $project->total_closeout_docs,
            'completed_closeout_docs' => $project->completed_closeout_docs,
            'target_handover_date' => $project->target_handover_date?->toDateString(),
        ];
    }

    /**
     * Key performance indicators: MTTR, MTBF, PM compliance, backlog ratio, open WO count.
     *
     * @return array{mttr_minutes: ?float, mtbf_hours: ?float, pm_compliance_rate: float, backlog_ratio: float, open_work_orders: int, sla_breached: int, completed_this_month: int, total_assets: int, critical_assets: int, active_sensors: int, sensors_with_anomalies: int}
     */
    public function getKpis(int $tenantId): array
    {
        $workOrders = WorkOrder::where('tenant_id', $tenantId);

        // Open work order count
        $openCount = (clone $workOrders)
            ->whereNotIn('status', ['completed', 'verified', 'cancelled'])
            ->count();

        // SLA breached count (active work orders only)
        $slaBreachedCount = (clone $workOrders)
            ->where('sla_breached', true)
            ->whereNotIn('status', ['completed', 'verified', 'cancelled'])
            ->count();

        // Completed this month
        $completedThisMonth = (clone $workOrders)
            ->whereIn('status', ['completed', 'verified'])
            ->where('completed_at', '>=', now()->startOfMonth())
            ->get();

        // MTTR: Mean Time To Repair (average minutes from started_at to completed_at)
        $avgTimeToRepair = $completedThisMonth
            ->map(fn (WorkOrder $wo) => $wo->getTimeToRepairMinutes())
            ->filter()
            ->avg();

        // MTBF: Mean Time Between Failures
        $mtbf = $this->calculateMtbf($tenantId);

        // PM Compliance: preventive WOs completed on time / total preventive WOs
        $pmStats = WorkOrder::where('tenant_id', $tenantId)
            ->where('type', 'preventive')
            ->selectRaw(
                'COUNT(*) as total_pm, '.
                "SUM(CASE WHEN status IN ('completed', 'verified') AND (sla_breached = 0 OR sla_breached IS NULL) THEN 1 ELSE 0 END) as on_time_pm",
            )->first();

        $pmComplianceRate = $pmStats->total_pm > 0
            ? round(((int) $pmStats->on_time_pm / (int) $pmStats->total_pm) * 100, 2)
            : 100.0;

        // Backlog ratio: open WOs / completed WOs in last 30 days
        $completedLast30 = WorkOrder::where('tenant_id', $tenantId)
            ->whereIn('status', ['completed', 'verified'])
            ->where('completed_at', '>=', now()->subDays(30))
            ->count();

        $backlogRatio = $completedLast30 > 0
            ? round($openCount / $completedLast30, 2)
            : ($openCount > 0 ? (float) $openCount : 0.0);

        // Asset stats
        $totalAssets = Asset::where('tenant_id', $tenantId)->count();
        $criticalAssets = Asset::where('tenant_id', $tenantId)
            ->where('condition', 'critical')
            ->count();

        // Sensor stats
        $activeSensors = SensorSource::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->count();

        $anomalySensors = SensorSource::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('alert_enabled', true)
            ->whereHas('readings', fn ($q) => $q->where('is_anomaly', true)
                ->where('recorded_at', '>=', now()->subDay()))
            ->count();

        return [
            'mttr_minutes' => $avgTimeToRepair !== null ? round((float) $avgTimeToRepair, 2) : null,
            'mtbf_hours' => $mtbf,
            'pm_compliance_rate' => $pmComplianceRate,
            'backlog_ratio' => $backlogRatio,
            'open_work_orders' => $openCount,
            'sla_breached' => $slaBreachedCount,
            'completed_this_month' => $completedThisMonth->count(),
            'total_assets' => $totalAssets,
            'critical_assets' => $criticalAssets,
            'active_sensors' => $activeSensors,
            'sensors_with_anomalies' => $anomalySensors,
        ];
    }

    /**
     * Sensor status summary with anomaly counts.
     *
     * @return array{total: int, active: int, inactive: int, with_anomalies_24h: int, anomalies: array}
     */
    public function getSensorOverview(int $tenantId): array
    {
        $sensors = SensorSource::where('tenant_id', $tenantId)
            ->with('asset:id,name')
            ->get();

        $active = $sensors->where('is_active', true);
        $inactive = $sensors->where('is_active', false);

        $anomalies = SensorSource::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereHas('readings', fn ($q) => $q->where('is_anomaly', true)
                ->where('recorded_at', '>=', now()->subHours(24)))
            ->with(['readings' => fn ($q) => $q->where('is_anomaly', true)
                ->where('recorded_at', '>=', now()->subHours(24))
                ->latest('recorded_at')
                ->limit(5)])
            ->get();

        return [
            'total' => $sensors->count(),
            'active' => $active->count(),
            'inactive' => $inactive->count(),
            'with_anomalies_24h' => $anomalies->count(),
            'anomalies' => $anomalies->map(fn (SensorSource $s) => [
                'sensor_id' => $s->id,
                'sensor_name' => $s->name,
                'sensor_type' => $s->sensor_type,
                'asset_name' => $s->asset?->name,
                'recent_anomalies' => $s->readings->map(fn ($r) => [
                    'value' => $r->value,
                    'anomaly_type' => $r->anomaly_type,
                    'recorded_at' => $r->recorded_at->toIso8601String(),
                ])->all(),
            ])->all(),
        ];
    }

    /**
     * Recent audit log entries for a tenant.
     *
     * @return Collection<int, AuditLog>
     */
    public function getRecentActivity(int $tenantId, int $limit = 20): Collection
    {
        return AuditLog::where('tenant_id', $tenantId)
            ->with('user:id,name,email')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Work orders grouped by status for a tenant.
     *
     * @return array<string, int>
     */
    public function getWorkOrdersByStatus(int $tenantId): array
    {
        return WorkOrder::where('tenant_id', $tenantId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    // ────────────────────────────────────────────────────────────────
    // Helpers
    // ────────────────────────────────────────────────────────────────

    /**
     * Calculate Mean Time Between Failures across all assets for a tenant.
     *
     * Uses pure PHP to compute intervals between consecutive corrective /
     * emergency WO completions per asset. This avoids driver-specific SQL
     * (TIMESTAMPDIFF exists in MySQL but not SQLite/PostgreSQL) so the same
     * code path works in tests, CI, and production.
     */
    private function calculateMtbf(int $tenantId): ?float
    {
        $rows = WorkOrder::where('tenant_id', $tenantId)
            ->whereIn('type', ['corrective', 'emergency'])
            ->whereNotNull('completed_at')
            ->whereNotNull('asset_id')
            ->orderBy('asset_id')
            ->orderBy('completed_at')
            ->get(['asset_id', 'completed_at']);

        if ($rows->isEmpty()) {
            return null;
        }

        $intervalsHours = [];
        $previousByAsset = [];

        foreach ($rows as $row) {
            $assetId = (int) $row->asset_id;
            $completedAt = $row->completed_at;

            if (isset($previousByAsset[$assetId])) {
                $hours = $previousByAsset[$assetId]->diffInMinutes($completedAt) / 60;
                if ($hours > 0) {
                    $intervalsHours[] = $hours;
                }
            }

            $previousByAsset[$assetId] = $completedAt;
        }

        if (count($intervalsHours) === 0) {
            return null;
        }

        return round(array_sum($intervalsHours) / count($intervalsHours), 2);
    }
}
