<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Project;
use App\Models\SensorReading;
use App\Models\SensorSource;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SensorTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $user;

    private SensorSource $sensor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['name' => 'Test', 'slug' => 'test']);
        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $project = Project::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Project',
        ]);

        $asset = Asset::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $project->id,
            'name' => 'Test Chiller',
        ]);

        $this->sensor = SensorSource::create([
            'tenant_id' => $this->tenant->id,
            'asset_id' => $asset->id,
            'name' => 'Supply Water Temp',
            'sensor_type' => 'temperature',
            'unit' => '°F',
            'threshold_min' => 38,
            'threshold_max' => 48,
            'alert_enabled' => true,
            'is_active' => true,
        ]);
    }

    public function test_sensor_value_in_range(): void
    {
        $this->assertFalse($this->sensor->isValueOutOfRange(42.0));
    }

    public function test_sensor_value_above_max(): void
    {
        $this->assertTrue($this->sensor->isValueOutOfRange(52.0));
        $this->assertEquals('above_maximum', $this->sensor->getAnomalyType(52.0));
    }

    public function test_sensor_value_below_min(): void
    {
        $this->assertTrue($this->sensor->isValueOutOfRange(35.0));
        $this->assertEquals('below_minimum', $this->sensor->getAnomalyType(35.0));
    }

    public function test_api_sensor_list(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/sensors')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_api_sensor_ingest(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/sensors/ingest', [
                'readings' => [
                    [
                        'sensor_source_id' => $this->sensor->id,
                        'value' => 43.5,
                        'recorded_at' => now()->toIso8601String(),
                    ],
                ],
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('sensor_readings', [
            'sensor_source_id' => $this->sensor->id,
            'value' => 43.5,
        ]);
    }

    public function test_api_sensor_readings(): void
    {
        SensorReading::create([
            'sensor_source_id' => $this->sensor->id,
            'value' => 42.0,
            'recorded_at' => now(),
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/sensors/{$this->sensor->id}/readings")
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }
}
