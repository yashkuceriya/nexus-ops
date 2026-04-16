<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Issue;
use App\Models\Project;
use App\Models\StatusMapping;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\WorkOrder\WorkOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkOrderTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $admin;
    private User $tech;
    private Project $project;
    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['name' => 'Test', 'slug' => 'test']);

        $this->admin = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->tech = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Tech',
            'email' => 'tech@test.com',
            'password' => bcrypt('password'),
            'role' => 'technician',
        ]);

        $this->project = Project::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Project',
            'status' => 'commissioning',
        ]);

        $this->asset = Asset::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'name' => 'Test Chiller',
            'system_type' => 'HVAC',
        ]);

        StatusMapping::create([
            'tenant_id' => $this->tenant->id,
            'source_system' => 'facility_grid',
            'source_entity' => 'issue',
            'source_status' => 'Open',
            'target_entity' => 'work_order',
            'target_status' => 'pending',
            'auto_transition' => true,
        ]);
    }

    public function test_work_order_number_generation(): void
    {
        $woNumber = WorkOrder::generateWoNumber();
        $this->assertStringStartsWith('WO-', $woNumber);
        $this->assertMatchesRegularExpression('/^WO-\d{6}-\d{4}$/', $woNumber);
    }

    public function test_sla_breach_detection(): void
    {
        $wo = WorkOrder::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'wo_number' => WorkOrder::generateWoNumber(),
            'title' => 'Test WO',
            'status' => 'in_progress',
            'priority' => 'high',
            'type' => 'corrective',
            'source' => 'manual',
            'sla_deadline' => now()->subHour(),
        ]);

        $this->assertTrue($wo->isSlaBreached());
    }

    public function test_sla_not_breached_when_completed_on_time(): void
    {
        $wo = WorkOrder::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'wo_number' => WorkOrder::generateWoNumber(),
            'title' => 'Test WO',
            'status' => 'completed',
            'priority' => 'medium',
            'type' => 'corrective',
            'source' => 'manual',
            'sla_deadline' => now()->addHour(),
            'completed_at' => now()->subMinutes(30),
        ]);

        $this->assertFalse($wo->isSlaBreached());
    }

    public function test_project_readiness_score_calculation(): void
    {
        $this->project->update([
            'total_issues' => 10,
            'open_issues' => 2,
            'total_tests' => 20,
            'completed_tests' => 15,
            'total_closeout_docs' => 10,
            'completed_closeout_docs' => 8,
        ]);

        $score = $this->project->calculateReadinessScore();

        // Issues: (10-2)/10 * 100 = 80, weighted 0.4 = 32
        // Tests: 15/20 * 100 = 75, weighted 0.3 = 22.5
        // Docs: 8/10 * 100 = 80, weighted 0.3 = 24
        // Total = 78.5
        $this->assertEquals(78.5, $score);
    }

    public function test_project_handover_blockers(): void
    {
        $this->project->update([
            'total_issues' => 10,
            'open_issues' => 3,
            'total_tests' => 20,
            'completed_tests' => 20,
            'total_closeout_docs' => 5,
            'completed_closeout_docs' => 2,
        ]);

        $blockers = $this->project->getHandoverBlockers();

        $this->assertCount(2, $blockers);
        $this->assertEquals('issues', $blockers[0]['type']);
        $this->assertEquals(3, $blockers[0]['count']);
        $this->assertEquals('docs', $blockers[1]['type']);
        $this->assertEquals(3, $blockers[1]['count']);
    }

    public function test_status_mapping_resolution(): void
    {
        $status = StatusMapping::resolve(
            $this->tenant->id,
            'facility_grid',
            'issue',
            'Open',
            'work_order'
        );

        $this->assertEquals('pending', $status);
    }

    public function test_api_login_returns_token(): void
    {
        $this->tenant->update(['is_active' => true]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['token', 'user']]);
    }

    public function test_api_login_fails_with_bad_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(422);
    }

    public function test_api_dashboard_requires_auth(): void
    {
        $this->getJson('/api/v1/dashboard')->assertStatus(401);
    }

    public function test_api_dashboard_returns_data(): void
    {
        $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/dashboard')
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_api_work_orders_crud(): void
    {
        // Create
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/work-orders', [
                'project_id' => $this->project->id,
                'asset_id' => $this->asset->id,
                'title' => 'Fix chiller leak',
                'description' => 'Refrigerant leak detected',
                'priority' => 'high',
                'type' => 'corrective',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Fix chiller leak');

        $woId = $response->json('data.id');

        // Read
        $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/work-orders/{$woId}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $woId);

        // List
        $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/work-orders')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    }
}
