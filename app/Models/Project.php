<?php

namespace App\Models;

use App\Domain\ReadinessScore;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'facilitygrid_project_id', 'name', 'description', 'status',
        'project_type', 'address', 'city', 'state', 'zip',
        'readiness_score', 'total_issues', 'open_issues',
        'total_tests', 'completed_tests', 'total_closeout_docs', 'completed_closeout_docs',
        'target_handover_date', 'actual_handover_date', 'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'readiness_score' => 'decimal:2',
            'target_handover_date' => 'date',
            'actual_handover_date' => 'date',
            'last_synced_at' => 'datetime',
        ];
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function closeoutRequirements(): HasMany
    {
        return $this->hasMany(CloseoutRequirement::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function calculateReadinessScore(): float
    {
        return ReadinessScore::fromProject($this)->calculate();
    }

    public function readinessScore(): ReadinessScore
    {
        return ReadinessScore::fromProject($this);
    }

    public function getHandoverBlockers(): array
    {
        $blockers = [];

        if ($this->open_issues > 0) {
            $blockers[] = ['type' => 'issues', 'count' => $this->open_issues, 'label' => "{$this->open_issues} open issues"];
        }

        $incompleteTests = $this->total_tests - $this->completed_tests;
        if ($incompleteTests > 0) {
            $blockers[] = ['type' => 'tests', 'count' => $incompleteTests, 'label' => "{$incompleteTests} incomplete tests"];
        }

        $missingDocs = $this->total_closeout_docs - $this->completed_closeout_docs;
        if ($missingDocs > 0) {
            $blockers[] = ['type' => 'docs', 'count' => $missingDocs, 'label' => "{$missingDocs} missing closeout documents"];
        }

        $failedFpts = $this->testExecutions()
            ->where('status', TestExecution::STATUS_FAILED)
            ->whereDoesntHave('retests', fn ($q) => $q->where('status', TestExecution::STATUS_PASSED))
            ->count();

        if ($failedFpts > 0) {
            $blockers[] = ['type' => 'fpts', 'count' => $failedFpts, 'label' => "{$failedFpts} failed FPTs without a passing retest"];
        }

        $openPfcs = ChecklistCompletion::query()
            ->where('tenant_id', $this->tenant_id)
            ->where('project_id', $this->id)
            ->where('type', ChecklistTemplate::TYPE_PFC)
            ->whereIn('status', [ChecklistCompletion::STATUS_IN_PROGRESS, ChecklistCompletion::STATUS_FAILED])
            ->count();

        if ($openPfcs > 0) {
            $blockers[] = ['type' => 'pfcs', 'count' => $openPfcs, 'label' => "{$openPfcs} open/failed PFCs"];
        }

        return $blockers;
    }

    public function testExecutions(): HasMany
    {
        return $this->hasMany(TestExecution::class);
    }

    public function checklistCompletions(): HasMany
    {
        return $this->hasMany(ChecklistCompletion::class);
    }
}
