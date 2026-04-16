<?php

namespace App\Livewire;

use App\Models\Asset;
use App\Models\Project;
use App\Models\SensorSource;
use App\Models\WorkOrder;
use Livewire\Component;

class DataTicker extends Component
{
    public int $tenantId;

    public function mount(): void
    {
        $this->tenantId = auth()->user()?->tenant_id ?? 0;
    }

    public function getItemsProperty(): array
    {
        $items = [];

        // Average readiness score
        $avgReadiness = Project::avg('readiness_score');
        $items[] = [
            'label' => 'Portfolio Readiness',
            'value' => round($avgReadiness ?? 0, 1),
            'unit' => '%',
            'trend' => 'up',
        ];

        // Total open work orders
        $openWos = WorkOrder::whereNotIn('status', ['completed', 'verified', 'cancelled'])
            ->count();
        $items[] = [
            'label' => 'Open Work Orders',
            'value' => $openWos,
            'unit' => '',
            'trend' => $openWos > 10 ? 'up' : 'down',
        ];

        // Total assets
        $totalAssets = Asset::count();
        $items[] = [
            'label' => 'Managed Assets',
            'value' => $totalAssets,
            'unit' => '',
            'trend' => 'stable',
        ];

        // Active sensors
        $activeSensors = SensorSource::where('is_active', true)->count();
        $items[] = [
            'label' => 'Active Sensors',
            'value' => $activeSensors,
            'unit' => '',
            'trend' => 'stable',
        ];

        // Sensor alerts
        $sensorAlerts = SensorSource::where('is_active', true)
            ->whereHas('readings', fn ($q) => $q->where('is_anomaly', true)->where('recorded_at', '>', now()->subHour()))
            ->count();
        $items[] = [
            'label' => 'Sensor Alerts',
            'value' => $sensorAlerts,
            'unit' => '',
            'trend' => $sensorAlerts > 0 ? 'up' : 'down',
        ];

        // Latest sensor reading (pick the most recently read sensor)
        $latestSensor = SensorSource::where('is_active', true)
            ->whereNotNull('last_value')
            ->orderByDesc('last_reading_at')
            ->first();
        if ($latestSensor) {
            $items[] = [
                'label' => $latestSensor->name,
                'value' => round((float) $latestSensor->last_value, 1),
                'unit' => $latestSensor->unit ?? '',
                'trend' => 'stable',
            ];
        }

        // SLA breaches
        $slaBreached = WorkOrder::where('sla_breached', true)->count();
        $items[] = [
            'label' => 'SLA Breaches',
            'value' => $slaBreached,
            'unit' => '',
            'trend' => $slaBreached > 0 ? 'up' : 'down',
        ];

        return $items;
    }

    public function render()
    {
        return view('livewire.data-ticker');
    }
}
