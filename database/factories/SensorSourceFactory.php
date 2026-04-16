<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\SensorSource;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SensorSource>
 */
class SensorSourceFactory extends Factory
{
    protected $model = SensorSource::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'asset_id' => Asset::factory(),
            'name' => fake()->randomElement(['Supply Water Temp', 'Return Air Temp', 'Discharge Pressure', 'Vibration Level']),
            'sensor_type' => 'temperature',
            'unit' => '°F',
            'threshold_min' => 38.0,
            'threshold_max' => 48.0,
            'alert_enabled' => true,
            'is_active' => true,
        ];
    }

    public function inAlert(): static
    {
        return $this->state([
            'last_value' => 55.0,
            'last_reading_at' => now(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state([
            'is_active' => false,
        ]);
    }
}
