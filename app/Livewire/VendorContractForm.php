<?php

namespace App\Livewire;

use App\Models\VendorContract;
use Livewire\Component;

class VendorContractForm extends Component
{
    public int $vendorId;

    public ?int $contractId = null;

    public string $title = '';

    public string $contract_number = '';

    public string $start_date = '';

    public string $end_date = '';

    public ?string $monthly_cost = null;

    public ?string $annual_value = null;

    public ?string $nte_limit = null;

    public string $scope = '';

    public bool $auto_renew = false;

    public string $status = 'draft';

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'contract_number' => 'nullable|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'monthly_cost' => 'nullable|numeric|min:0',
            'annual_value' => 'nullable|numeric|min:0',
            'nte_limit' => 'nullable|numeric|min:0',
            'scope' => 'nullable|string|max:5000',
            'auto_renew' => 'boolean',
            'status' => 'required|in:draft,active,expired,terminated',
        ];
    }

    public function mount(int $vendorId, ?int $contractId = null): void
    {
        $this->vendorId = $vendorId;
        $this->contractId = $contractId;

        if ($contractId) {
            $contract = VendorContract::findOrFail($contractId);
            $this->title = $contract->title;
            $this->contract_number = $contract->contract_number ?? '';
            $this->start_date = $contract->start_date->format('Y-m-d');
            $this->end_date = $contract->end_date->format('Y-m-d');
            $this->monthly_cost = $contract->monthly_cost;
            $this->annual_value = $contract->annual_value;
            $this->nte_limit = $contract->nte_limit;
            $this->scope = $contract->scope ?? '';
            $this->auto_renew = $contract->auto_renew;
            $this->status = $contract->status;
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'vendor_id' => $this->vendorId,
            'tenant_id' => auth()->user()->tenant_id,
            'title' => $this->title,
            'contract_number' => $this->contract_number ?: null,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'monthly_cost' => $this->monthly_cost ?: null,
            'annual_value' => $this->annual_value ?: null,
            'nte_limit' => $this->nte_limit ?: null,
            'scope' => $this->scope ?: null,
            'auto_renew' => $this->auto_renew,
            'status' => $this->status,
        ];

        if ($this->contractId) {
            $contract = VendorContract::findOrFail($this->contractId);
            $contract->update($data);
        } else {
            VendorContract::create($data);
        }

        $this->dispatch('contractSaved');
    }

    public function render()
    {
        return view('livewire.vendor-contract-form');
    }
}
