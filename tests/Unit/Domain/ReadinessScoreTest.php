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
        fptPassPercent: 100.0,
        fptExecutionsRun: 5,
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
        fptPassPercent: 0.0,
        fptExecutionsRun: 3,
    );

    // All four dimensions at 0%.
    expect($score->calculate())->toBe(0.0);
});

it('weights issues 30%, tests 20%, docs 20%, fpt 30%', function () {
    $score = new ReadinessScore(
        totalIssues: 10,
        openIssues: 2,
        totalTests: 20,
        completedTests: 15,
        totalDocs: 10,
        completedDocs: 8,
        fptPassPercent: 90.0,
        fptExecutionsRun: 5,
    );

    // Issues: (10-2)/10 * 100 = 80, weighted 0.30 = 24
    // Tests:  15/20 * 100     = 75, weighted 0.20 = 15
    // Docs:   8/10 * 100      = 80, weighted 0.20 = 16
    // FPT:    90              = 90, weighted 0.30 = 27
    // Total = 82
    expect($score->calculate())->toBe(82.0)
        ->and($score->issueCompletionPercent())->toBe(80.0)
        ->and($score->testCompletionPercent())->toBe(75.0)
        ->and($score->docCompletionPercent())->toBe(80.0)
        ->and($score->fptPassPercent)->toBe(90.0);
});

it('handles zero totals gracefully', function () {
    // When there are zero total items, the component should contribute 100%
    // (no work needed means that category is "done"). FPT defaults to 100
    // when no executions have been run yet so early-stage projects aren't
    // unfairly punished.
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
        ->and($score->fptPassPercent)->toBe(100.0)
        ->and($score->calculate())->toBe(100.0);
});

it('returns correct grade for score ranges', function () {
    // A >= 80 — everything perfect.
    $a = new ReadinessScore(10, 0, 20, 20, 15, 15, 100.0, 5);
    expect($a->grade())->toBe('A');

    // B >= 60 — FPT at 60%, others ~80.
    $b = new ReadinessScore(10, 2, 20, 15, 10, 8, 60.0, 5);
    expect($b->calculate())->toBeGreaterThanOrEqual(60.0)
        ->and($b->calculate())->toBeLessThan(80.0)
        ->and($b->grade())->toBe('B');

    // C >= 40 — middling FPT, half-done closeout.
    $c = new ReadinessScore(10, 5, 20, 8, 10, 4, 40.0, 4);
    expect($c->calculate())->toBeGreaterThanOrEqual(40.0)
        ->and($c->calculate())->toBeLessThan(60.0)
        ->and($c->grade())->toBe('C');

    // D >= 20 — most items open, FPT low.
    $d = new ReadinessScore(10, 8, 20, 5, 10, 2, 20.0, 3);
    expect($d->calculate())->toBeGreaterThanOrEqual(20.0)
        ->and($d->calculate())->toBeLessThan(40.0)
        ->and($d->grade())->toBe('D');

    // F < 20 — nearly nothing done.
    $f = new ReadinessScore(10, 9, 20, 2, 10, 1, 5.0, 2);
    expect($f->calculate())->toBeLessThan(20.0)
        ->and($f->grade())->toBe('F');
});

it('returns correct color for score ranges', function () {
    $a = new ReadinessScore(10, 0, 20, 20, 15, 15, 100.0, 5);
    expect($a->color())->toBe('green');

    $b = new ReadinessScore(10, 2, 20, 15, 10, 8, 60.0, 5);
    expect($b->color())->toBe('emerald');

    $c = new ReadinessScore(10, 5, 20, 8, 10, 4, 40.0, 4);
    expect($c->color())->toBe('yellow');

    $d = new ReadinessScore(10, 8, 20, 5, 10, 2, 20.0, 3);
    expect($d->color())->toBe('orange');

    $f = new ReadinessScore(10, 9, 20, 2, 10, 1, 5.0, 2);
    expect($f->color())->toBe('red');
});

it('gives passed executions 0.9 credit and witnessed 1.0 credit in fromProject', function () {
    // Unit test for the model-free helper. The integration path with a real
    // project is covered in ProjectTest. Here we just verify construction
    // math when callers build the object directly.
    $onlyPassedNoWitness = new ReadinessScore(0, 0, 0, 0, 0, 0, 90.0, 5);
    expect($onlyPassedNoWitness->calculate())->toBe(97.0); // 100*0.7 + 90*0.3

    $allWitnessed = new ReadinessScore(0, 0, 0, 0, 0, 0, 100.0, 5);
    expect($allWitnessed->calculate())->toBe(100.0);
});
