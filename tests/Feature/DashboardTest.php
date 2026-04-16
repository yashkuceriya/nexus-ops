<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Test Corp',
            'slug' => 'test-corp',
        ]);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_authenticated_users_can_access_dashboard(): void
    {
        $this->actingAs($this->user)
            ->get('/dashboard')
            ->assertStatus(200)
            ->assertSee('Portfolio Readiness Dashboard');
    }

    public function test_authenticated_users_can_access_projects(): void
    {
        $this->actingAs($this->user)
            ->get('/projects')
            ->assertStatus(200);
    }

    public function test_authenticated_users_can_access_work_orders(): void
    {
        $this->actingAs($this->user)
            ->get('/work-orders')
            ->assertStatus(200);
    }

    public function test_authenticated_users_can_access_assets(): void
    {
        $this->actingAs($this->user)
            ->get('/assets')
            ->assertStatus(200);
    }

    public function test_authenticated_users_can_access_sensors(): void
    {
        $this->actingAs($this->user)
            ->get('/sensors')
            ->assertStatus(200);
    }

    public function test_health_endpoint_returns_ok(): void
    {
        $this->get('/health')->assertStatus(200)->assertSee('OK');
    }

    public function test_login_page_renders(): void
    {
        $this->get('/login')
            ->assertStatus(200)
            ->assertSee('Nexus')
            ->assertSee('Ops');
    }

    public function test_login_with_valid_credentials(): void
    {
        $this->post('/login', [
            'email' => 'test@test.com',
            'password' => 'password',
        ])->assertRedirect('/dashboard');
    }

    public function test_login_with_invalid_credentials(): void
    {
        $this->post('/login', [
            'email' => 'test@test.com',
            'password' => 'wrong',
        ])->assertSessionHasErrors('email');
    }
}
