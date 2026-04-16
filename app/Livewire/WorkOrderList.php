<?php

namespace App\Livewire;

use App\Models\WorkOrder;
use Livewire\Component;
use Livewire\WithPagination;

class WorkOrderList extends Component
{
    use WithPagination;

    public string $statusFilter = '';
    public string $priorityFilter = '';
    public string $typeFilter = '';
    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $workOrders = WorkOrder::with(['assignee', 'project', 'asset'])
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->priorityFilter, fn ($q) => $q->where('priority', $this->priorityFilter))
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->orderByRaw("CASE priority WHEN 'emergency' THEN 1 WHEN 'critical' THEN 2 WHEN 'high' THEN 3 WHEN 'medium' THEN 4 WHEN 'low' THEN 5 ELSE 6 END")
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.work-order-list', ['workOrders' => $workOrders])
            ->layout('layouts.app', ['title' => 'Work Orders']);
    }
}
