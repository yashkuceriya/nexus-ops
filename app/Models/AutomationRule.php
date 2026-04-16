<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class AutomationRule extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'description', 'is_active',
        'trigger_type', 'conditions', 'actions',
        'execution_count', 'last_executed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'conditions' => 'array',
            'actions' => 'array',
            'execution_count' => 'integer',
            'last_executed_at' => 'datetime',
        ];
    }
}
