<?php

use App\Domain\WorkOrderStatus;

it('allows pending to transition to assigned', function () {
    $pending = WorkOrderStatus::Pending;

    expect($pending->canTransitionTo(WorkOrderStatus::Assigned))->toBeTrue()
        ->and($pending->canTransitionTo(WorkOrderStatus::InProgress))->toBeTrue()
        ->and($pending->canTransitionTo(WorkOrderStatus::Cancelled))->toBeTrue();
});

it('prevents verified from transitioning to any state', function () {
    $verified = WorkOrderStatus::Verified;

    expect($verified->allowedTransitions())->toBeEmpty();

    foreach (WorkOrderStatus::cases() as $target) {
        if ($target === WorkOrderStatus::Verified) {
            continue;
        }
        expect($verified->canTransitionTo($target))->toBeFalse(
            "Verified should not transition to {$target->value}"
        );
    }
});

it('returns correct label for each status', function () {
    expect(WorkOrderStatus::Pending->label())->toBe('Pending')
        ->and(WorkOrderStatus::Assigned->label())->toBe('Assigned')
        ->and(WorkOrderStatus::InProgress->label())->toBe('In Progress')
        ->and(WorkOrderStatus::OnHold->label())->toBe('On Hold')
        ->and(WorkOrderStatus::Completed->label())->toBe('Completed')
        ->and(WorkOrderStatus::Verified->label())->toBe('Verified')
        ->and(WorkOrderStatus::Cancelled->label())->toBe('Cancelled');
});

it('returns correct badge classes for each status', function () {
    // Each status should have a bg- and text- class pair
    foreach (WorkOrderStatus::cases() as $status) {
        $classes = $status->badgeClasses();
        expect($classes)->toContain('bg-')
            ->and($classes)->toContain('text-');
    }

    // Spot-check specific statuses
    expect(WorkOrderStatus::Completed->badgeClasses())->toBe('bg-emerald-100 text-emerald-800')
        ->and(WorkOrderStatus::Cancelled->badgeClasses())->toBe('bg-red-100 text-red-800');
});

it('canTransitionTo returns true for valid transitions', function () {
    $transitions = [
        [WorkOrderStatus::Pending, WorkOrderStatus::Assigned],
        [WorkOrderStatus::Assigned, WorkOrderStatus::InProgress],
        [WorkOrderStatus::InProgress, WorkOrderStatus::Completed],
        [WorkOrderStatus::Completed, WorkOrderStatus::Verified],
        [WorkOrderStatus::OnHold, WorkOrderStatus::InProgress],
        [WorkOrderStatus::Cancelled, WorkOrderStatus::Pending],
    ];

    foreach ($transitions as [$from, $to]) {
        expect($from->canTransitionTo($to))->toBeTrue(
            "{$from->value} should be able to transition to {$to->value}"
        );
    }
});

it('canTransitionTo returns false for invalid transitions', function () {
    $invalidTransitions = [
        [WorkOrderStatus::Pending, WorkOrderStatus::Completed],
        [WorkOrderStatus::Pending, WorkOrderStatus::Verified],
        [WorkOrderStatus::Assigned, WorkOrderStatus::Completed],
        [WorkOrderStatus::InProgress, WorkOrderStatus::Assigned],
        [WorkOrderStatus::Completed, WorkOrderStatus::Pending],
        [WorkOrderStatus::Verified, WorkOrderStatus::Pending],
    ];

    foreach ($invalidTransitions as [$from, $to]) {
        expect($from->canTransitionTo($to))->toBeFalse(
            "{$from->value} should NOT be able to transition to {$to->value}"
        );
    }
});

it('enforces that completed can only go to verified or back to in_progress', function () {
    $completed = WorkOrderStatus::Completed;
    $allowed = $completed->allowedTransitions();

    expect($allowed)->toHaveCount(2)
        ->and($allowed)->toContain(WorkOrderStatus::Verified)
        ->and($allowed)->toContain(WorkOrderStatus::InProgress);
});
