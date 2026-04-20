<?php

namespace App\Livewire;

use App\Models\SensorReading;
use App\Models\SensorSource;
use Livewire\Component;

class SensorDashboard extends Component
{
    public ?int $selectedSensorId = null;

    public function mount(): void
    {
        $first = SensorSource::where('is_active', true)
            ->first();
        $this->selectedSensorId = $first?->id;
    }

    public function selectSensor(int $id): void
    {
        $this->selectedSensorId = $id;
    }

    public function refreshReadings(): void
    {
        // Livewire polling calls this method every 5 seconds.
        // Computed properties are automatically re-evaluated on each request,
        // so no explicit action is needed here — the method simply triggers a re-render.
    }

    public function getSensorsProperty()
    {
        return SensorSource::where('is_active', true)
            ->with('asset')
            ->get();
    }

    public function getSelectedSensorProperty(): ?SensorSource
    {
        if (! $this->selectedSensorId) {
            return null;
        }

        return SensorSource::with('asset.location')->find($this->selectedSensorId);
    }

    public function getReadingsProperty()
    {
        if (! $this->selectedSensorId) {
            return collect();
        }

        return SensorReading::where('sensor_source_id', $this->selectedSensorId)
            ->where('recorded_at', '>', now()->subDay())
            ->orderBy('recorded_at')
            ->get();
    }

    public function getAnomalyCountProperty(): int
    {
        return SensorReading::whereIn(
            'sensor_source_id',
            SensorSource::pluck('id')
        )
            ->where('is_anomaly', true)
            ->where('recorded_at', '>', now()->subDay())
            ->count();
    }

    /**
     * 7-day × 24-hour anomaly density grid. Values are anomaly counts per
     * (day, hour) bucket across all active sensors in the tenant.
     *
     * @return array{max:int, grid:array<int, array<int, int>>}
     */
    public function getAnomalyHeatmapProperty(): array
    {
        $sensorIds = SensorSource::pluck('id');
        $since = now()->subDays(7)->startOfHour();

        // Bucket in PHP so the same code runs on SQLite, MySQL, and Postgres
        // without driver-specific date functions.
        $readings = SensorReading::query()
            ->whereIn('sensor_source_id', $sensorIds)
            ->where('is_anomaly', true)
            ->where('recorded_at', '>=', $since)
            ->get(['recorded_at']);

        $grid = [];
        $max = 0;
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $grid[$day] = array_fill(0, 24, 0);
        }
        foreach ($readings as $r) {
            $day = $r->recorded_at->toDateString();
            $hour = (int) $r->recorded_at->format('G');
            if (isset($grid[$day])) {
                $grid[$day][$hour]++;
                $max = max($max, $grid[$day][$hour]);
            }
        }
        return ['max' => $max, 'grid' => $grid];
    }

    public function render()
    {
        return view('livewire.sensor-dashboard')
            ->layout('layouts.app', ['title' => 'IoT Sensors']);
    }
}
