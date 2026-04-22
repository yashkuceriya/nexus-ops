<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\CxTestMatrix;
use App\Livewire\FloorPlan;
use App\Livewire\PortfolioDashboard;
use App\Models\Asset;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireComponentSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@acme.com')->firstOrFail();
    }

    public function test_portfolio_dashboard_mounts_and_refreshes_kpis(): void
    {
        $component = Livewire::actingAs($this->admin)
            ->test(PortfolioDashboard::class)
            ->assertOk()
            ->assertSet('tenantId', $this->admin->tenant_id);

        $kpis = $component->instance()->kpis;
        $this->assertIsArray($kpis);
        $this->assertArrayHasKey('mttr_hours', $kpis);
        $this->assertArrayHasKey('pm_compliance', $kpis);
        $this->assertArrayHasKey('open_work_orders', $kpis);

        $component->call('refreshKpis')->assertOk();
        $this->assertNotEmpty($component->get('lastUpdated'));
    }

    public function test_floor_plan_mounts_and_selects_an_asset(): void
    {
        $asset = Asset::where('tenant_id', $this->admin->tenant_id)->first();
        $this->assertNotNull($asset, 'Seeder should create at least one asset for the admin tenant.');

        $component = Livewire::actingAs($this->admin)
            ->test(FloorPlan::class)
            ->assertOk();

        $this->assertIsArray($component->instance()->assetPins);

        $component->call('selectAsset', $asset->id)->assertOk();
    }

    public function test_cx_test_matrix_mounts_and_clears_filters(): void
    {
        $project = Project::where('tenant_id', $this->admin->tenant_id)->firstOrFail();

        Livewire::actingAs($this->admin)
            ->test(CxTestMatrix::class, ['projectId' => $project->id])
            ->assertOk()
            ->set('levelFilter', 'system')
            ->set('systemFilter', 'HVAC')
            ->call('clearFilters')
            ->assertSet('levelFilter', '')
            ->assertSet('systemFilter', '');
    }

    protected User $admin;
}
