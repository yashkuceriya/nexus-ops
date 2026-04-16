<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChecklistTemplate extends Model
{
    use BelongsToTenant;

    public const TYPE_FACILITY_OPS = 'facility_ops';

    public const TYPE_PFC = 'pfc';

    protected $fillable = [
        'tenant_id', 'name', 'description', 'type', 'category',
        'cx_level', 'system_types', 'steps', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'steps' => 'array',
            'system_types' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function completions(): HasMany
    {
        return $this->hasMany(ChecklistCompletion::class);
    }

    public function scopePfc($query)
    {
        return $query->where('type', self::TYPE_PFC);
    }

    public function scopeFacilityOps($query)
    {
        return $query->where('type', self::TYPE_FACILITY_OPS);
    }
}
