<?php

namespace App\Livewire;

use App\Models\Asset;
use App\Models\SensorReading;
use App\Services\AssetHealthService;
use Livewire\Component;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AssetDetail extends Component
{
    public Asset $asset;

    public function mount(int $id): void
    {
        $this->asset = Asset::with(['project', 'location', 'sensorSources', 'maintenanceSchedules', 'issues'])
            ->findOrFail($id);
    }

    public function getChildrenProperty()
    {
        return $this->asset->children()
            ->orderBy('name')
            ->get();
    }

    public function getRecentWorkOrdersProperty()
    {
        return $this->asset->workOrders()
            ->with(['assignee'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }

    public function getAllWorkOrdersProperty()
    {
        return $this->asset->workOrders()
            ->with(['assignee'])
            ->orderByRaw("CASE status WHEN 'open' THEN 1 WHEN 'in_progress' THEN 2 WHEN 'on_hold' THEN 3 WHEN 'completed' THEN 4 WHEN 'cancelled' THEN 5 ELSE 6 END")
            ->orderByDesc('created_at')
            ->get();
    }

    public function getRecentReadingsProperty()
    {
        $sensorIds = $this->asset->sensorSources->pluck('id');

        return SensorReading::whereIn('sensor_source_id', $sensorIds)
            ->where('recorded_at', '>=', now()->subDay())
            ->orderByDesc('recorded_at')
            ->get()
            ->groupBy('sensor_source_id');
    }

    public function getOpenWorkOrdersProperty(): int
    {
        return $this->asset->workOrders()
            ->whereIn('status', ['open', 'in_progress'])
            ->count();
    }

    public function getHealthScoreProperty(): int
    {
        return app(AssetHealthService::class)->calculateHealthScore($this->asset);
    }

    public function getHealthBreakdownProperty(): array
    {
        return app(AssetHealthService::class)->getHealthBreakdown($this->asset);
    }

    public function getMaintenanceDueProperty()
    {
        return $this->asset->maintenanceSchedules()
            ->where('is_active', true)
            ->whereNotNull('next_due_date')
            ->orderBy('next_due_date')
            ->first();
    }

    public function generateQrCode(): string
    {
        $value = $this->asset->qr_code ?? $this->asset->generateQrCode();

        return QrCode::size(200)
            ->style('round')
            ->eye('circle')
            ->margin(1)
            ->generate($value);
    }

    public function render()
    {
        return view('livewire.asset-detail')
            ->layout('layouts.app', ['title' => $this->asset->name]);
    }
}
