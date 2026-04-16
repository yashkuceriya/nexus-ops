<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusMapping extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'source_system', 'source_entity', 'source_status',
        'target_entity', 'target_status', 'auto_transition', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'auto_transition' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public static function resolve(
        int $tenantId,
        string $sourceSystem,
        string $sourceEntity,
        string $sourceStatus,
        string $targetEntity
    ): ?string {
        return static::where('tenant_id', $tenantId)
            ->where('source_system', $sourceSystem)
            ->where('source_entity', $sourceEntity)
            ->where('source_status', $sourceStatus)
            ->where('target_entity', $targetEntity)
            ->where('is_active', true)
            ->value('target_status');
    }
}
