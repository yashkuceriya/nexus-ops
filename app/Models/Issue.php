<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Issue extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'project_id', 'asset_id', 'assigned_to',
        'external_issue_id', 'title', 'description',
        'status', 'priority', 'issue_type',
        'source_system', 'source_id', 'attachments',
        'due_date', 'resolved_at', 'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'due_date' => 'datetime',
            'resolved_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function workOrder(): HasOne
    {
        return $this->hasOne(WorkOrder::class);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'in_progress']);
    }

    public function isResolved(): bool
    {
        return in_array($this->status, ['work_completed', 'closed']);
    }
}
