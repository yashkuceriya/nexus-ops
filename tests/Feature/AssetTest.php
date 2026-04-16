<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Project;
use App\Models\SensorSource;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $user;

    private Project $project;

    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Asset Test Corp',
            'slug' => 'asset-test-corp',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Asset Tester',
            'email' => 'asset@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->project = Project::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Asset Project',
            'status' => 'commissioning',
        ]);

        $this->asset = Asset::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'name' => 'Rooftop AHU-1',
            'system_type' => 'HVAC',
            'qr_code' => 'QR-AHU-001',
            'condition' => 'good',
            'warranty_expiry' => now()->addYear(),
        ]);
    }

    public function test_assets_page_loads(): void
    {
        $this->actingAs($this->user)
            ->get('/assets')
            ->assertStatus(200);
    }

    public function test_asset_detail_page_loads(): void
    {
        $this->actingAs($this->user)
            ->get("/assets/{$this->asset->id}")
            ->assertStatus(200);
    }

    public function test_qr_code_lookup_via_api_works(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/assets/qr/QR-AHU-001')
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Rooftop AHU-1');
    }

    public function test_qr_code_lookup_returns_404_for_unknown_code(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/assets/qr/NONEXISTENT')
            ->assertStatus(404);
    }

    public function test_asset_warranty_status_is_correctly_computed(): void
    {
        // Warranty is in the future
        $this->assertTrue($this->asset->isWarrantyActive());

        // Expired warranty
        $expiredAsset = Asset::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'name' => 'Old Boiler',
            'system_type' => 'Plumbing',
            'warranty_expiry' => now()->subMonth(),
        ]);

        $this->assertFalse($expiredAsset->isWarrantyActive());
    }

    public function test_asset_sensor_sources_are_loaded_on_api_detail(): void
    {
        SensorSource::create([
            'tenant_id' => $this->tenant->id,
            'asset_id' => $this->asset->id,
            'name' => 'Discharge Air Temp',
            'sensor_type' => 'temperature',
            'unit' => '°F',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/assets/{$this->asset->id}");

        $response->assertStatus(200)
            ->assertJsonPath('meta.warranty_active', true);

        $sensorSources = $response->json('data.sensor_sources');
        $this->assertNotEmpty($sensorSources);
    }
}
