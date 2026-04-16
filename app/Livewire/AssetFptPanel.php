<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Asset;
use App\Models\TestExecution;
use App\Models\TestScript;
use App\Services\TestExecution\TestExecutionService;
use Livewire\Component;

/**
 * Asset-scoped FPT panel — rendered as a tab inside AssetDetail.
 *
 * Shows every execution that has ever run against this asset and lets the
 * user launch a new run against any published script matching the asset's
 * system type.
 */
class AssetFptPanel extends Component
{
    public Asset $asset;

    public ?int $scriptToRun = null;

    public function mount(int $assetId): void
    {
        $this->asset = Asset::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($assetId);
    }

    public function getExecutionsProperty()
    {
        return TestExecution::where('tenant_id', $this->asset->tenant_id)
            ->where('asset_id', $this->asset->id)
            ->with(['starter:id,name', 'witness:id,name'])
            ->latest('started_at')
            ->limit(20)
            ->get();
    }

    public function getAvailableScriptsProperty()
    {
        $tenantId = auth()->user()->tenant_id;

        return TestScript::availableTo($tenantId)
            ->published()
            ->when($this->asset->category || $this->asset->system_type, function ($q): void {
                $candidates = array_filter([
                    $this->asset->category,
                    $this->asset->system_type ? strtolower($this->asset->system_type) : null,
                ]);
                $q->where(function ($inner) use ($candidates): void {
                    foreach ($candidates as $c) {
                        $inner->orWhere('system_type', $c);
                    }
                    $inner->orWhere('system_type', 'other');
                });
            })
            ->orderBy('name')
            ->get(['id', 'name', 'version', 'system_type', 'is_system']);
    }

    public function runScript(TestExecutionService $service): void
    {
        if (! $this->scriptToRun) {
            return;
        }

        $tenantId = auth()->user()->tenant_id;

        $script = TestScript::availableTo($tenantId)->findOrFail($this->scriptToRun);

        try {
            $execution = $service->start($script, $this->asset, auth()->user());
            $this->redirectRoute('fpt.run', ['executionId' => $execution->id], navigate: true);
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.asset-fpt-panel');
    }
}
