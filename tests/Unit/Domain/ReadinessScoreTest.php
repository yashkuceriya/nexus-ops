<?php

use App\Domain\ReadinessScore;

it('calculates perfect readiness when all items complete', function () {
    $score = new ReadinessScore(
        totalIssues: 10,
        openIssues: 0,
        totalTests: 20,
        completedTests: 20,
        totalDocs: 15,
        completedDocs: 15,
    );

    expect($score->calculate())->toBe(100.0);
});

it('calculates zero readiness when nothing is complete', function () {
    $score = new ReadinessScore(
        totalIssues: 10,
        openIssues: 10,
        totalTests: 20,
        completedTests: 0,
        totalDocs: 15,
        completedDocs: 0,
    );

    // Issues: (10-10)/10 * 100 = 0%, weighted 0.4 = 0
    // Tests:  0/20 * 100 = 0%, weighted 0.3 = 0
    // Docs:   0/15 * 100 = 0%, weighted 0.3 = 0
    expect($score->calculate())->toBe(0.0);
});

it('weights issues at 40%, tests at 30%, docs at 30%', function () {
    $score = new ReadinessScore(
        totalIssues: 10,
        openIssues: 2,
        totalTests: 20,
        completedTests: 15,
        totalDocs: 10,
        completedDocs: 8,
    );

    // Issues: (10-2)/10 * 100 = 80, weighted 0.4 = 32
    // Tests:  15/20 * 100 = 75, weighted 0.3 = 22.5
    // Docs:   8/10 * 100 = 80, weighted 0.3 = 24
    // Total = 78.5
    expect($score->calculate())->toBe(78.5)
        ->and($score->issueCompletionPercent())->toBe(80.0)
        ->and($score->testCompletionPercent())->toBe(75.0)
        ->and($score->docCompletionPercent())->toBe(80.0);
});

it('handles zero totals gracefully', function () {
    // When there are zero total items, the component should contribute 100%
    // (no work needed means that category is "done")
    $score = new ReadinessScore(
        totalIssues: 0,
        openIssues: 0,
        totalTests: 0,
        completedTests: 0,
        totalDocs: 0,
        completedDocs: 0,
    );

    expect($score->issueCompletionPercent())->toBe(100.0)
        ->and($score->testCompletionPercent())->toBe(100.0)
        ->and($score->docCompletionPercent())->toBe(100.0)
        ->and($score->calculate())->toBe(100.0);
});

it('returns correct grade for score ranges', function () {
    // A >= 80
    $a = new ReadinessScore(10, 0, 20, 20, 15, 15);
    expect($a->grade())->toBe('A');

    // B >= 60
    $b = new ReadinessScore(10, 2, 20, 15, 10, 8);
    expect($b->calculate())->toBeGreaterThanOrEqual(60.0)
        ->and($b->calculate())->toBeLessThan(80.0)
        ->and($b->grade())->toBe('B');

    // C >= 40
    $c = new ReadinessScore(10, 5, 20, 8, 10, 4);
    expect($c->calculate())->toBeGreaterThanOrEqual(40.0)
        ->and($c->calculate())->toBeLessThan(60.0)
        ->and($c->grade())->toBe('C');

    // D >= 20
    // Issues: (10-6)/10*100=40, w=16. Tests: 5/20*100=25, w=7.5. Docs: 2/10*100=20, w=6. Total=29.5
    $d = new ReadinessScore(10, 6, 20, 5, 10, 2);
    expect($d->calculate())->toBeGreaterThanOrEqual(20.0)
        ->and($d->calculate())->toBeLessThan(40.0)
        ->and($d->grade())->toBe('D');

    // F < 20
    // Issues: (10-9)/10*100=10, w=4. Tests: 2/20*100=10, w=3. Docs: 1/10*100=10, w=3. Total=10
    $f = new ReadinessScore(10, 9, 20, 2, 10, 1);
    expect($f->calculate())->toBeLessThan(20.0)
        ->and($f->grade())->toBe('F');
});

it('returns correct color for score ranges', function () {
    $a = new ReadinessScore(10, 0, 20, 20, 15, 15);
    expect($a->color())->toBe('green');

    $b = new ReadinessScore(10, 2, 20, 15, 10, 8);
    expect($b->color())->toBe('emerald');

    $c = new ReadinessScore(10, 5, 20, 8, 10, 4);
    expect($c->color())->toBe('yellow');

    $d = new ReadinessScore(10, 6, 20, 5, 10, 2);
    expect($d->color())->toBe('orange');

    $f = new ReadinessScore(10, 9, 20, 2, 10, 1);
    expect($f->color())->toBe('red');
});
