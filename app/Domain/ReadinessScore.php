<?php

declare(strict_types=1);

namespace App\Domain;

use App\Models\Project;

final class ReadinessScore
{
    private const float WEIGHT_ISSUES = 0.4;
    private const float WEIGHT_TESTS = 0.3;
    private const float WEIGHT_DOCS = 0.3;

    public function __construct(
        public readonly int $totalIssues,
        public readonly int $openIssues,
        public readonly int $totalTests,
        public readonly int $completedTests,
        public readonly int $totalDocs,
        public readonly int $completedDocs,
    ) {}

    public static function fromProject(Project $project): self
    {
        return new self(
            totalIssues: (int) $project->total_issues,
            openIssues: (int) $project->open_issues,
            totalTests: (int) $project->total_tests,
            completedTests: (int) $project->completed_tests,
            totalDocs: (int) $project->total_closeout_docs,
            completedDocs: (int) $project->completed_closeout_docs,
        );
    }

    public function calculate(): float
    {
        return round(
            ($this->issueCompletionPercent() * self::WEIGHT_ISSUES)
            + ($this->testCompletionPercent() * self::WEIGHT_TESTS)
            + ($this->docCompletionPercent() * self::WEIGHT_DOCS),
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
            default      => 'F',
        };
    }

    public function color(): string
    {
        return match ($this->grade()) {
            'A'     => 'green',
            'B'     => 'emerald',
            'C'     => 'yellow',
            'D'     => 'orange',
            default => 'red',
        };
    }
}
