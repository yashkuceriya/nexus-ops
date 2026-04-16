<?php

declare(strict_types=1);

namespace App\Services\Sensor;

use App\Events\SensorAlertTriggered;
use App\Models\SensorReading;
use App\Models\SensorSource;
use App\Models\WorkOrder;
use App\Services\WorkOrder\WorkOrderService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class SensorIngestService
{
    /** @var int Debounce window in minutes to prevent duplicate alerts for the same sensor */
    private const int DEBOUNCE_MINUTES = 60;

    public function __construct(
        private readonly WorkOrderService $workOrderService,
    ) {}

    // ────────────────────────────────────────────────────────────────
    // Single reading ingestion
    // ────────────────────────────────────────────────────────────────

    /**
     * Ingest a single sensor reading, check thresholds, and create alerts when needed.
     */
    public function ingest(int $sensorSourceId, float $value, ?Carbon $recordedAt = null): SensorReading
    {
        $sensor = SensorSource::findOrFail($sensorSourceId);
        $recordedAt ??= Carbon::now();

        $isAnomaly = $sensor->isValueOutOfRange($value);
        $anomalyType = $sensor->getAnomalyType($value);

        return DB::transaction(function () use ($sensor, $value, $recordedAt, $isAnomaly, $anomalyType): SensorReading {
            $reading = SensorReading::create([
                'sensor_source_id' => $sensor->id,
                'value' => $value,
                'is_anomaly' => $isAnomaly,
                'anomaly_type' => $anomalyType,
                'recorded_at' => $recordedAt,
            ]);

            $sensor->update([
                'last_value' => $value,
                'last_reading_at' => $recordedAt,
            ]);

            if ($isAnomaly && $sensor->alert_enabled && ! $this->isDebouncePeriodActive($sensor)) {
                $this->workOrderService->createFromSensorAlert($sensor, $value);

                SensorAlertTriggered::dispatch(
                    tenantId: $sensor->tenant_id,
                    sensorSourceId: $sensor->id,
                    sensorName: $sensor->name,
                    value: $value,
                    thresholdExceeded: $anomalyType ?? 'threshold_breach',
                    assetName: $sensor->asset?->name,
                );
            }

            return $reading;
        });
    }

    // ────────────────────────────────────────────────────────────────
    // Batch ingestion (preserved from original implementation)
    // ────────────────────────────────────────────────────────────────

    /**
     * Ingest a batch of sensor readings.
     *
     * @param  array<int, array{sensor_source_id: int, value: float, recorded_at?: string}>  $readings
     * @return array{ingested: int, anomalies: int, errors: array<int, string>}
     */
    public function ingestBatch(int $tenantId, array $readings): array
    {
        $ingested = 0;
        $anomalies = 0;
        $errors = [];

        $sensorIds = array_unique(array_column($readings, 'sensor_source_id'));
        $sensors = SensorSource::where('tenant_id', $tenantId)
            ->whereIn('id', $sensorIds)
            ->get()
            ->keyBy('id');

        DB::beginTransaction();

        try {
            foreach ($readings as $index => $reading) {
                $sensorId = (int) $reading['sensor_source_id'];
                $sensor = $sensors->get($sensorId);

                if (! $sensor) {
                    $errors[$index] = "Sensor source {$sensorId} not found for tenant.";

                    continue;
                }

                if (! $sensor->is_active) {
                    $errors[$index] = "Sensor source {$sensorId} is inactive.";

                    continue;
                }

                $value = (float) $reading['value'];
                $recordedAt = isset($reading['recorded_at'])
                    ? Carbon::parse($reading['recorded_at'])
                    : Carbon::now();

                $isAnomaly = $sensor->isValueOutOfRange($value);
                $anomalyType = $sensor->getAnomalyType($value);

                SensorReading::create([
                    'sensor_source_id' => $sensorId,
                    'value' => $value,
                    'is_anomaly' => $isAnomaly,
                    'anomaly_type' => $anomalyType,
                    'recorded_at' => $recordedAt,
                ]);

                $sensor->update([
                    'last_value' => $value,
                    'last_reading_at' => $recordedAt,
                ]);

                $ingested++;

                if ($isAnomaly) {
                    $anomalies++;

                    if ($sensor->alert_enabled && ! $this->isDebouncePeriodActive($sensor)) {
                        $this->workOrderService->createFromSensorAlert($sensor, $value);

                        SensorAlertTriggered::dispatch(
                            tenantId: $sensor->tenant_id,
                            sensorSourceId: $sensor->id,
                            sensorName: $sensor->name,
                            value: $value,
                            thresholdExceeded: $anomalyType ?? 'threshold_breach',
                            assetName: $sensor->asset?->name,
                        );
                    }
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }

        return [
            'ingested' => $ingested,
            'anomalies' => $anomalies,
            'errors' => $errors,
        ];
    }

    // ────────────────────────────────────────────────────────────────
    // Helpers
    // ────────────────────────────────────────────────────────────────

    /**
     * Check whether an alert work order was already created within the debounce window
     * for this sensor, preventing duplicate emergency work orders.
     */
    private function isDebouncePeriodActive(SensorSource $sensor): bool
    {
        $cutoff = Carbon::now()->subMinutes(self::DEBOUNCE_MINUTES);

        // WorkOrderService::createFromSensorAlert creates WOs with source='sensor'.
        // The previous value 'sensor_alert' meant debounce never matched, which
        // could flood the tenant with duplicate emergency work orders on
        // repeated threshold breaches.
        return WorkOrder::where('tenant_id', $sensor->tenant_id)
            ->where('source', 'sensor')
            ->where('asset_id', $sensor->asset_id)
            ->where('title', 'like', "Sensor Alert: {$sensor->name}%")
            ->where('created_at', '>=', $cutoff)
            ->exists();
    }
}
