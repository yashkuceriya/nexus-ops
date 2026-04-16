<?php

namespace App\Livewire;

use App\Models\Asset;
use App\Services\AssetHealthService;
use Livewire\Component;

class AssetHealthMatrix extends Component
{
    public int $tenantId;

    public function mount(): void
    {
        $this->tenantId = auth()->user()->tenant_id;
    }

    public function getMatrixDataProperty(): array
    {
        $service = app(AssetHealthService::class);

        return $service->getHealthMatrix($this->tenantId);
    }

    public function getSystemTypesProperty(): array
    {
        return collect($this->matrixData)
            ->pluck('system_type')
            ->unique()
            ->values()
            ->toArray();
    }

    public function getDangerZoneCountProperty(): int
    {
        $matrix = $this->matrixData;

        if (empty($matrix)) {
            return 0;
        }

        $maxCriticality = max(array_column($matrix, 'criticality'));
        $criticalityThreshold = $maxCriticality > 0 ? $maxCriticality * 0.5 : 0;

        return collect($matrix)
            ->filter(fn ($a) => $a['health_score'] < 40 && $a['criticality'] > $criticalityThreshold)
            ->count();
    }

    public function getAssetTableProperty(): array
    {
        $service = app(AssetHealthService::class);
        $assets = Asset::with(['sensorSources', 'maintenanceSchedules'])
            ->get();

        return $assets->map(function (Asset $asset) use ($service) {
            $breakdown = $service->getHealthBreakdown($asset);

            return [
                'id' => $asset->id,
                'name' => $asset->name,
                'health_score' => $service->calculateHealthScore($asset),
                'condition' => $asset->condition ?? 'unknown',
                'system_type' => $asset->system_type ?? 'Unknown',
                'factors' => $breakdown,
            ];
        })
            ->sortBy('health_score')
            ->values()
            ->toArray();
    }

    public function getSystemTypeColor(int $index): string
    {
        $colors = [
            '#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6',
            '#ec4899', '#14b8a6', '#f97316', '#6366f1', '#84cc16',
        ];

        return $colors[$index % count($colors)];
    }

    public function render()
    {
        return view('livewire.asset-health-matrix')
            ->layout('layouts.app', ['title' => 'Health Matrix']);
    }
}
