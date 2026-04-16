<?php

namespace App\Livewire;

use App\Models\Asset;
use Livewire\Component;
use Livewire\WithPagination;

class AssetList extends Component
{
    use WithPagination;

    public string $systemFilter = '';

    public string $conditionFilter = '';

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $assets = Asset::with(['project', 'location', 'sensorSources'])
            ->when($this->systemFilter, fn ($q) => $q->where('system_type', $this->systemFilter))
            ->when($this->conditionFilter, fn ($q) => $q->where('condition', $this->conditionFilter))
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.asset-list', ['assets' => $assets])
            ->layout('layouts.app', ['title' => 'Assets']);
    }
}
