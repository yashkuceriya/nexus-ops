<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Project;
use App\Models\TestExecution;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TestExecutionList extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $statusFilter = '';

    #[Url(history: true)]
    public ?int $projectFilter = null;

    #[Url(history: true)]
    public string $cxLevelFilter = '';

    #[Url(history: true)]
    public bool $witnessedOnly = false;

    public function updating(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->statusFilter = '';
        $this->projectFilter = null;
        $this->cxLevelFilter = '';
        $this->witnessedOnly = false;
        $this->resetPage();
    }

    public function getExecutionsProperty()
    {
        return $this->baseQuery()
            ->with([
                'asset:id,name,asset_tag',
                'project:id,name',
                'starter:id,name',
                'witness:id,name',
            ])
            ->latest('started_at')
            ->paginate(20);
    }

    public function getProjectsProperty()
    {
        return Project::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Tenant-scoped stats that honour every active filter — this is what
     * commissioning managers glance at first when triaging failures across
     * a project portfolio.
     *
     * @return array<string, int|float>
     */
    #[Computed]
    public function stats(): array
    {
        $base = $this->baseQuery();

        $total = (clone $base)->count();
        $passed = (clone $base)->where('status', TestExecution::STATUS_PASSED)->count();
        $failed = (clone $base)->where('status', TestExecution::STATUS_FAILED)->count();
        $running = (clone $base)->where('status', TestExecution::STATUS_IN_PROGRESS)->count();
        $witnessed = (clone $base)->whereNotNull('witness_signed_at')->count();

        $complete = $passed + $failed;
        $passRate = $complete > 0 ? round(($passed / $complete) * 100, 1) : 0.0;

        return [
            'total' => $total,
            'passed' => $passed,
            'failed' => $failed,
            'running' => $running,
            'witnessed' => $witnessed,
            'pass_rate' => $passRate,
        ];
    }

    private function baseQuery()
    {
        return TestExecution::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->projectFilter, fn ($q) => $q->where('project_id', $this->projectFilter))
            ->when($this->cxLevelFilter !== '', fn ($q) => $q->where('cx_level', $this->cxLevelFilter))
            ->when($this->witnessedOnly, fn ($q) => $q->whereNotNull('witness_signed_at'));
    }

    public function render()
    {
        return view('livewire.test-execution-list');
    }
}
