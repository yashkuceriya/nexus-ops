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

    public function render()
    {
        return view('livewire.sensor-dashboard')
            ->layout('layouts.app', ['title' => 'IoT Sensors']);
    }
}
