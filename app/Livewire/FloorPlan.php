<?php

namespace App\Livewire;

use App\Models\Asset;
use App\Models\Location;
use App\Models\Project;
use App\Models\SensorReading;
use Livewire\Component;

class FloorPlan extends Component
{
    public ?int $selectedAssetId = null;
    public ?int $selectedFloor = null;
    public array $floors = [];

    public function mount(): void
    {
        $project = Project::orderBy('id')
            ->first();

        if ($project) {
            $this->floors = Location::where('project_id', $project->id)
                ->orderBy('name')
                ->get()
                ->map(fn ($l) => ['id' => $l->id, 'name' => $l->name])
                ->toArray();

            if (count($this->floors) > 0) {
                $this->selectedFloor = $this->floors[0]['id'];
            }
        }
    }

    public function getAssetsProperty()
    {
        $query = Asset::with(['location', 'sensorSources.readings' => function ($q) {
                $q->where('recorded_at', '>=', now()->subHour())
                    ->orderByDesc('recorded_at');
            }, 'workOrders' => function ($q) {
                $q->whereIn('status', ['open', 'in_progress']);
            }]);

        if ($this->selectedFloor) {
            $query->where('location_id', $this->selectedFloor);
        }

        return $query->get();
    }

    public function getAssetPinsProperty(): array
    {
        $assets = $this->assets;
        $pins = [];

        // Predefined positions on the SVG floor plan grid
        $positions = [
            ['x' => 120, 'y' => 140], ['x' => 220, 'y' => 140], ['x' => 320, 'y' => 140],
            ['x' => 420, 'y' => 140], ['x' => 520, 'y' => 140], ['x' => 620, 'y' => 140],
            ['x' => 120, 'y' => 240], ['x' => 220, 'y' => 240], ['x' => 320, 'y' => 240],
            ['x' => 420, 'y' => 240], ['x' => 520, 'y' => 240], ['x' => 620, 'y' => 240],
            ['x' => 170, 'y' => 370], ['x' => 270, 'y' => 370], ['x' => 370, 'y' => 370],
            ['x' => 470, 'y' => 370], ['x' => 570, 'y' => 370], ['x' => 670, 'y' => 370],
            ['x' => 170, 'y' => 450], ['x' => 270, 'y' => 450], ['x' => 370, 'y' => 450],
            ['x' => 470, 'y' => 450], ['x' => 570, 'y' => 450], ['x' => 670, 'y' => 450],
        ];

        foreach ($assets as $index => $asset) {
            $pos = $positions[$index % count($positions)];
            $hasSensors = $asset->sensorSources->isNotEmpty();
            $hasAnomaly = $hasSensors && $asset->sensorSources->flatMap->readings->where('is_anomaly', true)->isNotEmpty();
            $hasOpenWo = $asset->workOrders->isNotEmpty();

            if (! $hasSensors) {
                $color = 'gray';
                $status = 'No sensors';
            } elseif ($hasAnomaly) {
                $color = 'red';
                $status = 'Sensor alert';
            } elseif ($hasOpenWo) {
                $color = 'amber';
                $status = 'Open work orders';
            } else {
                $color = 'green';
                $status = 'Normal';
            }

            $lastReading = $hasSensors
                ? $asset->sensorSources->flatMap->readings->sortByDesc('recorded_at')->first()
                : null;

            $pins[] = [
                'id' => $asset->id,
                'name' => $asset->name,
                'x' => $pos['x'],
                'y' => $pos['y'],
                'color' => $color,
                'status' => $status,
                'condition' => ucfirst($asset->condition ?? 'Unknown'),
                'last_reading' => $lastReading ? number_format($lastReading->value, 1) . ' ' . ($lastReading->sensorSource->unit ?? '') : 'N/A',
                'open_wo_count' => $asset->workOrders->count(),
                'system_type' => $asset->system_type ?? 'Unknown',
            ];
        }

        return $pins;
    }

    public function selectAsset(int $id): void
    {
        $this->selectedAssetId = $this->selectedAssetId === $id ? null : $id;
    }

    public function getSelectedAssetDetailProperty(): ?array
    {
        if (! $this->selectedAssetId) {
            return null;
        }

        foreach ($this->assetPins as $pin) {
            if ($pin['id'] === $this->selectedAssetId) {
                return $pin;
            }
        }

        return null;
    }

    public function render()
    {
        return view('livewire.floor-plan')
            ->layout('layouts.app', ['title' => 'Floor Plan']);
    }
}
