<?php

declare(strict_types=1);

use App\Livewire\CommissioningAnalytics;
use App\Livewire\PreFunctionalChecklistBoard;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\ChecklistCompletion;
use App\Models\ChecklistTemplate;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\WeeklyCxDigestNotification;
use App\Services\Checklist\PreFunctionalChecklistService;
use App\Services\Turnover\TurnoverPackageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

uses(RefreshDatabase::class);

/**
 * Covers the v5 Pre-Functional Checklist module end-to-end:
 *   - Service start / answer / complete with auto-issue generation
 *   - Blocker + scorecard integration in turnover payload
 *   - Livewire board cell states + runner persistence
 *   - Weekly Cx digest command (snapshot correctness + dispatch)
 */
beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id, 'role' => 'admin']);
    $this->project = Project::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Harbor Lights',
    ]);
    $this->asset = Asset::factory()->create([
        'tenant_id' => $this->tenant->id,
        'project_id' => $this->project->id,
        'system_type' => 'chiller',
        'category' => 'chiller',
    ]);
    $this->template = ChecklistTemplate::create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Chiller PFC Test',
        'type' => ChecklistTemplate::TYPE_PFC,
        'category' => 'hvac',
        'cx_level' => 'L1',
        'system_types' => ['chiller'],
        'steps' => [
            ['order' => 1, 'title' => 'Nameplate matches submittal', 'type' => 'pass_fail', 'priority' => 'high'],
            ['order' => 2, 'title' => 'Clearance verified', 'type' => 'pass_fail', 'priority' => 'medium'],
            ['order' => 3, 'title' => 'Electrical disconnect installed', 'type' => 'pass_fail', 'priority' => 'critical'],
        ],
        'is_active' => true,
    ]);
    $this->actingAs($this->user);
});

it('starts a pfc, records responses, and completes cleanly without opening issues', function () {
    $service = app(PreFunctionalChecklistService::class);

    $completion = $service->start($this->template, $this->asset, $this->user);
    expect($completion->status)->toBe(ChecklistCompletion::STATUS_IN_PROGRESS)
        ->and($completion->project_id)->toBe($this->project->id)
        ->and($completion->asset_id)->toBe($this->asset->id)
        ->and($completion->type)->toBe(ChecklistTemplate::TYPE_PFC);

    foreach ([1, 2, 3] as $order) {
        $service->recordResponse($completion, $order, 'pass');
    }

    $completed = $service->complete($completion->refresh(), $this->user);

    expect($completed->status)->toBe(ChecklistCompletion::STATUS_COMPLETED)
        ->and($completed->pass_count)->toBe(3)
        ->and($completed->fail_count)->toBe(0)
        ->and($completed->completed_at)->not->toBeNull();

    $issues = Issue::where('source_system', 'pfc')->count();
    expect($issues)->toBe(0);
});

it('completing a pfc with failed items auto-opens deficiency issues', function () {
    $service = app(PreFunctionalChecklistService::class);
    $completion = $service->start($this->template, $this->asset, $this->user);

    $service->recordResponse($completion, 1, 'pass');
    $service->recordResponse($completion, 2, 'fail', notes: 'Clearance only 24" on the back side.');
    $service->recordResponse($completion, 3, 'fail', notes: 'Disconnect missing.');

    $completed = $service->complete($completion->refresh(), $this->user);

    expect($completed->status)->toBe(ChecklistCompletion::STATUS_FAILED)
        ->and($completed->fail_count)->toBe(2);

    $issues = Issue::where('source_system', 'pfc')
        ->where('asset_id', $this->asset->id)
        ->get();

    expect($issues)->toHaveCount(2)
        ->and($issues->pluck('priority')->all())->toContain('critical')
        ->and($issues->pluck('title')->first())->toContain('PFC item failed');

    $audit = AuditLog::where('action', 'pfc_completed')->latest('id')->first();
    expect($audit)->not->toBeNull()
        ->and($audit->new_values['fail_count'])->toBe(2);
});

it('returns an existing in-progress completion instead of creating a duplicate', function () {
    $service = app(PreFunctionalChecklistService::class);

    $first = $service->start($this->template, $this->asset, $this->user);
    $second = $service->start($this->template, $this->asset, $this->user);

    expect($second->id)->toBe($first->id);
});

it('pfc payload + blockers roll into the turnover package', function () {
    $service = app(PreFunctionalChecklistService::class);

    // Clean completion.
    $clean = $service->start($this->template, $this->asset, $this->user);
    foreach ([1, 2, 3] as $order) {
        $service->recordResponse($clean, $order, 'pass');
    }
    $service->complete($clean->refresh(), $this->user);

    // Failed completion against a second asset.
    $asset2 = Asset::factory()->create([
        'tenant_id' => $this->tenant->id,
        'project_id' => $this->project->id,
        'system_type' => 'chiller',
        'category' => 'chiller',
    ]);
    $fail = $service->start($this->template, $asset2, $this->user);
    $service->recordResponse($fail, 1, 'pass');
    $service->recordResponse($fail, 2, 'fail', notes: 'Gap.');
    $service->complete($fail->refresh(), $this->user);

    // In-flight completion against a third asset.
    $asset3 = Asset::factory()->create([
        'tenant_id' => $this->tenant->id,
        'project_id' => $this->project->id,
        'system_type' => 'chiller',
        'category' => 'chiller',
    ]);
    $service->start($this->template, $asset3, $this->user);

    $payload = app(TurnoverPackageService::class)->buildPayload($this->project->refresh());

    expect($payload['pfc'])->toBeArray()
        ->and($payload['pfc']['total'])->toBe(3)
        ->and($payload['pfc']['completed'])->toBe(1)
        ->and($payload['pfc']['failed'])->toBe(1)
        ->and($payload['pfc']['in_progress'])->toBe(1)
        ->and($payload['pfc']['clean_rate'])->toBe(50.0);

    $blockerTypes = collect($payload['handover_blockers'])->pluck('type')->all();
    expect($blockerTypes)->toContain('pfcs');
});

it('pfc board renders cells with correct statuses and opens the runner', function () {
    $service = app(PreFunctionalChecklistService::class);
    $completion = $service->start($this->template, $this->asset, $this->user);
    $service->recordResponse($completion, 1, 'pass');

    $component = Livewire::test(PreFunctionalChecklistBoard::class, ['projectId' => $this->project->id])
        ->assertSuccessful()
        ->assertSee('Chiller PFC Test');

    // Open the runner for the seeded (asset, template) pair.
    $component->call('openRunner', $this->asset->id, $this->template->id);

    expect($component->get('activeAssetId'))->toBe($this->asset->id)
        ->and($component->get('activeTemplateId'))->toBe($this->template->id);

    // Persist a pass/fail through the runner and save as complete.
    $component->call('setResponse', 2, 'pass');
    $component->call('setResponse', 3, 'fail', 'Missing lockable disconnect.');
    $component->call('saveRunner', true);

    $fresh = ChecklistCompletion::find($completion->id);
    expect($fresh->status)->toBe(ChecklistCompletion::STATUS_FAILED)
        ->and($fresh->fail_count)->toBe(1);

    $issues = Issue::where('source_system', 'pfc')->get();
    expect($issues)->toHaveCount(1);
});

it('weekly cx digest command compiles a snapshot and notifies admins', function () {
    Notification::fake();

    // Seed one clean + one failed PFC so the snapshot is non-trivial.
    $service = app(PreFunctionalChecklistService::class);
    $clean = $service->start($this->template, $this->asset, $this->user);
    foreach ([1, 2, 3] as $order) {
        $service->recordResponse($clean, $order, 'pass');
    }
    $service->complete($clean->refresh(), $this->user);

    $asset2 = Asset::factory()->create([
        'tenant_id' => $this->tenant->id,
        'project_id' => $this->project->id,
        'system_type' => 'chiller',
        'category' => 'chiller',
    ]);
    $failed = $service->start($this->template, $asset2, $this->user);
    $service->recordResponse($failed, 1, 'pass');
    $service->recordResponse($failed, 2, 'fail');
    $service->complete($failed->refresh(), $this->user);

    $this->artisan('cx:weekly-digest', ['--tenant' => $this->tenant->id])
        ->assertSuccessful();

    Notification::assertSentTo($this->user, WeeklyCxDigestNotification::class, function ($notification) {
        $data = $notification->toArray($this->user);

        return $data['type'] === 'cx_weekly_digest'
            && $data['pfc_total'] === 2
            && $data['pfc_completed'] === 1
            && $data['pfc_failed'] === 1;
    });
});

it('dry-run mode of weekly digest does not send notifications', function () {
    Notification::fake();

    $this->artisan('cx:weekly-digest', ['--tenant' => $this->tenant->id, '--dry' => true])
        ->assertSuccessful();

    Notification::assertNothingSent();
});

it('commissioning analytics exposes pfc snapshot when completions exist', function () {
    $service = app(PreFunctionalChecklistService::class);
    $completion = $service->start($this->template, $this->asset, $this->user);
    foreach ([1, 2, 3] as $order) {
        $service->recordResponse($completion, $order, 'pass');
    }
    $service->complete($completion->refresh(), $this->user);

    $component = Livewire::test(CommissioningAnalytics::class);
    $snap = $component->instance()->pfcSnapshot;

    expect($snap['total'])->toBe(1)
        ->and($snap['clean_rate'])->toBe(100.0);

    $component->assertSuccessful()
        ->assertSee('Pre-Functional Checklists');
});

it('public turnover preview shows pfc section when pfc data exists', function () {
    $service = app(PreFunctionalChecklistService::class);
    $completion = $service->start($this->template, $this->asset, $this->user);
    foreach ([1, 2, 3] as $order) {
        $service->recordResponse($completion, $order, 'pass');
    }
    $service->complete($completion->refresh(), $this->user);

    $signed = URL::signedRoute('public.turnover.show', ['projectId' => $this->project->id], now()->addDays(30));
    auth()->logout();

    $this->get($signed)
        ->assertSuccessful()
        ->assertSee('Pre-Functional Checklists')
        ->assertSee('PFC clean rate');
});
