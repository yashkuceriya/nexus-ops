<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestStep extends Model
{
    use HasFactory;

    public const TYPE_NUMERIC = 'numeric';

    public const TYPE_BOOLEAN = 'boolean';

    public const TYPE_SELECTION = 'selection';

    public const TYPE_TEXT = 'text';

    public const TYPE_NONE = 'none';

    public const EVAL_WITHIN_TOLERANCE = 'within_tolerance';

    public const EVAL_GTE = 'greater_than_or_equal';

    public const EVAL_LTE = 'less_than_or_equal';

    public const EVAL_BETWEEN = 'between';

    public const EVAL_EXACT = 'exact';

    protected $fillable = [
        'test_script_id', 'sequence', 'title', 'instruction',
        'expected_behavior', 'measurement_type', 'expected_value',
        'expected_numeric', 'tolerance', 'measurement_unit',
        'selection_options', 'requires_photo', 'requires_witness',
        'is_critical', 'sensor_metric_key',
        'auto_evaluate', 'evaluation_mode', 'acceptable_min', 'acceptable_max',
    ];

    protected function casts(): array
    {
        return [
            'selection_options' => 'array',
            'requires_photo' => 'boolean',
            'requires_witness' => 'boolean',
            'is_critical' => 'boolean',
            'auto_evaluate' => 'boolean',
            'expected_numeric' => 'decimal:4',
            'tolerance' => 'decimal:4',
            'acceptable_min' => 'decimal:4',
            'acceptable_max' => 'decimal:4',
            'sequence' => 'integer',
        ];
    }

    public function script(): BelongsTo
    {
        return $this->belongsTo(TestScript::class, 'test_script_id');
    }

    /**
     * Evaluate a numeric measurement against this step's configured rule.
     * Returns `TestStepResult::STATUS_PASS` or `STATUS_FAIL`.
     *
     * This is the authoritative pass/fail decision for numeric steps —
     * the runner and the service both call through here so behaviour is
     * identical at data-entry time and at record-replay time.
     */
    public function evaluateNumeric(float $value): string
    {
        $mode = $this->evaluation_mode ?: self::EVAL_WITHIN_TOLERANCE;
        $expected = $this->expected_numeric !== null ? (float) $this->expected_numeric : null;
        $tolerance = $this->tolerance !== null ? (float) $this->tolerance : 0.0;
        $min = $this->acceptable_min !== null ? (float) $this->acceptable_min : null;
        $max = $this->acceptable_max !== null ? (float) $this->acceptable_max : null;

        $pass = match ($mode) {
            self::EVAL_GTE => $expected !== null ? $value >= $expected : ($min !== null ? $value >= $min : true),
            self::EVAL_LTE => $expected !== null ? $value <= $expected : ($max !== null ? $value <= $max : true),
            self::EVAL_BETWEEN => ($min === null || $value >= $min) && ($max === null || $value <= $max),
            self::EVAL_EXACT => $expected !== null && abs($value - $expected) < 0.0001,
            default => $expected === null
                ? true
                : abs($value - $expected) <= $tolerance,
        };

        return $pass ? TestStepResult::STATUS_PASS : TestStepResult::STATUS_FAIL;
    }

    /** Legacy convenience — retained for backwards compatibility. */
    public function matchesNumeric(float $value): bool
    {
        return $this->evaluateNumeric($value) === TestStepResult::STATUS_PASS;
    }
}
