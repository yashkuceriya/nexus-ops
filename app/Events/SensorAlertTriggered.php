<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SensorAlertTriggered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $tenantId,
        public readonly int $sensorSourceId,
        public readonly string $sensorName,
        public readonly float $value,
        public readonly string $thresholdExceeded,
        public readonly ?string $assetName = null,
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenant.{$this->tenantId}"),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'sensor_source_id' => $this->sensorSourceId,
            'sensor_name' => $this->sensorName,
            'value' => $this->value,
            'threshold_exceeded' => $this->thresholdExceeded,
            'asset_name' => $this->assetName,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
