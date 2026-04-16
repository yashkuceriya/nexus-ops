<?php

declare(strict_types=1);

namespace App\Domain;

use App\Models\Project;
use App\Models\TestExecution;

/**
 * Project handover-readiness calculation.
 *
 * Weights four dimensions:
 *   - Issues closed      (0.30)
 *   - Closeout tests     (0.20)
 *   - Documents uploaded (0.20)
 *   - FPT pass rate      (0.30)
 *
 * The FPT dimension reads live from the `test_executions` table so it stays
 * honest even when the cached project counters drift. It counts the *latest*
 * execution per (asset × script) pair and scores:
 *   - passed + witnessed = 1.0 (full credit)
 *   - passed (no witness) = 0.9
 *   - failed / aborted = 0.0
 *   - in_progress / not run = partial by step progress
 */
final class ReadinessScore
{
    private const float WEIGHT_ISSUES = 0.30;

    private const float WEIGHT_TESTS = 0.20;

    private const float WEIGHT_DOCS = 0.20;

    private const float WEIGHT_FPT = 0.30;

    public function __construct(
        public readonly int $totalIssues,
        public readonly int $openIssues,
        public readonly int $totalTests,
        public readonly int $completedTests,
        public readonly int $totalDocs,
        public readonly int $completedDocs,
        public readonly float $fptPassPercent = 100.0,
        public readonly int $fptExecutionsRun = 0,
    ) {}

    public static function fromProject(Project $project): self
    {
        [$fptPercent, $fptRun] = self::fptStatsFor($project);

        return new self(
            totalIssues: (int) $project->total_issues,
            openIssues: (int) $project->open_issues,
            totalTests: (int) $project->total_tests,
            completedTests: (int) $project->completed_tests,
            totalDocs: (int) $project->total_closeout_docs,
            completedDocs: (int) $project->completed_closeout_docs,
            fptPassPercent: $fptPercent,
            fptExecutionsRun: $fptRun,
        );
    }

    public function calculate(): float
    {
        return round(
            ($this->issueCompletionPercent() * self::WEIGHT_ISSUES)
            + ($this->testCompletionPercent() * self::WEIGHT_TESTS)
            + ($this->docCompletionPercent() * self::WEIGHT_DOCS)
            + ($this->fptPassPercent * self::WEIGHT_FPT),
            2,
        );
    }

    public function issueCompletionPercent(): float
    {
        if ($this->totalIssues <= 0) {
            return 100.0;
        }

        return (($this->totalIssues - $this->openIssues) / $this->totalIssues) * 100;
    }

    public function testCompletionPercent(): float
    {
        if ($this->totalTests <= 0) {
            return 100.0;
        }

        return ($this->completedTests / $this->totalTests) * 100;
    }

    public function docCompletionPercent(): float
    {
        if ($this->totalDocs <= 0) {
            return 100.0;
        }

        return ($this->completedDocs / $this->totalDocs) * 100;
    }

    public function grade(): string
    {
        $score = $this->calculate();

        return match (true) {
            $score >= 80 => 'A',
            $score >= 60 => 'B',
            $score >= 40 => 'C',
            $score >= 20 => 'D',
            default => 'F',
        };
    }

    public function color(): string
    {
        return match ($this->grade()) {
            'A' => 'green',
            'B' => 'emerald',
            'C' => 'yellow',
            'D' => 'orange',
            default => 'red',
        };
    }

    /**
     * @return array{0: float, 1: int} [passPercent, executionsConsidered]
     */
    private static function fptStatsFor(Project $project): array
    {
        // Consider only the latest execution per (asset, script) pair.
        $latestIds = TestExecution::query()
            ->where('tenant_id', $project->tenant_id)
            ->where('project_id', $project->id)
            ->selectRaw('MAX(id) as max_id')
            ->groupBy('asset_id', 'test_script_id')
            ->pluck('max_id');

        if ($latestIds->isEmpty()) {
            // No FPTs run → neutral (100) so we don't punish early-stage projects.
            return [100.0, 0];
        }

        $executions = TestExecution::query()
            ->where('tenant_id', $project->tenant_id)
            ->whereIn('id', $latestIds)
            ->get(['status', 'witness_signed_at', 'pass_count', 'fail_count', 'total_count']);

        $count = $executions->count();
        $score = 0.0;

        foreach ($executions as $e) {
            $score += match ($e->status) {
                TestExecution::STATUS_PASSED => $e->witness_signed_at ? 1.0 : 0.9,
                TestExecution::STATUS_FAILED,
                TestExecution::STATUS_ABORTED => 0.0,
                TestExecution::STATUS_ON_HOLD => 0.3,
                TestExecution::STATUS_IN_PROGRESS => $e->total_count > 0
                    ? ($e->pass_count / $e->total_count) * 0.5
                    : 0.0,
                default => 0.0,
            };
        }

        return [round(($score / $count) * 100, 2), $count];
    }
}
