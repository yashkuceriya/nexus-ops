<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Auth Test Corp',
            'slug' => 'auth-test-corp',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Auth Test User',
            'email' => 'auth@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    public function test_login_page_loads(): void
    {
        $this->get('/login')
            ->assertStatus(200)
            ->assertSee('Nexus')
            ->assertSee('Ops');
    }

    public function test_login_with_valid_credentials_redirects_to_dashboard(): void
    {
        $this->post('/login', [
            'email' => 'auth@test.com',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($this->user);
    }

    public function test_login_with_invalid_credentials_shows_error(): void
    {
        $this->post('/login', [
            'email' => 'auth@test.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_logout_works(): void
    {
        $this->actingAs($this->user)
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_unauthenticated_api_calls_return_401(): void
    {
        $this->getJson('/api/v1/dashboard')->assertStatus(401);
        $this->getJson('/api/v1/work-orders')->assertStatus(401);
        $this->getJson('/api/v1/assets')->assertStatus(401);
        $this->getJson('/api/v1/sensors')->assertStatus(401);
    }

    public function test_api_login_returns_sanctum_token(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'auth@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['token', 'user']]);

        $this->assertNotEmpty($response->json('data.token'));
    }
}
