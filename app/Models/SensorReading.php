<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorReading extends Model
{
    protected $fillable = [
        'sensor_source_id', 'value', 'is_anomaly', 'anomaly_type', 'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'is_anomaly' => 'boolean',
            'recorded_at' => 'datetime',
        ];
    }

    public function sensorSource(): BelongsTo
    {
        return $this->belongsTo(SensorSource::class);
    }
}
