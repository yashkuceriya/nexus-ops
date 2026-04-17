<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A searchable knowledge-base record that captures WHY a problem happened
 * and HOW similar issues should be prevented on future projects.
 *
 * Commissioning workflows emphasise continuous improvement — this is the
 * canonical place we capture those improvements so they don't get lost in
 * closed issue threads.
 */
class LessonLearned extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $table = 'lessons_learned';

    public const CATEGORY_DESIGN = 'design';

    public const CATEGORY_CONSTRUCTION = 'construction';

    public const CATEGORY_COMMISSIONING = 'commissioning';

    public const CATEGORY_OPERATIONS = 'operations';

    public const CATEGORY_VENDOR = 'vendor';

    public const CATEGORY_SAFETY = 'safety';

    public const CATEGORY_PROCESS = 'process';

    protected $fillable = [
        'tenant_id', 'project_id', 'issue_id', 'work_order_id', 'asset_id',
        'created_by', 'title', 'category', 'severity',
        'problem_summary', 'root_cause', 'corrective_action',
        'preventive_action', 'recommendation',
        'tags', 'occurred_at', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'occurred_at' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return array<string, string> */
    public static function categories(): array
    {
        return [
            self::CATEGORY_DESIGN => 'Design',
            self::CATEGORY_CONSTRUCTION => 'Construction',
            self::CATEGORY_COMMISSIONING => 'Commissioning',
            self::CATEGORY_OPERATIONS => 'Operations',
            self::CATEGORY_VENDOR => 'Vendor / Contractor',
            self::CATEGORY_SAFETY => 'Safety',
            self::CATEGORY_PROCESS => 'Process',
        ];
    }

    /** @return array<string, string> */
    public static function severities(): array
    {
        return [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'critical' => 'Critical',
        ];
    }
}
