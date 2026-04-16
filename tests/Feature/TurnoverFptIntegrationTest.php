<?php

declare(strict_types=1);

use App\Livewire\CxTestMatrix;
use App\Livewire\PortfolioDashboard;
use App\Livewire\TestExecutionList;
use App\Livewire\TurnoverConsole;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\TestScript;
use App\Models\TestStep;
use App\Models\TestStepResult;
use App\Models\User;
use App\Services\TestExecution\TestExecutionService;
use App\Services\Turnover\TurnoverPackageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

/**
 * These tests cover the v3 enhancements that surface FPT data everywhere it
 * matters for handover: the Turnover Package PDF payload, the Turnover
 * Console Livewire page, the Cx Matrix CSV export, the executions list
 * stats / filters, and the portfolio dashboard widget.
 */
beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id, 'role' => 'admin']);
    $this->project = Project::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Test Project Alpha',
    ]);
    $this->asset = Asset::factory()->create([
        'tenant_id' => $this->tenant->id,
        'project_id' => $this->project->id,
        'system_type' => 'chiller',
        'category' => 'chiller',
    ]);
    $this->actingAs($this->user);
});

function seedFptExecutions($ctx, int $passed = 2, int $failed = 1, bool $witnessed = true): array
{
    $service = app(TestExecutionService::class);
    $executions = [];

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

        if ($witnessed) {
            $service->witnessSign(
                execution: $execution->refresh(),
                witness: $ctx->user,
                signatureImage: 'data:image/png;base64,aGk=',
            );
        }

        $executions[] = $execution->refresh();
    }

    return $executions;
}

it('turnover package payload includes a full FPT scorecard', function () {
    seedFptExecutions($this, passed: 2, failed: 1);

    $payload = app(TurnoverPackageService::class)->buildPayload($this->project->refresh());

    expect($payload)->toHaveKey('fpt');
    expect($payload['fpt']['executions_total'])->toBe(3)
        ->and($payload['fpt']['executions_passed'])->toBe(2)
        ->and($payload['fpt']['executions_failed'])->toBe(1)
        ->and($payload['fpt']['execution_pass_rate'])->toBe(66.7);

    $levelRows = collect($payload['fpt']['by_level']);
    expect($levelRows->firstWhere('level', 'L3'))->not->toBeNull();

    expect($payload['fpt']['rows'])->toHaveCount(3);
    expect($payload['fpt']['rows'][0])->toHaveKeys([
        'id', 'script', 'asset', 'cx_level', 'status', 'witnessed', 'starter',
    ]);
});

it('turnover console renders readiness, blockers, and FPT metrics', function () {
    seedFptExecutions($this, passed: 2, failed: 1);

    Livewire::test(TurnoverConsole::class, ['projectId' => $this->project->id])
        ->assertSee('Accelerated Turnover Package')
        ->assertSee('Handover Readiness')
        ->assertSee('Commissioning Performance')
        ->assertSee('66.7%')           // pass rate
        ->assertSee('3')               // execution total
        ->assertSee('Download Package');
});

it('turnover console lists generation history from the audit log', function () {
    AuditLog::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'action' => 'turnover_package_generated',
        'auditable_type' => (new Project)->getMorphClass(),
        'auditable_id' => $this->project->id,
        'new_values' => [
            'filename' => 'Turnover_Package_Alpha_2026-04-15.pdf',
            'readiness_score' => 87.5,
        ],
    ]);

    Livewire::test(TurnoverConsole::class, ['projectId' => $this->project->id])
        ->assertSee('Turnover_Package_Alpha_2026-04-15.pdf')
        ->assertSee('88%');
});

it('cx matrix exports a CSV with assets, scripts, and cell statuses', function () {
    seedFptExecutions($this, passed: 1, failed: 0, witnessed: true);

    // Call the exporter directly so we can capture and inspect the streamed
    // CSV bytes. Livewire happily returns the StreamedResponse untouched.
    $component = Livewire::test(CxTestMatrix::class, ['projectId' => $this->project->id]);
    $response = $component->instance()->exportCsv();

    expect($response->headers->get('Content-Type'))->toContain('text/csv');
    expect($response->headers->get('Content-Disposition'))->toContain('cx-matrix-');

    ob_start();
    $response->sendContent();
    $csv = ob_get_clean();

    expect($csv)->toContain('Asset')
        ->toContain($this->asset->name)
        ->toContain('passed')
        // Witnessed executions should carry a bracket tag so spreadsheet
        // consumers can filter quickly.
        ->toContain('witnessed');
});

it('executions list computes tenant-scoped stats honouring the filters', function () {
    seedFptExecutions($this, passed: 2, failed: 1, witnessed: true);

    // An out-of-tenant execution should not leak into stats.
    $otherTenant = Tenant::factory()->create();
    $otherUser = User::factory()->create(['tenant_id' => $otherTenant->id]);
    $otherProject = Project::factory()->create(['tenant_id' => $otherTenant->id]);
    $otherAsset = Asset::factory()->create(['tenant_id' => $otherTenant->id, 'project_id' => $otherProject->id]);
    $otherScript = TestScript::create([
        'tenant_id' => $otherTenant->id,
        'name' => 'Other',
        'slug' => 'other-'.uniqid(),
        'system_type' => 'chiller',
        'status' => TestScript::STATUS_PUBLISHED,
        'version' => 1,
    ]);
    TestStep::create([
        'test_script_id' => $otherScript->id,
        'sequence' => 1,
        'title' => 'x',
        'instruction' => 'x',
        'measurement_type' => TestStep::TYPE_BOOLEAN,
    ]);
    app(TestExecutionService::class)->start($otherScript, $otherAsset, $otherUser);

    $component = Livewire::test(TestExecutionList::class);
    $stats = $component->instance()->stats;

    expect($stats['total'])->toBe(3)
        ->and($stats['passed'])->toBe(2)
        ->and($stats['failed'])->toBe(1)
        ->and($stats['witnessed'])->toBe(3)
        ->and($stats['pass_rate'])->toBe(66.7);

    $component->set('witnessedOnly', true);
    expect($component->instance()->stats['total'])->toBe(3);

    $component->set('statusFilter', 'failed');
    expect($component->instance()->stats['total'])->toBe(1);
});

it('dashboard widget shows portfolio-wide FPT snapshot', function () {
    seedFptExecutions($this, passed: 2, failed: 1, witnessed: true);

    $component = Livewire::test(PortfolioDashboard::class);
    $snapshot = $component->instance()->commissioningSnapshot;

    expect($snapshot['total'])->toBe(3)
        ->and($snapshot['passed'])->toBe(2)
        ->and($snapshot['failed'])->toBe(1)
        ->and($snapshot['witnessed'])->toBe(3)
        ->and($snapshot['pass_rate'])->toBe(66.7)
        ->and($snapshot['witness_pct'])->toBe(100.0);

    $component->assertSee('Commissioning Performance');
});
