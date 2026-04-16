<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceSchedule extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'asset_id', 'name', 'description',
        'frequency', 'trigger_type', 'runtime_hours_interval',
        'next_due_date', 'last_completed_date',
        'estimated_duration_minutes', 'checklist', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'next_due_date' => 'date',
            'last_completed_date' => 'date',
            'checklist' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function isDue(): bool
    {
        if (! $this->is_active || ! $this->next_due_date) {
            return false;
        }

        return $this->next_due_date->isPast() || $this->next_due_date->isToday();
    }

    public function calculateNextDueDate(): void
    {
        $intervals = [
            'daily' => '1 day',
            'weekly' => '1 week',
            'biweekly' => '2 weeks',
            'monthly' => '1 month',
            'quarterly' => '3 months',
            'semi_annual' => '6 months',
            'annual' => '1 year',
        ];

        $base = $this->last_completed_date ?? now();
        $interval = $intervals[$this->frequency] ?? '1 month';

        $this->update([
            'next_due_date' => $base->copy()->add($interval),
            'last_completed_date' => now(),
        ]);
    }
}
