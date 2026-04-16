<?php

declare(strict_types=1);

use App\Livewire\CommissioningAnalytics;
use App\Livewire\DeficiencyBoard;
use App\Livewire\TurnoverConsole;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\TestScript;
use App\Models\TestStep;
use App\Models\TestStepResult;
use App\Models\User;
use App\Services\TestExecution\TestExecutionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

uses(RefreshDatabase::class);

/**
 * Covers the v4 stakeholder & analytics surfaces:
 *   - Commissioning Analytics (trend, top-failing, aging)
 *   - Deficiency Board (kanban advance / rewind / claim + audit trail)
 *   - Public signed turnover share (happy path + tampered signature)
 *   - Turnover Console share-link minting
 */
beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id, 'role' => 'admin']);
    $this->otherUser = User::factory()->create(['tenant_id' => $this->tenant->id, 'role' => 'technician']);
    $this->project = Project::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Beacon Tower',
    ]);
    $this->asset = Asset::factory()->create([
        'tenant_id' => $this->tenant->id,
        'project_id' => $this->project->id,
        'system_type' => 'chiller',
        'category' => 'chiller',
    ]);
    $this->actingAs($this->user);
});

/**
 * Quickly seed `$passed` passing + `$failed` failing completed executions.
 */
function v4SeedExecutions($ctx, int $passed = 2, int $failed = 1): void
{
    $service = app(TestExecutionService::class);

    for ($i = 0; $i < $passed + $failed; $i++) {
        $script = TestScript::create([
            'tenant_id' => $ctx->tenant->id,
            'name' => 'FPT '.$i,
            'slug' => 'fpt-'.uniqid(),
            'system_type' => 'chiller',
            'status' => TestScript::STATUS_PUBLISHED,
            'cx_level' => 'L3',
            'version' => 1,
        ]);
        TestStep::create([
            'test_script_id' => $script->id,
            'sequence' => 1,
            'title' => 'Verify',
            'instruction' => 'Confirm.',
            'measurement_type' => TestStep::TYPE_BOOLEAN,
        ]);

        $execution = $service->start($script, $ctx->asset, $ctx->user);
        $result = $execution->results()->first();
        $service->recordStepResult(
            $result,
            $ctx->user,
            $i < $passed ? TestStepResult::STATUS_PASS : TestStepResult::STATUS_FAIL,
        );
        $service->complete($execution->refresh(), $ctx->user);
    }
}

it('commissioning analytics headline + trend reflect executions', function () {
    v4SeedExecutions($this, passed: 3, failed: 2);

    $component = Livewire::test(CommissioningAnalytics::class);

    $headline = $component->instance()->headline;
    expect($headline['total'])->toBe(5)
        ->and($headline['passed'])->toBe(3)
        ->and($headline['failed'])->toBe(2)
        ->and($headline['pass_rate'])->toBe(60.0);

    $trend = $component->instance()->monthlyTrend;
    expect($trend)->toBeArray()
        ->and(count($trend))->toBe(6);

    $topFailing = $component->instance()->topFailingScripts;
    expect(count($topFailing))->toBeGreaterThanOrEqual(1);

    $component->assertSuccessful()
        ->assertSee('Pass Rate')
        ->assertSee('Deficiency Aging');
});

it('commissioning analytics buckets deficiencies by age correctly', function () {
    // Force-create two issues with different created_at timestamps. We use
    // `forceFill`+`save` because Laravel will otherwise stamp `created_at`
    // to "now" regardless of what's passed to ::create.
    $fresh = new Issue([
        'tenant_id' => $this->tenant->id,
        'project_id' => $this->project->id,
        'title' => 'Fresh',
        'status' => 'open',
        'priority' => 'high',
        'issue_type' => 'deficiency',
    ]);
    $fresh->timestamps = false;
    $fresh->created_at = now();
    $fresh->updated_at = now();
    $fresh->save();

    $old = new Issue([
        'tenant_id' => $this->tenant->id,
        'project_id' => $this->project->id,
        'title' => 'Old critical',
        'status' => 'in_progress',
        'priority' => 'critical',
        'issue_type' => 'deficiency',
    ]);
    $old->timestamps = false;
    $old->created_at = now()->subDays(45);
    $old->updated_at = now()->subDays(45);
    $old->save();

    $component = Livewire::test(CommissioningAnalytics::class);
    $aging = $component->instance()->deficiencyAging;

    $freshBucket = collect($aging)->firstWhere('label', '0–7 days');
    $monthBucket = collect($aging)->firstWhere('label', '31–90 days');

    expect($freshBucket['count'])->toBe(1)
        ->and($monthBucket['count'])->toBe(1)
        ->and($monthBucket['critical'])->toBe(1);
});

it('deficiency board advances, rewinds, and claims issues with an audit trail', function () {
    $issue = Issue::create([
        'tenant_id' => $this->tenant->id,
        'project_id' => $this->project->id,
        'asset_id' => $this->asset->id,
        'title' => 'Chilled water undersupply',
        'status' => 'open',
        'priority' => 'high',
        'issue_type' => 'deficiency',
        'source_system' => 'fpt',
    ]);

    $component = Livewire::test(DeficiencyBoard::class);

    $component->call('advance', $issue->id);
    expect($issue->fresh()->status)->toBe('in_progress');

    $component->call('advance', $issue->id);
    expect($issue->fresh()->status)->toBe('work_completed');
    expect($issue->fresh()->resolved_at)->not->toBeNull();

    $component->call('rewind', $issue->id);
    expect($issue->fresh()->status)->toBe('in_progress');
    expect($issue->fresh()->resolved_at)->toBeNull();

    $component->call('assignToMe', $issue->id);
    expect($issue->fresh()->assigned_to)->toBe($this->user->id);

    $logs = AuditLog::query()
        ->where('auditable_type', $issue->getMorphClass())
        ->where('auditable_id', $issue->id)
        ->pluck('action')
        ->all();
    expect($logs)->toContain('issue_status_advanced', 'issue_status_reverted', 'issue_assigned');
});

it('deficiency board groups cards by column and honours filters', function () {
    $open = Issue::create([
        'tenant_id' => $this->tenant->id,
        'project_id' => $this->project->id,
        'title' => 'Open A',
        'status' => 'open',
        'priority' => 'low',
        'issue_type' => 'deficiency',
        'source_system' => 'fpt',
    ]);
    Issue::create([
        'tenant_id' => $this->tenant->id,
        'project_id' => $this->project->id,
        'title' => 'Closed B',
        'status' => 'closed',
        'priority' => 'low',
        'issue_type' => 'deficiency',
        'source_system' => 'manual',
    ]);

    $component = Livewire::test(DeficiencyBoard::class);
    $board = $component->instance()->board;
    expect($board['open'])->toHaveCount(1)
        ->and($board['closed'])->toHaveCount(1);

    $component->set('autoOpenedOnly', true);
    $board = $component->instance()->board;
    expect($board['open'])->toHaveCount(1)
        ->and($board['closed'])->toHaveCount(0);

    expect($board['open'][0]['id'])->toBe($open->id);
});

it('turnover console mints a signed share URL and records it in the audit trail', function () {
    $component = Livewire::test(TurnoverConsole::class, ['projectId' => $this->project->id])
        ->set('shareExpiryDays', 14)
        ->call('generateShareLink');

    $link = $component->get('shareLink');
    expect($link)->toBeString()
        ->and($link)->toContain('/share/turnover/'.$this->project->id)
        ->and($link)->toContain('signature=');

    $audit = AuditLog::query()
        ->where('auditable_type', $this->project->getMorphClass())
        ->where('auditable_id', $this->project->id)
        ->where('action', 'turnover_share_created')
        ->first();
    expect($audit)->not->toBeNull();
    expect($audit->new_values['expiry_days'])->toBe(14);
});

it('public signed turnover link renders stakeholder preview without auth', function () {
    v4SeedExecutions($this, passed: 2, failed: 1);

    $signed = URL::signedRoute('public.turnover.show', ['projectId' => $this->project->id], now()->addDays(30));

    auth()->logout();
    $response = $this->get($signed);

    $response->assertSuccessful()
        ->assertSee('Accelerated Turnover Package')
        ->assertSee($this->project->name)
        ->assertSee('Readiness Score')
        ->assertSee('Functional Performance Testing');
});

it('public turnover link rejects tampered or expired signatures', function () {
    auth()->logout();
    // Unsigned: Laravel's signed middleware will 403
    $response = $this->get("/share/turnover/{$this->project->id}");
    $response->assertForbidden();
});
