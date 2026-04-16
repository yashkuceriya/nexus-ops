<?php

namespace App\Livewire;

use App\Models\Asset;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\WorkOrder\WorkOrderService;
use Livewire\Component;

class WorkOrderForm extends Component
{
    public bool $showModal = false;

    public bool $editMode = false;

    public ?int $workOrderId = null;

    public ?int $projectId = null;

    public ?int $assetId = null;

    public string $title = '';

    public string $description = '';

    public string $priority = 'medium';

    public string $type = 'corrective';

    public ?int $assignedTo = null;

    public ?int $slaHours = null;

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'projectId' => 'nullable|integer|exists:projects,id',
            'assetId' => 'nullable|integer|exists:assets,id',
            'priority' => 'required|in:emergency,critical,high,medium,low',
            'type' => 'required|in:corrective,preventive,inspection,sensor_alert,request',
            'assignedTo' => 'nullable|integer|exists:users,id',
            'slaHours' => 'nullable|integer|min:1|max:720',
        ];
    }

    protected array $validationAttributes = [
        'projectId' => 'project',
        'assetId' => 'asset',
        'assignedTo' => 'assignee',
        'slaHours' => 'SLA hours',
    ];

    public function getProjectsProperty()
    {
        return Project::orderBy('name')
            ->get(['id', 'name']);
    }

    public function getAssetsProperty()
    {
        $query = Asset::query();

        if ($this->projectId) {
            $query->where('project_id', $this->projectId);
        }

        return $query->orderBy('name')->get(['id', 'name', 'asset_tag']);
    }

    public function getUsersProperty()
    {
        return User::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function updatedProjectId(): void
    {
        $this->assetId = null;
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $this->resetForm();
        $this->editMode = true;

        $wo = WorkOrder::findOrFail($id);

        $this->workOrderId = $wo->id;
        $this->projectId = $wo->project_id;
        $this->assetId = $wo->asset_id;
        $this->title = $wo->title;
        $this->description = $wo->description ?? '';
        $this->priority = $wo->priority;
        $this->type = $wo->type;
        $this->assignedTo = $wo->assigned_to;
        $this->slaHours = $wo->sla_hours;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $service = app(WorkOrderService::class);
        $tenantId = auth()->user()->tenant_id;

        $data = [
            'project_id' => $this->projectId,
            'asset_id' => $this->assetId,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'type' => $this->type,
            'assigned_to' => $this->assignedTo,
            'sla_hours' => $this->slaHours,
        ];

        if ($this->editMode && $this->workOrderId) {
            $service->update($tenantId, $this->workOrderId, $data);
            session()->flash('success', 'Work order updated successfully.');
            $this->dispatch('toast', type: 'success', message: 'Work order updated successfully.');
        } else {
            $service->create($tenantId, auth()->id(), $data);
            session()->flash('success', 'Work order created successfully.');
            $this->dispatch('toast', type: 'success', message: 'Work order created successfully.');
        }

        $this->showModal = false;
        $this->dispatch('work-order-saved');
    }

    public function close(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->workOrderId = null;
        $this->projectId = null;
        $this->assetId = null;
        $this->title = '';
        $this->description = '';
        $this->priority = 'medium';
        $this->type = 'corrective';
        $this->assignedTo = null;
        $this->slaHours = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.work-order-form');
    }
}
