<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogViewer extends Component
{
    use WithPagination;

    public string $actionFilter = '';

    public string $entityFilter = '';

    public string $userFilter = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingActionFilter(): void
    {
        $this->resetPage();
    }

    public function updatingEntityFilter(): void
    {
        $this->resetPage();
    }

    public function updatingUserFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function getActionsProperty(): array
    {
        return AuditLog::distinct()
            ->pluck('action')
            ->sort()
            ->values()
            ->toArray();
    }

    public function getEntityTypesProperty(): array
    {
        return AuditLog::distinct()
            ->pluck('auditable_type')
            ->sort()
            ->values()
            ->toArray();
    }

    public function getUsersProperty()
    {
        $userIds = AuditLog::distinct()
            ->pluck('user_id')
            ->filter();

        return User::whereIn('id', $userIds)->orderBy('name')->get(['id', 'name']);
    }

    public function render()
    {
        $logs = AuditLog::with('user')
            ->when($this->actionFilter, fn ($q) => $q->where('action', $this->actionFilter))
            ->when($this->entityFilter, fn ($q) => $q->where('auditable_type', $this->entityFilter))
            ->when($this->userFilter, fn ($q) => $q->where('user_id', $this->userFilter))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('action', 'like', "%{$this->search}%")
                        ->orWhere('auditable_type', 'like', "%{$this->search}%")
                        ->orWhere('ip_address', 'like', "%{$this->search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('livewire.audit-log-viewer', ['logs' => $logs])
            ->layout('layouts.app', ['title' => 'Audit Trail']);
    }
}
