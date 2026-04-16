<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\SensorReading;
use Carbon\Carbon;

class AssetHealthService
{
    /**
     * Calculate a 0-100 health score for an asset using weighted factors.
     */
    public function calculateHealthScore(Asset $asset): int
    {
        $ageFactor = $this->calculateAgeFactor($asset);
        $conditionFactor = $this->calculateConditionFactor($asset);
        $woFrequencyFactor = $this->calculateWorkOrderFrequencyFactor($asset);
        $sensorAnomalyFactor = $this->calculateSensorAnomalyFactor($asset);
        $pmComplianceFactor = $this->calculatePmComplianceFactor($asset);

        $score = ($ageFactor * 0.20)
            + ($conditionFactor * 0.25)
            + ($woFrequencyFactor * 0.25)
            + ($sensorAnomalyFactor * 0.15)
            + ($pmComplianceFactor * 0.15);

        return (int) round(max(0, min(100, $score)));
    }

    /**
     * Return all assets with health data for a scatter plot matrix.
     */
    public function getHealthMatrix(int $tenantId): array
    {
        $assets = Asset::where('tenant_id', $tenantId)
            ->with(['sensorSources', 'maintenanceSchedules'])
            ->get();

        return $assets->map(function (Asset $asset) {
            return [
                'id' => $asset->id,
                'name' => $asset->name,
                'health_score' => $this->calculateHealthScore($asset),
                'criticality' => (float) ($asset->replacement_cost ?? 0),
                'system_type' => $asset->system_type ?? 'Unknown',
                'condition' => $asset->condition ?? 'unknown',
            ];
        })->toArray();
    }

    /**
     * Return monthly health score snapshots for trend analysis.
     */
    public function getHealthTrend(Asset $asset, int $months = 6): array
    {
        $trend = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            // Condition factor stays the same (snapshot of current)
            $conditionFactor = $this->calculateConditionFactor($asset);

            // Age factor adjusted for that month
            $ageFactor = $this->calculateAgeFactor($asset, $monthEnd);

            // WO frequency for the 90-day window ending at that month
            // (Work order types are corrective|preventive|inspection|emergency;
            //  'reactive' was a stale label that silently always returned 0.)
            $woCount = $asset->workOrders()
                ->whereIn('type', ['corrective', 'emergency'])
                ->whereBetween('created_at', [$monthEnd->copy()->subDays(90), $monthEnd])
                ->count();
            $woFactor = max(0, 100 - ($woCount * 15));

            // Sensor anomaly rate for 30-day window ending at that month
            $sensorIds = $asset->sensorSources->pluck('id');
            $totalReadings = SensorReading::whereIn('sensor_source_id', $sensorIds)
                ->whereBetween('recorded_at', [$monthEnd->copy()->subDays(30), $monthEnd])
                ->count();
            $anomalyReadings = SensorReading::whereIn('sensor_source_id', $sensorIds)
                ->whereBetween('recorded_at', [$monthEnd->copy()->subDays(30), $monthEnd])
                ->where('is_anomaly', true)
                ->count();
            $anomalyPct = $totalReadings > 0 ? ($anomalyReadings / $totalReadings) * 100 : 0;
            $sensorFactor = max(0, 100 - ($anomalyPct * 2));

            // PM compliance stays the same snapshot
            $pmFactor = $this->calculatePmComplianceFactor($asset);

            $score = ($ageFactor * 0.20)
                + ($conditionFactor * 0.25)
                + ($woFactor * 0.25)
                + ($sensorFactor * 0.15)
                + ($pmFactor * 0.15);

            $trend[] = [
                'month' => $date->format('M Y'),
                'score' => (int) round(max(0, min(100, $score))),
            ];
        }

        return $trend;
    }

    /**
     * Get detailed breakdown of health factors for an asset.
     */
    public function getHealthBreakdown(Asset $asset): array
    {
        return [
            'age' => (int) round($this->calculateAgeFactor($asset)),
            'condition' => (int) round($this->calculateConditionFactor($asset)),
            'work_orders' => (int) round($this->calculateWorkOrderFrequencyFactor($asset)),
            'sensor_anomaly' => (int) round($this->calculateSensorAnomalyFactor($asset)),
            'pm_compliance' => (int) round($this->calculatePmComplianceFactor($asset)),
        ];
    }

    private function calculateAgeFactor(Asset $asset, ?Carbon $asOf = null): float
    {
        $asOf = $asOf ?? now();

        if (! $asset->expected_life_years || ! $asset->install_date) {
            return 50.0;
        }

        $ageYears = $asset->install_date->diffInDays($asOf) / 365.25;
        $remainingLife = max(0, $asset->expected_life_years - $ageYears);

        return ($remainingLife / $asset->expected_life_years) * 100;
    }

    private function calculateConditionFactor(Asset $asset): float
    {
        return match ($asset->condition) {
            'excellent' => 100.0,
            'good' => 80.0,
            'fair' => 60.0,
            'poor' => 30.0,
            'critical' => 10.0,
            default => 50.0,
        };
    }

    private function calculateWorkOrderFrequencyFactor(Asset $asset): float
    {
        $woCount = $asset->workOrders()
            ->whereIn('type', ['corrective', 'emergency'])
            ->where('created_at', '>=', now()->subDays(90))
            ->count();

        return (float) max(0, 100 - ($woCount * 15));
    }

    private function calculateSensorAnomalyFactor(Asset $asset): float
    {
        $sensorIds = $asset->sensorSources->pluck('id');

        if ($sensorIds->isEmpty()) {
            return 100.0;
        }

        $totalReadings = SensorReading::whereIn('sensor_source_id', $sensorIds)
            ->where('recorded_at', '>=', now()->subDays(30))
            ->count();

        if ($totalReadings === 0) {
            return 100.0;
        }

        $anomalyReadings = SensorReading::whereIn('sensor_source_id', $sensorIds)
            ->where('recorded_at', '>=', now()->subDays(30))
            ->where('is_anomaly', true)
            ->count();

        $anomalyPct = ($anomalyReadings / $totalReadings) * 100;

        return (float) max(0, 100 - ($anomalyPct * 2));
    }

    private function calculatePmComplianceFactor(Asset $asset): float
    {
        $schedules = $asset->maintenanceSchedules()->where('is_active', true)->get();

        if ($schedules->isEmpty()) {
            return 100.0;
        }

        $completedOnTime = $schedules->filter(function ($schedule) {
            return $schedule->last_completed_date !== null;
        })->count();

        return ($completedOnTime / $schedules->count()) * 100;
    }
}
