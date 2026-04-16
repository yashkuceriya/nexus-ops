<?php

namespace Tests\Feature;

use App\Models\AutomationRule;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\AutomationEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutomationTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $user;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Automation Test Corp',
            'slug' => 'automation-test-corp',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Automation Tester',
            'email' => 'automation@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->project = Project::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Automation Project',
            'status' => 'commissioning',
        ]);
    }

    public function test_automation_list_page_loads(): void
    {
        $this->actingAs($this->user)
            ->get('/automation')
            ->assertStatus(200);
    }

    public function test_automation_create_page_loads(): void
    {
        $this->actingAs($this->user)
            ->get('/automation/create')
            ->assertStatus(200);
    }

    public function test_automation_engine_evaluates_rules_and_executes_actions(): void
    {
        $wo = WorkOrder::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'wo_number' => WorkOrder::generateWoNumber(),
            'title' => 'Leaking pipe',
            'status' => 'pending',
            'priority' => 'low',
            'type' => 'corrective',
            'source' => 'manual',
        ]);

        $rule = AutomationRule::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Escalate critical WOs',
            'is_active' => true,
            'trigger_type' => 'work_order_created',
            'conditions' => [
                ['field' => 'priority', 'operator' => 'equals', 'value' => 'critical'],
            ],
            'actions' => [
                ['type' => 'change_priority', 'priority' => 'high'],
            ],
            'execution_count' => 0,
        ]);

        $engine = new AutomationEngine;

        // Should NOT fire - priority is 'low', not 'critical'
        $engine->evaluateRules('work_order_created', [
            'work_order_id' => $wo->id,
            'priority' => 'low',
        ], $this->tenant->id);

        $rule->refresh();
        $this->assertEquals(0, $rule->execution_count);

        // Should fire - priority matches 'critical'
        $engine->evaluateRules('work_order_created', [
            'work_order_id' => $wo->id,
            'priority' => 'critical',
        ], $this->tenant->id);

        $rule->refresh();
        $this->assertEquals(1, $rule->execution_count);
        $this->assertNotNull($rule->last_executed_at);
    }

    public function test_conditions_with_equals_operator_work(): void
    {
        $engine = new AutomationEngine;

        $this->assertTrue($engine->checkConditions(
            [['field' => 'status', 'operator' => 'equals', 'value' => 'open']],
            ['status' => 'open']
        ));

        $this->assertFalse($engine->checkConditions(
            [['field' => 'status', 'operator' => 'equals', 'value' => 'open']],
            ['status' => 'closed']
        ));
    }

    public function test_conditions_with_greater_than_operator_work(): void
    {
        $engine = new AutomationEngine;

        $this->assertTrue($engine->checkConditions(
            [['field' => 'value', 'operator' => 'greater_than', 'value' => 50]],
            ['value' => 75]
        ));

        $this->assertFalse($engine->checkConditions(
            [['field' => 'value', 'operator' => 'greater_than', 'value' => 50]],
            ['value' => 30]
        ));
    }

    public function test_rule_execution_count_increments(): void
    {
        $rule = AutomationRule::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Counter Test Rule',
            'is_active' => true,
            'trigger_type' => 'sensor_alert',
            'conditions' => [],
            'actions' => [],
            'execution_count' => 0,
        ]);

        $engine = new AutomationEngine;

        $engine->evaluateRules('sensor_alert', [], $this->tenant->id);
        $rule->refresh();
        $this->assertEquals(1, $rule->execution_count);

        $engine->evaluateRules('sensor_alert', [], $this->tenant->id);
        $rule->refresh();
        $this->assertEquals(2, $rule->execution_count);
    }
}
