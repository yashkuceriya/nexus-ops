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

        $rows = SensorReading::query()
            ->whereIn('sensor_source_id', $sensorIds)
            ->where('is_anomaly', true)
            ->where('recorded_at', '>=', $since)
            ->selectRaw("strftime('%Y-%m-%d', recorded_at) as d, CAST(strftime('%H', recorded_at) AS INTEGER) as h, COUNT(*) as c")
            ->groupBy('d', 'h')
            ->get();

        $grid = [];
        $max = 0;
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $grid[$day] = array_fill(0, 24, 0);
        }
        foreach ($rows as $r) {
            if (isset($grid[$r->d])) {
                $grid[$r->d][(int) $r->h] = (int) $r->c;
                $max = max($max, (int) $r->c);
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
