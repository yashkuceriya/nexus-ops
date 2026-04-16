<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestStepResult extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PASS = 'pass';

    public const STATUS_FAIL = 'fail';

    public const STATUS_SKIPPED = 'skipped';

    public const STATUS_NA = 'na';

    protected $fillable = [
        'test_execution_id', 'test_step_id',
        'step_sequence', 'step_title', 'step_instruction',
        'measurement_type', 'expected_value', 'expected_numeric',
        'tolerance', 'measurement_unit',
        'auto_evaluated', 'evaluation_mode', 'acceptable_min', 'acceptable_max',
        'status', 'measured_value', 'measured_numeric',
        'notes', 'photo_path', 'issue_id',
        'recorded_by', 'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'expected_numeric' => 'decimal:4',
            'tolerance' => 'decimal:4',
            'acceptable_min' => 'decimal:4',
            'acceptable_max' => 'decimal:4',
            'measured_numeric' => 'decimal:4',
            'auto_evaluated' => 'boolean',
            'recorded_at' => 'datetime',
            'step_sequence' => 'integer',
        ];
    }

    public function execution(): BelongsTo
    {
        return $this->belongsTo(TestExecution::class, 'test_execution_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(TestStep::class, 'test_step_id');
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function isPassed(): bool
    {
        return $this->status === self::STATUS_PASS;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAIL;
    }
}
