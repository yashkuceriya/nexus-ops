<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SensorSource extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id', 'asset_id', 'location_id', 'external_id',
        'name', 'sensor_type', 'unit',
        'threshold_min', 'threshold_max',
        'last_value', 'last_reading_at',
        'alert_enabled', 'is_active', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'threshold_min' => 'decimal:2',
            'threshold_max' => 'decimal:2',
            'last_value' => 'decimal:2',
            'last_reading_at' => 'datetime',
            'alert_enabled' => 'boolean',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function readings(): HasMany
    {
        return $this->hasMany(SensorReading::class);
    }

    public function isValueOutOfRange(float $value): bool
    {
        if ($this->threshold_min !== null && $value < (float) $this->threshold_min) {
            return true;
        }

        if ($this->threshold_max !== null && $value > (float) $this->threshold_max) {
            return true;
        }

        return false;
    }

    public function getAnomalyType(float $value): ?string
    {
        if ($this->threshold_min !== null && $value < (float) $this->threshold_min) {
            return 'below_minimum';
        }

        if ($this->threshold_max !== null && $value > (float) $this->threshold_max) {
            return 'above_maximum';
        }

        return null;
    }
}
