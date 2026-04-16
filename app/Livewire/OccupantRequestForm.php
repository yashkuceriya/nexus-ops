<?php

namespace App\Livewire;

use App\Events\NewOccupantRequest;
use App\Models\Location;
use App\Models\OccupantRequest;
use App\Models\Project;
use App\Models\Tenant;
use Livewire\Component;

class OccupantRequestForm extends Component
{
    public ?int $tenantId = null;
    public ?int $projectId = null;
    public ?int $locationId = null;
    public string $category = '';
    public string $description = '';
    public string $requesterName = '';
    public string $requesterEmail = '';
    public string $requesterPhone = '';

    public bool $submitted = false;
    public string $trackingToken = '';

    protected function rules(): array
    {
        return [
            'tenantId' => 'required|integer|exists:tenants,id',
            'projectId' => 'required|integer|exists:projects,id',
            'locationId' => 'nullable|integer|exists:locations,id',
            'category' => 'required|in:hvac,plumbing,electrical,cleaning,pest_control,other',
            'description' => 'required|string|min:10|max:2000',
            'requesterName' => 'required|string|max:255',
            'requesterEmail' => 'required|email|max:255',
            'requesterPhone' => 'nullable|string|max:20',
        ];
    }

    protected array $validationAttributes = [
        'tenantId' => 'organization',
        'projectId' => 'building',
        'locationId' => 'location',
        'requesterName' => 'name',
        'requesterEmail' => 'email',
        'requesterPhone' => 'phone',
    ];

    public function mount(): void
    {
        // Default to first tenant for demo
        $tenant = Tenant::where('is_active', true)->first();
        if ($tenant) {
            $this->tenantId = $tenant->id;
        }
    }

    public function getTenantsProperty()
    {
        return Tenant::where('is_active', true)->orderBy('name')->get(['id', 'name']);
    }

    public function getProjectsProperty()
    {
        if (! $this->tenantId) {
            return collect();
        }

        return Project::where('tenant_id', $this->tenantId)->orderBy('name')->get(['id', 'name']);
    }

    public function getLocationsProperty()
    {
        if (! $this->projectId) {
            return collect();
        }

        return Location::where('project_id', $this->projectId)->orderBy('name')->get(['id', 'name', 'type']);
    }

    public function updatedTenantId(): void
    {
        $this->projectId = null;
        $this->locationId = null;
    }

    public function updatedProjectId(): void
    {
        $this->locationId = null;
    }

    public function submit(): void
    {
        $this->validate();

        $request = OccupantRequest::create([
            'tenant_id' => $this->tenantId,
            'tracking_token' => OccupantRequest::generateTrackingToken(),
            'requester_name' => $this->requesterName,
            'requester_email' => $this->requesterEmail,
            'requester_phone' => $this->requesterPhone ?: null,
            'project_id' => $this->projectId,
            'location_id' => $this->locationId ?: null,
            'category' => $this->category,
            'description' => $this->description,
            'status' => 'submitted',
        ]);

        NewOccupantRequest::dispatch(
            tenantId: $request->tenant_id,
            requestId: $request->id,
            category: $request->category,
            location: $request->location?->name,
            description: $request->description,
        );

        $this->trackingToken = $request->tracking_token;
        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.occupant-request-form')
            ->layout('layouts.public', ['title' => 'Submit a Request']);
    }
}
