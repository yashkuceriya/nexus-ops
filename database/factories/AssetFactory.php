<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\Project;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'project_id' => Project::factory(),
            'name' => fake()->words(2, true) . ' ' . fake()->randomElement(['Chiller', 'AHU', 'Pump', 'VFD', 'Boiler']),
            'system_type' => fake()->randomElement(['HVAC', 'Plumbing', 'Electrical', 'Fire Protection']),
            'condition' => 'good',
            'commissioning_status' => 'not_started',
        ];
    }

    public function critical(): static
    {
        return $this->state([
            'condition' => 'critical',
        ]);
    }

    public function withSensors(): static
    {
        return $this->afterCreating(function (Asset $asset) {
            \App\Models\SensorSource::factory()
                ->count(2)
                ->create([
                    'tenant_id' => $asset->tenant_id,
                    'asset_id' => $asset->id,
                ]);
        });
    }

    public function withMaintenanceSchedule(): static
    {
        return $this->afterCreating(function (Asset $asset) {
            \App\Models\MaintenanceSchedule::create([
                'tenant_id' => $asset->tenant_id,
                'asset_id' => $asset->id,
                'name' => 'Quarterly PM - ' . $asset->name,
                'frequency' => 'quarterly',
                'trigger_type' => 'calendar',
                'next_due_date' => now()->addMonth(),
                'is_active' => true,
            ]);
        });
    }
}
