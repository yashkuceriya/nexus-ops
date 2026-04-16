<?php

namespace App\Livewire;

use App\Models\Vendor;
use Livewire\Component;

class VendorForm extends Component
{
    public ?int $vendorId = null;

    public string $name = '';
    public string $contact_name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $city = '';
    public string $state = '';
    public string $zip = '';
    public array $trade_specialties = [];
    public string $license_number = '';
    public ?string $insurance_expiry = null;
    public string $notes = '';

    public const AVAILABLE_TRADES = [
        'HVAC', 'Electrical', 'Plumbing', 'Fire/Life Safety',
        'General Maintenance', 'Roofing', 'Painting', 'Landscaping',
        'Janitorial', 'Elevator', 'Security Systems', 'IT/Telecom',
    ];

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'zip' => 'nullable|string|max:20',
            'trade_specialties' => 'array',
            'license_number' => 'nullable|string|max:100',
            'insurance_expiry' => 'nullable|date',
            'notes' => 'nullable|string|max:5000',
        ];
    }

    public function mount(?int $vendorId = null): void
    {
        $this->vendorId = $vendorId;

        if ($vendorId) {
            $vendor = Vendor::findOrFail($vendorId);
            $this->name = $vendor->name;
            $this->contact_name = $vendor->contact_name ?? '';
            $this->email = $vendor->email ?? '';
            $this->phone = $vendor->phone ?? '';
            $this->address = $vendor->address ?? '';
            $this->city = $vendor->city ?? '';
            $this->state = $vendor->state ?? '';
            $this->zip = $vendor->zip ?? '';
            $this->trade_specialties = $vendor->trade_specialties ?? [];
            $this->license_number = $vendor->license_number ?? '';
            $this->insurance_expiry = $vendor->insurance_expiry?->format('Y-m-d');
            $this->notes = $vendor->notes ?? '';
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $this->name,
            'contact_name' => $this->contact_name ?: null,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'address' => $this->address ?: null,
            'city' => $this->city ?: null,
            'state' => $this->state ?: null,
            'zip' => $this->zip ?: null,
            'trade_specialties' => $this->trade_specialties,
            'license_number' => $this->license_number ?: null,
            'insurance_expiry' => $this->insurance_expiry ?: null,
            'notes' => $this->notes ?: null,
        ];

        if ($this->vendorId) {
            $vendor = Vendor::findOrFail($this->vendorId);
            $vendor->update($data);
        } else {
            Vendor::create($data);
        }

        $this->dispatch('vendorSaved');
    }

    public function render()
    {
        return view('livewire.vendor-form');
    }
}
