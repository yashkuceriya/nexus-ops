<?php

use App\Models\AuditLog;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\WorkOrder\WorkOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();

    $this->admin = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'role' => 'admin',
    ]);

    $this->tech = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'role' => 'technician',
    ]);

    $this->project = Project::factory()->create([
        'tenant_id' => $this->tenant->id,
    ]);

    $this->service = app(WorkOrderService::class);

    $this->actingAs($this->admin);

    // Prevent broadcast events from triggering tenant-aware job checks
    Event::fake([\App\Events\WorkOrderStatusChanged::class]);
});

it('completes full work order lifecycle: create -> assign -> start -> complete -> verify', function () {
    // Create via service (mimics API create flow)
    $wo = $this->service->create(
        tenantId: $this->tenant->id,
        createdBy: $this->admin->id,
        data: [
            'project_id' => $this->project->id,
            'title' => 'Fix chiller compressor',
            'priority' => 'high',
            'type' => 'corrective',
        ],
    );

    expect($wo->status)->toBe('pending')
        ->and($wo->wo_number)->toStartWith('WO-')
        ->and($wo->sla_hours)->toBe(8)
        ->and($wo->sla_deadline)->not->toBeNull();

    // Assign to technician
    $wo = $this->service->assignWorkOrder($wo, $this->tech);
    expect($wo->assigned_to)->toBe($this->tech->id);

    // Transition: pending -> in_progress
    $this->actingAs($this->admin);
    $wo = $this->service->updateStatus($wo, 'in_progress');
    expect($wo->status)->toBe('in_progress')
        ->and($wo->started_at)->not->toBeNull();

    // Transition: in_progress -> completed
    $wo = $this->service->updateStatus($wo, 'completed', 'Compressor replaced successfully');
    expect($wo->status)->toBe('completed')
        ->and($wo->completed_at)->not->toBeNull()
        ->and($wo->resolution_notes)->toBe('Compressor replaced successfully');

    // Transition: completed -> verified
    $wo = $this->service->updateStatus($wo, 'verified');
    expect($wo->status)->toBe('verified')
        ->and($wo->verified_at)->not->toBeNull();

    // Verify audit log entries were created for each transition
    $auditEntries = AuditLog::where('auditable_type', WorkOrder::class)
        ->where('auditable_id', $wo->id)
        ->pluck('action')
        ->toArray();

    expect($auditEntries)->toContain('work_order_created')
        ->and($auditEntries)->toContain('work_order_assigned')
        ->and($auditEntries)->toContain('work_order_status_changed');

    // Verify we have status change audit entries for each transition
    $statusChanges = AuditLog::where('auditable_type', WorkOrder::class)
        ->where('auditable_id', $wo->id)
        ->where('action', 'work_order_status_changed')
        ->get();

    expect($statusChanges)->toHaveCount(3); // in_progress, completed, verified
});

it('prevents invalid status transitions', function () {
    $wo = WorkOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'project_id' => $this->project->id,
        'status' => 'verified',
    ]);

    // Verified is a terminal state -- nothing should be allowed
    expect(fn () => $this->service->updateStatus($wo, 'in_progress'))
        ->toThrow(InvalidArgumentException::class);
});

it('calculates SLA deadline based on priority', function () {
    $now = now();

    $wo = $this->service->create(
        tenantId: $this->tenant->id,
        createdBy: $this->admin->id,
        data: [
            'project_id' => $this->project->id,
            'title' => 'Emergency: gas leak',
            'priority' => 'emergency',
            'type' => 'corrective',
        ],
    );

    // Emergency priority = 2 hours SLA = 120 minutes
    $minutesDiff = abs($wo->sla_deadline->diffInMinutes($now));
    expect($wo->sla_hours)->toBe(2)
        ->and($minutesDiff)->toBeLessThanOrEqual(121)
        ->and($minutesDiff)->toBeGreaterThanOrEqual(119);
});

it('marks SLA as breached when deadline passes', function () {
    $wo = WorkOrder::factory()->overdue()->create([
        'tenant_id' => $this->tenant->id,
        'project_id' => $this->project->id,
        'status' => 'in_progress',
    ]);

    expect($wo->isSlaBreached())->toBeTrue();
});

it('does not mark SLA as breached when completed before deadline', function () {
    $wo = WorkOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'project_id' => $this->project->id,
        'status' => 'completed',
        'sla_deadline' => now()->addHours(5),
        'completed_at' => now()->subHour(),
    ]);

    expect($wo->isSlaBreached())->toBeFalse();
});

it('sets sla_breached flag when completing an overdue work order', function () {
    $this->actingAs($this->admin);

    $wo = WorkOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'project_id' => $this->project->id,
        'status' => 'in_progress',
        'started_at' => now()->subHours(5),
        'sla_deadline' => now()->subHours(1),
    ]);

    $wo = $this->service->updateStatus($wo, 'completed');

    expect($wo->status)->toBe('completed')
        ->and($wo->sla_breached)->toBeTrue();
});

it('prevents skipping states in the lifecycle', function () {
    $wo = WorkOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'project_id' => $this->project->id,
        'status' => 'pending',
    ]);

    // Cannot jump from pending directly to completed
    expect(fn () => $this->service->updateStatus($wo, 'completed'))
        ->toThrow(InvalidArgumentException::class);

    // Cannot jump from pending directly to verified
    expect(fn () => $this->service->updateStatus($wo, 'verified'))
        ->toThrow(InvalidArgumentException::class);
});

it('generates unique work order numbers', function () {
    $numbers = collect();
    for ($i = 0; $i < 5; $i++) {
        $wo = WorkOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
        ]);
        $numbers->push($wo->wo_number);
    }

    expect($numbers->unique()->count())->toBe(5);
});

it('prevents transitioning from cancelled to completed', function () {
    $wo = WorkOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'project_id' => $this->project->id,
        'status' => 'cancelled',
    ]);

    // Cancelled can only go back to pending
    expect(fn () => $this->service->updateStatus($wo, 'completed'))
        ->toThrow(InvalidArgumentException::class);

    // But can reopen
    $this->actingAs($this->admin);
    $wo = $this->service->updateStatus($wo, 'pending');
    expect($wo->status)->toBe('pending');
});
