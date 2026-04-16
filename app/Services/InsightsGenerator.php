<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\MaintenanceSchedule;
use App\Models\SensorSource;
use App\Models\WorkOrder;

class InsightsGenerator
{
    public function __construct(
        protected int $tenantId
    ) {}

    public function generate(): array
    {
        $insights = [];

        $insights = array_merge(
            $insights,
            $this->analyzeWorkOrderFrequency(),
            $this->analyzeSensorAnomalies(),
            $this->analyzePmCompliance(),
            $this->analyzeSlaBreach(),
        );

        return $insights;
    }

    protected function analyzeWorkOrderFrequency(): array
    {
        $insights = [];

        $recentWos = WorkOrder::where('tenant_id', $this->tenantId)
            ->where('type', 'corrective')
            ->where('created_at', '>=', now()->subDays(90))
            ->get();

        if ($recentWos->isEmpty()) {
            return $insights;
        }

        $totalCorrective = $recentWos->count();

        // Group by asset system type
        $assetIds = $recentWos->pluck('asset_id')->filter()->unique();
        $assets = Asset::whereIn('id', $assetIds)->get()->keyBy('id');

        $byType = [];
        foreach ($recentWos as $wo) {
            $type = $assets->get($wo->asset_id)?->system_type ?? 'Unknown';
            $byType[$type] = ($byType[$type] ?? 0) + 1;
        }

        if (! empty($byType)) {
            arsort($byType);
            $topType = array_key_first($byType);
            $topCount = $byType[$topType];
            $pct = $totalCorrective > 0 ? round(($topCount / $totalCorrective) * 100) : 0;

            if ($pct > 30) {
                $insights[] = [
                    'icon' => 'wrench',
                    'insight_text' => "{$topType} systems generated {$pct}% of corrective work orders in the last 90 days ({$topCount} of {$totalCorrective}).",
                    'confidence' => min(95, 60 + $pct),
                    'category' => 'work_orders',
                    'action_label' => 'Review WO Trends',
                ];
            }
        }

        return $insights;
    }

    protected function analyzeSensorAnomalies(): array
    {
        $insights = [];

        $sensors = SensorSource::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->withCount(['readings as total_readings' => fn ($q) => $q->where('recorded_at', '>=', now()->subDays(7))])
            ->withCount(['readings as anomaly_readings' => fn ($q) => $q->where('is_anomaly', true)->where('recorded_at', '>=', now()->subDays(7))])
            ->get();

        foreach ($sensors as $sensor) {
            if ($sensor->total_readings > 0) {
                $anomalyRate = round(($sensor->anomaly_readings / $sensor->total_readings) * 100);

                if ($anomalyRate >= 15) {
                    $insights[] = [
                        'icon' => 'signal',
                        'insight_text' => "{$sensor->name} shows {$anomalyRate}% anomaly rate over the past 7 days — investigate potential equipment degradation.",
                        'confidence' => min(92, 55 + $anomalyRate),
                        'category' => 'sensors',
                        'action_label' => 'View Sensor Data',
                    ];
                }
            }
        }

        // Limit to top 2 sensor insights
        return array_slice($insights, 0, 2);
    }

    protected function analyzePmCompliance(): array
    {
        $insights = [];

        $totalPm = MaintenanceSchedule::where('tenant_id', $this->tenantId)->where('is_active', true)->count();
        $completedPm = MaintenanceSchedule::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->whereNotNull('last_completed_date')
            ->count();

        $overdue = MaintenanceSchedule::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->whereNotNull('next_due_date')
            ->where('next_due_date', '<', now())
            ->count();

        if ($totalPm > 0) {
            $compliance = round(($completedPm / $totalPm) * 100);

            if ($compliance < 90) {
                $insights[] = [
                    'icon' => 'calendar',
                    'insight_text' => "PM compliance is at {$compliance}% — {$overdue} schedule(s) are overdue. Timely preventive maintenance reduces emergency work orders by up to 40%.",
                    'confidence' => 88,
                    'category' => 'maintenance',
                    'action_label' => 'Review PM Schedules',
                ];
            }
        }

        return $insights;
    }

    protected function analyzeSlaBreach(): array
    {
        $insights = [];

        $currentMonth = WorkOrder::where('tenant_id', $this->tenantId)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        $currentBreached = WorkOrder::where('tenant_id', $this->tenantId)
            ->where('sla_breached', true)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        $lastMonth = WorkOrder::where('tenant_id', $this->tenantId)
            ->where('created_at', '>=', now()->subMonth()->startOfMonth())
            ->where('created_at', '<', now()->startOfMonth())
            ->count();

        $lastBreached = WorkOrder::where('tenant_id', $this->tenantId)
            ->where('sla_breached', true)
            ->where('created_at', '>=', now()->subMonth()->startOfMonth())
            ->where('created_at', '<', now()->startOfMonth())
            ->count();

        $currentRate = $currentMonth > 0 ? ($currentBreached / $currentMonth) * 100 : 0;
        $lastRate = $lastMonth > 0 ? ($lastBreached / $lastMonth) * 100 : 0;

        if ($currentRate > $lastRate && $currentBreached > 0) {
            $increase = round($currentRate - $lastRate);
            $insights[] = [
                'icon' => 'clock',
                'insight_text' => "SLA breach rate increased by {$increase}% compared to last month ({$currentBreached} breaches this month). Consider reallocating technician resources.",
                'confidence' => 82,
                'category' => 'sla',
                'action_label' => 'View SLA Report',
            ];
        } elseif ($currentBreached > 0) {
            $insights[] = [
                'icon' => 'clock',
                'insight_text' => "{$currentBreached} SLA breach(es) recorded this month with a " . round($currentRate) . "% breach rate. Monitor response times closely.",
                'confidence' => 75,
                'category' => 'sla',
                'action_label' => 'View SLA Report',
            ];
        }

        return $insights;
    }
}
