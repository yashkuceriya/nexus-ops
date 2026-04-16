<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\CloseoutRequirement;
use App\Models\Project;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * A single-project closeout readiness dashboard.
 *
 * Gives PMs a live, filterable view of every closeout artifact the project
 * must deliver before handover — categorised, scored, and one-click actionable.
 */
class CloseoutTracker extends Component
{
    public Project $project;

    #[Url(history: true)]
    public string $categoryFilter = '';

    #[Url(history: true)]
    public string $statusFilter = '';

    #[Url(history: true)]
    public bool $onlyOverdue = false;

    public function mount(int $projectId): void
    {
        $this->project = Project::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($projectId);
    }

    public function getRequirementsProperty()
    {
        return CloseoutRequirement::query()
            ->where('tenant_id', $this->project->tenant_id)
            ->where('project_id', $this->project->id)
            ->when($this->categoryFilter !== '', fn ($q) => $q->where('category', $this->categoryFilter))
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->onlyOverdue, fn ($q) => $q
                ->where('status', '!=', 'approved')
                ->whereNotNull('due_date')
                ->where('due_date', '<', now()))
            ->with(['asset:id,name,asset_tag', 'document:id,file_name'])
            ->orderByRaw("CASE status WHEN 'rejected' THEN 1 WHEN 'required' THEN 2 WHEN 'submitted' THEN 3 WHEN 'approved' THEN 4 ELSE 5 END")
            ->orderBy('due_date')
            ->get();
    }

    public function getStatsProperty(): array
    {
        $requirements = CloseoutRequirement::where('tenant_id', $this->project->tenant_id)
            ->where('project_id', $this->project->id)
            ->get(['status', 'due_date']);

        $total = $requirements->count();
        $approved = $requirements->where('status', 'approved')->count();
        $submitted = $requirements->where('status', 'submitted')->count();
        $rejected = $requirements->where('status', 'rejected')->count();
        $required = $requirements->where('status', 'required')->count();
        $overdue = $requirements
            ->filter(fn ($r) => $r->status !== 'approved' && $r->due_date && $r->due_date->isPast())
            ->count();

        $progress = $total > 0 ? (int) round(($approved / $total) * 100) : 0;

        return [
            'total' => $total,
            'approved' => $approved,
            'submitted' => $submitted,
            'rejected' => $rejected,
            'required' => $required,
            'overdue' => $overdue,
            'progress' => $progress,
        ];
    }

    public function getCategoriesProperty(): array
    {
        return [
            'om_manual' => 'O&M Manuals',
            'warranty' => 'Warranties',
            'as_built' => 'As-Built Drawings',
            'test_report' => 'Test Reports',
            'training_record' => 'Training Records',
            'spare_parts_list' => 'Spare Parts Lists',
            'certification' => 'Certifications',
            'other' => 'Other',
        ];
    }

    public function markSubmitted(int $id): void
    {
        $this->transitionStatus($id, 'submitted', 'closeout_requirement_submitted');
    }

    public function approve(int $id): void
    {
        $this->transitionStatus($id, 'approved', 'closeout_requirement_approved');
    }

    public function reject(int $id): void
    {
        $this->transitionStatus($id, 'rejected', 'closeout_requirement_rejected');
    }

    private function transitionStatus(int $id, string $newStatus, string $auditAction): void
    {
        $req = CloseoutRequirement::where('tenant_id', $this->project->tenant_id)
            ->where('project_id', $this->project->id)
            ->findOrFail($id);

        $old = $req->status;
        $req->update(['status' => $newStatus]);

        AuditLog::record(
            action: $auditAction,
            model: $req->refresh(),
            oldValues: ['status' => $old],
            newValues: ['status' => $newStatus],
        );

        $this->dispatch('toast', type: 'success', message: "Requirement marked {$newStatus}.");
    }

    public function clearFilters(): void
    {
        $this->reset(['categoryFilter', 'statusFilter', 'onlyOverdue']);
    }

    public function render()
    {
        return view('livewire.closeout-tracker');
    }
}
