<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistCompletion extends Model
{
    use BelongsToTenant;

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'tenant_id', 'project_id', 'asset_id', 'work_order_id',
        'checklist_template_id', 'completed_by', 'type',
        'responses', 'notes', 'status', 'completed_at',
        'pass_count', 'fail_count', 'na_count',
    ];

    protected function casts(): array
    {
        return [
            'responses' => 'array',
            'completed_at' => 'datetime',
            'pass_count' => 'integer',
            'fail_count' => 'integer',
            'na_count' => 'integer',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplate::class, 'checklist_template_id');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Whether this completion represents a fully passing record — used by
     * the readiness score and the turnover package to distinguish a
     * rubber-stamp PFC from one that landed with deficiencies.
     */
    public function isCleanPfc(): bool
    {
        return $this->status === self::STATUS_COMPLETED && $this->fail_count === 0;
    }
}
