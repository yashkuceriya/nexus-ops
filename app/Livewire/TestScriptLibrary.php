<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Asset;
use App\Models\TestScript;
use App\Services\TestExecution\TestExecutionService;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Library of commissioning FPT scripts visible to the tenant — both
 * tenant-authored and NexusOps system templates.
 */
class TestScriptLibrary extends Component
{
    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $systemType = '';

    #[Url(history: true)]
    public string $source = '';

    #[Url(history: true)]
    public string $cxLevel = '';

    public bool $showCreate = false;

    public string $newName = '';

    public string $newSystemType = 'chiller';

    public string $newCxLevel = 'L3';

    public string $newDescription = '';

    public ?int $estimatedMinutes = 30;

    protected function rules(): array
    {
        return [
            'newName' => ['required', 'string', 'min:3', 'max:255'],
            'newSystemType' => ['required', 'string'],
            'newCxLevel' => ['nullable', 'in:L1,L2,L3,L4,L5'],
            'newDescription' => ['nullable', 'string'],
            'estimatedMinutes' => ['nullable', 'integer', 'min:1', 'max:480'],
        ];
    }

    public function getScriptsProperty()
    {
        $tenantId = auth()->user()->tenant_id;

        return TestScript::availableTo($tenantId)
            ->when($this->search !== '', function ($q): void {
                $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $this->search).'%';
                $q->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhere('system_type', 'like', $term);
                });
            })
            ->when($this->systemType !== '', fn ($q) => $q->where('system_type', $this->systemType))
            ->when($this->cxLevel !== '', fn ($q) => $q->where('cx_level', $this->cxLevel))
            ->when($this->source === 'system', fn ($q) => $q->where('is_system', true))
            ->when($this->source === 'tenant', fn ($q) => $q->where('is_system', false))
            ->withCount(['steps', 'executions'])
            ->orderBy('is_system')
            ->orderBy('name')
            ->paginate(20);
    }

    public function getSystemTypesProperty(): array
    {
        return [
            'chiller' => 'Chiller',
            'crah' => 'CRAH / CRAC',
            'ahu' => 'Air Handling Unit',
            'ups' => 'UPS',
            'generator' => 'Generator',
            'ats' => 'Automatic Transfer Switch',
            'pdu' => 'PDU',
            'fire_pump' => 'Fire Pump',
            'cooling_tower' => 'Cooling Tower',
            'pump' => 'Pump',
            'vav_box' => 'VAV Box',
            'boiler' => 'Boiler',
            'bms' => 'BMS / Controls',
            'electrical_panel' => 'Electrical Panel',
            'other' => 'Other',
        ];
    }

    public function create(): void
    {
        $data = $this->validate();

        $script = TestScript::create([
            'tenant_id' => auth()->user()->tenant_id,
            'created_by' => auth()->id(),
            'name' => $data['newName'],
            'slug' => Str::slug($data['newName']).'-'.substr((string) Str::uuid(), 0, 6),
            'description' => $data['newDescription'] ?: null,
            'system_type' => $data['newSystemType'],
            'cx_level' => $data['newCxLevel'] ?: null,
            'status' => TestScript::STATUS_DRAFT,
            'version' => 1,
            'is_system' => false,
            'estimated_duration_minutes' => $data['estimatedMinutes'],
        ]);

        $this->reset(['newName', 'newDescription', 'showCreate']);
        $this->newSystemType = 'chiller';
        $this->newCxLevel = 'L3';
        $this->estimatedMinutes = 30;

        $this->redirectRoute('fpt.scripts.edit', ['scriptId' => $script->id], navigate: true);
    }

    public function cloneScript(int $scriptId, TestExecutionService $service): void
    {
        $tenantId = auth()->user()->tenant_id;
        $source = TestScript::availableTo($tenantId)->findOrFail($scriptId);

        $clone = $service->cloneToTenant($source, auth()->user());

        $this->dispatch('toast', type: 'success', message: 'Cloned to tenant as draft v1.');
        $this->redirectRoute('fpt.scripts.edit', ['scriptId' => $clone->id], navigate: true);
    }

    public function startExecution(int $scriptId, int $assetId, TestExecutionService $service): void
    {
        $tenantId = auth()->user()->tenant_id;

        $script = TestScript::availableTo($tenantId)->findOrFail($scriptId);
        $asset = Asset::where('tenant_id', $tenantId)->findOrFail($assetId);

        try {
            $execution = $service->start($script, $asset, auth()->user());
            $this->redirectRoute('fpt.run', ['executionId' => $execution->id], navigate: true);
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.test-script-library');
    }
}
