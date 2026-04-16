<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\SensorSource;
use App\Models\WorkOrder;
use Livewire\Component;

class FacilityMap extends Component
{
    public int $tenantId;

    public function mount(): void
    {
        $this->tenantId = auth()->user()?->tenant_id ?? 0;
    }

    public function getMarkersProperty(): array
    {
        $projects = Project::all();

        $demoCoords = [
            ['lat' => 39.0438, 'lng' => -77.4874, 'city' => 'Ashburn', 'state' => 'VA', 'type' => 'Data Center'],
            ['lat' => 42.3601, 'lng' => -71.0589, 'city' => 'Boston', 'state' => 'MA', 'type' => 'Medical Center'],
            ['lat' => 42.3736, 'lng' => -71.1097, 'city' => 'Cambridge', 'state' => 'MA', 'type' => 'Research Lab'],
        ];

        $markers = [];

        foreach ($projects as $index => $project) {
            $coords = $demoCoords[$index % count($demoCoords)];

            $openWos = WorkOrder::where('project_id', $project->id)
                ->whereNotIn('status', ['completed', 'verified', 'cancelled'])
                ->count();

            $sensorAlerts = SensorSource::whereHas('asset', fn ($q) => $q->where('project_id', $project->id))
                ->where('is_active', true)
                ->whereHas('readings', fn ($q) => $q->where('is_anomaly', true)->where('recorded_at', '>', now()->subHour()))
                ->count();

            $markers[] = [
                'id' => $project->id,
                'name' => $project->name,
                'lat' => $coords['lat'],
                'lng' => $coords['lng'],
                'readiness_score' => (float) $project->readiness_score,
                'open_wos' => $openWos,
                'sensor_alerts' => $sensorAlerts,
                'status' => $project->status,
                'city' => $project->city ?: $coords['city'],
                'state' => $project->state ?: $coords['state'],
                'type' => $coords['type'],
            ];
        }

        return $markers;
    }

    public function render()
    {
        return view('livewire.facility-map')
            ->layout('layouts.app', ['title' => 'Facility Map']);
    }
}
