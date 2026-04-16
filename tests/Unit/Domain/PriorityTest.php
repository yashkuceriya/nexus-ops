<?php

use App\Domain\Priority;
use Carbon\Carbon;

it('returns 2 hours SLA for emergency priority', function () {
    expect(Priority::Emergency->slaHours())->toBe(2);
});

it('returns correct SLA for all priorities', function () {
    expect(Priority::Emergency->slaHours())->toBe(2)
        ->and(Priority::Critical->slaHours())->toBe(4)
        ->and(Priority::High->slaHours())->toBe(8)
        ->and(Priority::Medium->slaHours())->toBe(24)
        ->and(Priority::Low->slaHours())->toBe(48);
});

it('calculates SLA deadline from given time', function () {
    $start = Carbon::parse('2026-04-14 10:00:00');

    $emergencyDeadline = Priority::Emergency->slaDeadlineFrom($start);
    expect($emergencyDeadline->toDateTimeString())->toBe('2026-04-14 12:00:00');

    $mediumDeadline = Priority::Medium->slaDeadlineFrom($start);
    expect($mediumDeadline->toDateTimeString())->toBe('2026-04-15 10:00:00');
});

it('does not mutate the original start time when calculating deadline', function () {
    $start = Carbon::parse('2026-04-14 10:00:00');
    $original = $start->toDateTimeString();

    Priority::Emergency->slaDeadlineFrom($start);

    expect($start->toDateTimeString())->toBe($original);
});

it('SLA hours are strictly ordered from most to least urgent', function () {
    $priorities = [
        Priority::Emergency,
        Priority::Critical,
        Priority::High,
        Priority::Medium,
        Priority::Low,
    ];

    for ($i = 0; $i < count($priorities) - 1; $i++) {
        expect($priorities[$i]->slaHours())->toBeLessThan(
            $priorities[$i + 1]->slaHours(),
            "{$priorities[$i]->value} SLA should be shorter than {$priorities[$i + 1]->value}"
        );
    }
});

it('can be constructed from string values', function () {
    expect(Priority::from('emergency'))->toBe(Priority::Emergency)
        ->and(Priority::from('low'))->toBe(Priority::Low)
        ->and(Priority::tryFrom('invalid'))->toBeNull();
});
