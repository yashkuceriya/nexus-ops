<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Project;
use App\Models\TestExecution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeNewRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_routes_return_200(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@acme.com')->firstOrFail();
        $project = Project::where('tenant_id', $admin->tenant_id)->firstOrFail();
        $execution = TestExecution::where('tenant_id', $admin->tenant_id)->first();

        $routes = [
            '/fpt/scripts',
            '/fpt/executions',
            '/lessons-learned',
            '/reports/commissioning',
            "/projects/{$project->id}/pfc",
            "/projects/{$project->id}/cx-matrix",
            "/projects/{$project->id}/turnover",
            "/projects/{$project->id}/closeout",
        ];

        if ($execution) {
            $routes[] = "/fpt/executions/{$execution->id}";
        }

        foreach ($routes as $route) {
            $response = $this->actingAs($admin)->get($route);
            $this->assertSame(200, $response->status(), "Route {$route} returned {$response->status()}");
        }
    }
}
