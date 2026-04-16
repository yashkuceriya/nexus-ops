<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $user;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Project Test Corp',
            'slug' => 'project-test-corp',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Project Tester',
            'email' => 'project@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->project = Project::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Tower A Commissioning',
            'status' => 'commissioning',
            'total_issues' => 20,
            'open_issues' => 4,
            'total_tests' => 50,
            'completed_tests' => 40,
            'total_closeout_docs' => 10,
            'completed_closeout_docs' => 7,
        ]);
    }

    public function test_projects_page_loads_for_authenticated_user(): void
    {
        $this->actingAs($this->user)
            ->get('/projects')
            ->assertStatus(200);
    }

    public function test_project_detail_page_loads(): void
    {
        $this->actingAs($this->user)
            ->get("/projects/{$this->project->id}")
            ->assertStatus(200);
    }

    public function test_project_readiness_score_calculation_is_correct(): void
    {
        // Issues: (20-4)/20 * 100 = 80, weighted 0.30 = 24
        // Tests: 40/50 * 100       = 80, weighted 0.20 = 16
        // Docs:  7/10 * 100        = 70, weighted 0.20 = 14
        // FPT:   no executions → 100 (neutral), weighted 0.30 = 30
        // Total = 84
        $score = $this->project->calculateReadinessScore();
        $this->assertEquals(84.0, $score);
    }

    public function test_project_handover_blockers_are_computed_correctly(): void
    {
        $blockers = $this->project->getHandoverBlockers();

        $this->assertCount(3, $blockers);
        $this->assertEquals('issues', $blockers[0]['type']);
        $this->assertEquals(4, $blockers[0]['count']);
        $this->assertEquals('tests', $blockers[1]['type']);
        $this->assertEquals(10, $blockers[1]['count']);
        $this->assertEquals('docs', $blockers[2]['type']);
        $this->assertEquals(3, $blockers[2]['count']);
    }

    public function test_unauthenticated_user_cannot_access_projects(): void
    {
        $this->get('/projects')->assertRedirect('/login');
    }

    public function test_api_project_readiness_endpoint_returns_data(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/dashboard/projects/{$this->project->id}/readiness")
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    }
}
