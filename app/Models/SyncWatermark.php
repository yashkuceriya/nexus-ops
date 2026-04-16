<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncWatermark extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'connector', 'entity', 'cursor',
        'last_successful_sync_at', 'last_attempted_at', 'last_error',
        'consecutive_failures',
    ];

    protected function casts(): array
    {
        return [
            'last_successful_sync_at' => 'datetime',
            'last_attempted_at' => 'datetime',
        ];
    }

    public function recordSuccess(?string $cursor = null): void
    {
        $this->update([
            'cursor' => $cursor ?? $this->cursor,
            'last_successful_sync_at' => now(),
            'last_attempted_at' => now(),
            'last_error' => null,
            'consecutive_failures' => 0,
        ]);
    }

    public function recordFailure(string $error): void
    {
        $this->update([
            'last_attempted_at' => now(),
            'last_error' => $error,
            'consecutive_failures' => $this->consecutive_failures + 1,
        ]);
    }

    public function isCircuitBroken(int $threshold = 5): bool
    {
        return $this->consecutive_failures >= $threshold;
    }
}
