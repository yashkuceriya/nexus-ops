<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A single run of a Functional Performance Test against one asset.
 *
 * Executions are immutable *records* — once a step result is captured it
 * cannot be rewritten. To correct a failed FPT a new execution is created
 * with `parent_execution_id` pointing at the failed run, preserving the
 * complete deficiency → retest audit trail that commissioning authorities
 * and owners rely on.
 */
class TestExecution extends Model
{
    use BelongsToTenant;
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_PASSED = 'passed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_ABORTED = 'aborted';

    public const STATUS_ON_HOLD = 'on_hold';

    protected $fillable = [
        'tenant_id', 'test_script_id', 'test_script_version', 'test_script_name',
        'project_id', 'asset_id', 'status', 'cx_level',
        'started_by', 'started_at', 'completed_at',
        'parent_execution_id', 'cx_agent_id', 'witness_id',
        'witness_signature_hash', 'witness_signature_image',
        'witness_signature_ip', 'witness_signature_user_agent',
        'witness_signed_at',
        'overall_notes',
        'pass_count', 'fail_count', 'pending_count', 'total_count',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'witness_signed_at' => 'datetime',
            'pass_count' => 'integer',
            'fail_count' => 'integer',
            'pending_count' => 'integer',
            'total_count' => 'integer',
        ];
    }

    public function script(): BelongsTo
    {
        return $this->belongsTo(TestScript::class, 'test_script_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function starter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function cxAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cx_agent_id');
    }

    public function witness(): BelongsTo
    {
        return $this->belongsTo(User::class, 'witness_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(TestExecution::class, 'parent_execution_id');
    }

    public function retests(): HasMany
    {
        return $this->hasMany(TestExecution::class, 'parent_execution_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(TestStepResult::class)->orderBy('step_sequence');
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isClosed(): bool
    {
        return in_array($this->status, [
            self::STATUS_PASSED, self::STATUS_FAILED, self::STATUS_ABORTED,
        ], true);
    }

    public function progressPercent(): int
    {
        if ($this->total_count === 0) {
            return 0;
        }

        return (int) round((($this->pass_count + $this->fail_count) / $this->total_count) * 100);
    }
}
