<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A commissioning Functional Performance Test template.
 *
 * Scripts are either:
 *   - system templates (is_system=true, tenant_id=null) seeded by NexusOps
 *     and visible to every tenant; or
 *   - tenant templates authored in the tenant itself.
 *
 * Note: because system templates have a NULL tenant_id, we override the
 * `BelongsToTenant` global scope for queries that need to include system
 * templates.
 */
class TestScript extends Model
{
    use BelongsToTenant;
    use HasFactory;
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'tenant_id', 'created_by', 'name', 'slug', 'description',
        'system_type', 'asset_category', 'cx_level', 'version', 'status',
        'is_system', 'cloned_from_id', 'estimated_duration_minutes',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'version' => 'integer',
        ];
    }

    public function steps(): HasMany
    {
        return $this->hasMany(TestStep::class)->orderBy('sequence');
    }

    public function executions(): HasMany
    {
        return $this->hasMany(TestExecution::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function clonedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'cloned_from_id');
    }

    /** Scope that returns both tenant-owned templates *and* system templates. */
    public function scopeAvailableTo(Builder $query, int $tenantId): Builder
    {
        return $query->withoutGlobalScope('tenant')
            ->where(function (Builder $q) use ($tenantId): void {
                $q->where('tenant_id', $tenantId)
                    ->orWhere('is_system', true);
            });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }
}
