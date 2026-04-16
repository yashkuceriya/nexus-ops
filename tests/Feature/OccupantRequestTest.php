<?php

namespace Tests\Feature;

use App\Models\OccupantRequest;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OccupantRequestTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Occupant Test Corp',
            'slug' => 'occupant-test-corp',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Occupant Tester',
            'email' => 'occupant@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->project = Project::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Occupant Project',
            'status' => 'commissioning',
        ]);
    }

    public function test_public_request_page_loads_without_auth(): void
    {
        $this->get('/request')
            ->assertStatus(200);
    }

    public function test_request_tracker_page_loads_without_auth(): void
    {
        $this->get('/request/SOMETOKEN')
            ->assertStatus(200);
    }

    public function test_tracking_token_generation_produces_unique_tokens(): void
    {
        $token1 = OccupantRequest::generateTrackingToken();
        $token2 = OccupantRequest::generateTrackingToken();

        $this->assertNotEmpty($token1);
        $this->assertNotEmpty($token2);
        $this->assertEquals(8, strlen($token1));
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{8}$/', $token1);
    }

    public function test_occupant_request_record_can_be_created(): void
    {
        $token = OccupantRequest::generateTrackingToken();

        $request = OccupantRequest::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'tracking_token' => $token,
            'requester_name' => 'Jane Doe',
            'requester_email' => 'jane@building.com',
            'category' => 'hvac',
            'description' => 'Office too cold on floor 3',
            'status' => 'submitted',
        ]);

        $this->assertDatabaseHas('occupant_requests', [
            'tracking_token' => $token,
            'requester_name' => 'Jane Doe',
            'category' => 'hvac',
        ]);

        $this->assertEquals('submitted', $request->status);
    }

    public function test_occupant_request_belongs_to_project(): void
    {
        $request = OccupantRequest::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'tracking_token' => OccupantRequest::generateTrackingToken(),
            'requester_name' => 'Bob',
            'requester_email' => 'bob@building.com',
            'category' => 'electrical',
            'description' => 'Flickering lights in lobby',
            'status' => 'submitted',
        ]);

        $this->assertEquals($this->project->id, $request->project->id);
        $this->assertEquals('Occupant Project', $request->project->name);
    }
}
