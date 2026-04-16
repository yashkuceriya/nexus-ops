<?php

namespace App\Livewire;

use App\Models\Vendor;
use Livewire\Component;

class VendorList extends Component
{
    public string $search = '';
    public string $tradeFilter = '';
    public string $activeFilter = '';

    public bool $showForm = false;
    public ?int $editingVendorId = null;

    protected $listeners = ['vendorSaved' => 'closeForm', 'closeModal' => 'closeForm'];

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->editingVendorId = null;
    }

    public function openCreateForm(): void
    {
        $this->editingVendorId = null;
        $this->showForm = true;
    }

    public function openEditForm(int $id): void
    {
        $this->editingVendorId = $id;
        $this->showForm = true;
    }

    public function render()
    {
        $query = Vendor::withCount(['contracts as active_contracts_count' => function ($q) {
                $q->where('status', 'active');
            }]);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('contact_name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        if ($this->tradeFilter) {
            $query->whereJsonContains('trade_specialties', $this->tradeFilter);
        }

        if ($this->activeFilter !== '') {
            $query->where('is_active', $this->activeFilter === '1');
        }

        $vendors = $query->orderBy('name')->get();

        return view('livewire.vendor-list', ['vendors' => $vendors])
            ->layout('layouts.app', ['title' => 'Vendors']);
    }
}
