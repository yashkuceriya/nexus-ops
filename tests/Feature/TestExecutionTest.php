<?php

use App\Models\Asset;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\TestExecution;
use App\Models\TestScript;
use App\Models\TestStep;
use App\Models\TestStepResult;
use App\Models\User;
use App\Services\TestExecution\TestExecutionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id, 'role' => 'admin']);
    $this->project = Project::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->asset = Asset::factory()->create([
        'tenant_id' => $this->tenant->id,
        'project_id' => $this->project->id,
        'system_type' => 'chiller',
        'category' => 'chiller',
    ]);
    $this->actingAs($this->user);
    $this->service = app(TestExecutionService::class);
});

function makePublishedScript(Tenant $tenant, int $stepCount = 3): TestScript
{
    $script = TestScript::create([
        'tenant_id' => $tenant->id,
        'name' => 'Test FPT',
        'slug' => 'test-fpt-'.uniqid(),
        'system_type' => 'chiller',
        'status' => TestScript::STATUS_PUBLISHED,
        'version' => 1,
    ]);

    for ($i = 1; $i <= $stepCount; $i++) {
        TestStep::create([
            'test_script_id' => $script->id,
            'sequence' => $i,
            'title' => "Step {$i}",
            'instruction' => "Do something #{$i}",
            'measurement_type' => $i === 1 ? 'numeric' : 'boolean',
            'expected_numeric' => $i === 1 ? 44.0 : null,
            'tolerance' => $i === 1 ? 2.0 : null,
            'measurement_unit' => $i === 1 ? '°F' : null,
        ]);
    }

    return $script;
}

it('starts an execution and snapshots every step', function () {
    $script = makePublishedScript($this->tenant, 4);

    $execution = $this->service->start($script, $this->asset, $this->user);

    expect($execution->status)->toBe(TestExecution::STATUS_IN_PROGRESS)
        ->and($execution->total_count)->toBe(4)
        ->and($execution->pending_count)->toBe(4)
        ->and($execution->test_script_version)->toBe(1)
        ->and($execution->test_script_name)->toBe('Test FPT');

    expect($execution->results()->count())->toBe(4);
    expect($execution->results()->where('status', 'pending')->count())->toBe(4);
});

it('refuses to start an unpublished script', function () {
    $script = makePublishedScript($this->tenant, 1);
    $script->update(['status' => TestScript::STATUS_DRAFT]);

    $this->service->start($script, $this->asset, $this->user);
})->throws(InvalidArgumentException::class, 'published');

it('records a passing step and updates counters', function () {
    $script = makePublishedScript($this->tenant, 3);
    $execution = $this->service->start($script, $this->asset, $this->user);
    $first = $execution->results()->orderBy('step_sequence')->first();

    $this->service->recordStepResult(
        result: $first,
        recordedBy: $this->user,
        status: TestStepResult::STATUS_PASS,
        measuredValue: '44.2',
        measuredNumeric: 44.2,
    );

    $execution->refresh();

    expect($execution->pass_count)->toBe(1)
        ->and($execution->pending_count)->toBe(2)
        ->and($execution->fail_count)->toBe(0);

    expect($first->refresh()->status)->toBe(TestStepResult::STATUS_PASS);
});

it('auto-creates a linked deficiency issue when a step fails', function () {
    $script = makePublishedScript($this->tenant, 2);
    $execution = $this->service->start($script, $this->asset, $this->user);
    $first = $execution->results()->first();

    $this->service->recordStepResult(
        result: $first,
        recordedBy: $this->user,
        status: TestStepResult::STATUS_FAIL,
        measuredValue: '52.0',
        measuredNumeric: 52.0,
        notes: 'Out of spec',
    );

    $first->refresh();

    expect($first->issue_id)->not->toBeNull();

    $issue = Issue::find($first->issue_id);
    expect($issue)->not->toBeNull()
        ->and($issue->source_system)->toBe('fpt')
        ->and($issue->issue_type)->toBe('deficiency')
        ->and($issue->priority)->toBe('high')
        ->and($issue->asset_id)->toBe($this->asset->id)
        ->and($issue->project_id)->toBe($this->project->id);
});

it('marks execution passed when all steps pass', function () {
    $script = makePublishedScript($this->tenant, 2);
    $execution = $this->service->start($script, $this->asset, $this->user);

    foreach ($execution->results as $r) {
        $this->service->recordStepResult(
            result: $r,
            recordedBy: $this->user,
            status: TestStepResult::STATUS_PASS,
        );
    }

    $closed = $this->service->complete($execution->refresh(), $this->user);

    expect($closed->status)->toBe(TestExecution::STATUS_PASSED)
        ->and($closed->completed_at)->not->toBeNull();
});

it('marks execution failed when any step fails', function () {
    $script = makePublishedScript($this->tenant, 2);
    $execution = $this->service->start($script, $this->asset, $this->user);

    $this->service->recordStepResult(
        result: $execution->results->first(),
        recordedBy: $this->user,
        status: TestStepResult::STATUS_PASS,
    );

    $this->service->recordStepResult(
        result: $execution->results->last(),
        recordedBy: $this->user,
        status: TestStepResult::STATUS_FAIL,
    );

    $closed = $this->service->complete($execution->refresh(), $this->user);

    expect($closed->status)->toBe(TestExecution::STATUS_FAILED);
});

it('refuses to complete an execution with pending steps by marking failed', function () {
    $script = makePublishedScript($this->tenant, 3);
    $execution = $this->service->start($script, $this->asset, $this->user);

    $this->service->recordStepResult(
        result: $execution->results->first(),
        recordedBy: $this->user,
        status: TestStepResult::STATUS_PASS,
    );

    $closed = $this->service->complete($execution->refresh(), $this->user);

    expect($closed->status)->toBe(TestExecution::STATUS_FAILED);
});

it('chains a retest to the failed parent execution', function () {
    $script = makePublishedScript($this->tenant, 2);
    $execution = $this->service->start($script, $this->asset, $this->user);

    foreach ($execution->results as $r) {
        $this->service->recordStepResult($r, $this->user, TestStepResult::STATUS_FAIL);
    }
    $failed = $this->service->complete($execution->refresh(), $this->user);

    $retest = $this->service->retest($failed, $this->user);

    expect($retest->parent_execution_id)->toBe($failed->id)
        ->and($retest->status)->toBe(TestExecution::STATUS_IN_PROGRESS)
        ->and($retest->results()->count())->toBe(2);
});

it('produces a tamper-evident witness signature', function () {
    $script = makePublishedScript($this->tenant, 1);
    $execution = $this->service->start($script, $this->asset, $this->user);
    $this->service->recordStepResult($execution->results->first(), $this->user, TestStepResult::STATUS_PASS);
    $execution = $this->service->complete($execution->refresh(), $this->user);

    $signed = $this->service->witnessSign($execution, $this->user);

    expect($signed->witness_signature_hash)->not->toBeNull()
        ->and($signed->witness_signed_at)->not->toBeNull();

    expect($this->service->verifyWitnessSignature($signed->refresh()))->toBeTrue();

    $signed->update(['pass_count' => 9999]);
    expect($this->service->verifyWitnessSignature($signed->refresh()))->toBeFalse();
});

it('blocks cross-tenant execution start', function () {
    $otherTenant = Tenant::factory()->create();
    $otherUser = User::factory()->create(['tenant_id' => $otherTenant->id]);

    $script = makePublishedScript($this->tenant, 1);

    $this->service->start($script, $this->asset, $otherUser);
})->throws(InvalidArgumentException::class, 'same tenant');

it('system scripts are visible across tenants', function () {
    $systemScript = TestScript::create([
        'tenant_id' => null,
        'name' => 'System Chiller FPT',
        'slug' => 'sys-chiller',
        'system_type' => 'chiller',
        'status' => TestScript::STATUS_PUBLISHED,
        'version' => 1,
        'is_system' => true,
    ]);

    $scripts = TestScript::availableTo($this->tenant->id)->get();

    expect($scripts->pluck('id'))->toContain($systemScript->id);
});
