<?php

namespace App\Livewire;

use App\Models\Vendor;
use App\Models\WorkOrder;
use Livewire\Component;

class VendorDetail extends Component
{
    public Vendor $vendor;

    public string $activeTab = 'contracts';

    public bool $showVendorForm = false;

    public bool $showContractForm = false;

    public ?int $editingContractId = null;

    protected $listeners = [
        'vendorSaved' => 'refreshVendor',
        'contractSaved' => 'closeContractForm',
        'closeModal' => 'closeAllForms',
    ];

    public function mount(int $id): void
    {
        $this->vendor = Vendor::with(['contracts'])
            ->findOrFail($id);
    }

    public function refreshVendor(): void
    {
        $this->vendor->refresh();
        $this->vendor->load(['contracts']);
        $this->showVendorForm = false;
    }

    public function closeContractForm(): void
    {
        $this->showContractForm = false;
        $this->editingContractId = null;
        $this->vendor->load(['contracts']);
    }

    public function closeAllForms(): void
    {
        $this->showVendorForm = false;
        $this->showContractForm = false;
        $this->editingContractId = null;
    }

    public function openEditVendor(): void
    {
        $this->showVendorForm = true;
    }

    public function openAddContract(): void
    {
        $this->editingContractId = null;
        $this->showContractForm = true;
    }

    public function openEditContract(int $id): void
    {
        $this->editingContractId = $id;
        $this->showContractForm = true;
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function getContractsProperty()
    {
        return $this->vendor->contracts()->orderByDesc('start_date')->get();
    }

    public function getWorkOrdersProperty()
    {
        return WorkOrder::where('vendor_id', $this->vendor->id)
            ->with(['project:id,name', 'asset:id,name', 'assignee:id,name'])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();
    }

    public function getPerformanceMetricsProperty(): array
    {
        $responseTime = $this->vendor->avg_response_hours ?? 0;
        $completionTime = $this->vendor->avg_completion_hours ?? 0;
        $totalWos = $this->vendor->total_work_orders;
        $totalSpend = (float) $this->vendor->total_spend;
        $rating = (float) ($this->vendor->rating ?? 0);

        // Normalize to 0-100 for radar chart
        return [
            'response_time' => min(100, max(0, 100 - ($responseTime / 48 * 100))),
            'completion_time' => min(100, max(0, 100 - ($completionTime / 168 * 100))),
            'volume' => min(100, ($totalWos / max(1, 50)) * 100),
            'cost_efficiency' => $totalWos > 0 ? min(100, max(0, 100 - (($totalSpend / $totalWos) / 5000 * 100))) : 50,
            'quality' => ($rating / 5) * 100,
        ];
    }

    public function render()
    {
        return view('livewire.vendor-detail')
            ->layout('layouts.app', ['title' => $this->vendor->name]);
    }
}
