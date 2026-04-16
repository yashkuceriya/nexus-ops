<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorContract;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;
    private Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Vendor Test Corp',
            'slug' => 'vendor-test-corp',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Vendor Tester',
            'email' => 'vendor@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->vendor = Vendor::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Acme HVAC Services',
            'contact_name' => 'John Smith',
            'email' => 'john@acmehvac.com',
            'phone' => '555-0100',
            'trade_specialties' => ['HVAC', 'Refrigeration'],
            'is_active' => true,
        ]);
    }

    public function test_vendor_list_page_loads(): void
    {
        $this->actingAs($this->user)
            ->get('/vendors')
            ->assertStatus(200);
    }

    public function test_vendor_detail_page_loads(): void
    {
        $this->actingAs($this->user)
            ->get("/vendors/{$this->vendor->id}")
            ->assertStatus(200);
    }

    public function test_vendor_has_contracts_relationship(): void
    {
        $contract = VendorContract::create([
            'vendor_id' => $this->vendor->id,
            'tenant_id' => $this->tenant->id,
            'title' => 'Annual HVAC Maintenance',
            'contract_number' => 'CON-2026-001',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addYear(),
            'status' => 'active',
            'monthly_cost' => 2500.00,
            'annual_value' => 30000.00,
        ]);

        $this->vendor->refresh();
        $contracts = $this->vendor->contracts;

        $this->assertCount(1, $contracts);
        $this->assertEquals('Annual HVAC Maintenance', $contracts->first()->title);
    }

    public function test_api_work_orders_include_vendor_id(): void
    {
        $project = Project::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Vendor WO Project',
            'status' => 'commissioning',
        ]);

        $wo = WorkOrder::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $project->id,
            'vendor_id' => $this->vendor->id,
            'wo_number' => WorkOrder::generateWoNumber(),
            'title' => 'HVAC repair via vendor',
            'status' => 'open',
            'priority' => 'medium',
            'type' => 'corrective',
            'source' => 'manual',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/work-orders/{$wo->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.vendor_id', $this->vendor->id);
    }

    public function test_vendor_trade_specialties_are_stored_as_array(): void
    {
        $this->vendor->refresh();

        $this->assertIsArray($this->vendor->trade_specialties);
        $this->assertContains('HVAC', $this->vendor->trade_specialties);
        $this->assertContains('Refrigeration', $this->vendor->trade_specialties);
    }
}
