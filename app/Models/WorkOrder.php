<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;
    protected $fillable = [
        'tenant_id', 'project_id', 'asset_id', 'location_id', 'issue_id',
        'assigned_to', 'created_by', 'vendor_id', 'wo_number', 'title', 'description',
        'status', 'priority', 'type', 'source',
        'sla_hours', 'sla_deadline', 'sla_breached',
        'started_at', 'completed_at', 'verified_at',
        'estimated_cost', 'actual_cost', 'resolution_notes', 'photos',
    ];

    protected function casts(): array
    {
        return [
            'sla_deadline' => 'datetime',
            'sla_breached' => 'boolean',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'verified_at' => 'datetime',
            'estimated_cost' => 'decimal:2',
            'actual_cost' => 'decimal:2',
            'photos' => 'array',
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

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public static function generateWoNumber(): string
    {
        $prefix = 'WO-' . now()->format('Ym');
        $latest = static::where('wo_number', 'like', $prefix . '%')
            ->orderByDesc('wo_number')
            ->value('wo_number');

        $sequence = $latest ? (int) substr($latest, -4) + 1 : 1;

        return $prefix . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function isSlaBreached(): bool
    {
        if (! $this->sla_deadline) {
            return false;
        }

        if ($this->completed_at) {
            return $this->completed_at->isAfter($this->sla_deadline);
        }

        return now()->isAfter($this->sla_deadline);
    }

    public function getTimeToRepairMinutes(): ?int
    {
        if (! $this->started_at || ! $this->completed_at) {
            return null;
        }

        return $this->started_at->diffInMinutes($this->completed_at);
    }
}
