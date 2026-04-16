<?php

namespace App\Notifications;

use App\Models\SensorSource;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SensorAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly SensorSource $sensor,
        private readonly float $value,
        private readonly string $assetName,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $threshold = $this->sensor->threshold_max !== null && $this->value > (float) $this->sensor->threshold_max
            ? $this->sensor->threshold_max
            : $this->sensor->threshold_min;

        return [
            'type' => 'sensor_alert',
            'sensor_name' => $this->sensor->name,
            'value' => $this->value,
            'threshold' => $threshold,
            'asset_name' => $this->assetName,
        ];
    }
}
