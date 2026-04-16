<?php

namespace App\Livewire;

use App\Models\AutomationRule;
use Livewire\Component;
use Livewire\WithPagination;

class AutomationRuleList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $triggerFilter = '';

    public string $statusFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTriggerFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function toggleActive(int $ruleId): void
    {
        $rule = AutomationRule::findOrFail($ruleId);
        $rule->update(['is_active' => ! $rule->is_active]);
    }

    public function deleteRule(int $ruleId): void
    {
        AutomationRule::findOrFail($ruleId)->delete();
    }

    public function render()
    {
        $rules = AutomationRule::when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->triggerFilter, fn ($q) => $q->where('trigger_type', $this->triggerFilter))
            ->when($this->statusFilter !== '', function ($q) {
                if ($this->statusFilter === 'active') {
                    $q->where('is_active', true);
                } elseif ($this->statusFilter === 'inactive') {
                    $q->where('is_active', false);
                }
            })
            ->orderByDesc('updated_at')
            ->paginate(15);

        return view('livewire.automation-rule-list', ['rules' => $rules])
            ->layout('layouts.app', ['title' => 'Automation Rules']);
    }
}
