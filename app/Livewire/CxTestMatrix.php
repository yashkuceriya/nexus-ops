<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Asset;
use App\Models\Project;
use App\Models\TestExecution;
use App\Models\TestScript;
use App\Services\TestExecution\TestExecutionService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The Commissioning Test Matrix — the iconic Cx deliverable.
 *
 * Rows are assets on a project, columns are published test scripts relevant
 * to those assets, and each cell shows the most recent execution status
 * (passed / failed / in progress / retest / not run). Clicking a cell takes
 * you directly into the execution runner or to start a new run.
 *
 * This is the view a commissioning authority walks into a handover meeting
 * with — "here's every test, every asset, every outcome, at a glance".
 */
class CxTestMatrix extends Component
{
    public Project $project;

    #[Url(history: true)]
    public string $levelFilter = '';

    #[Url(history: true)]
    public string $systemFilter = '';

    #[Url(history: true)]
    public bool $onlyGaps = false;

    public function mount(int $projectId): void
    {
        $tenantId = auth()->user()->tenant_id;

        $this->project = Project::where('tenant_id', $tenantId)
            ->with('tenant:id,name')
            ->findOrFail($projectId);
    }

    public function getAssetsProperty()
    {
        return Asset::where('tenant_id', $this->project->tenant_id)
            ->where('project_id', $this->project->id)
            ->when($this->systemFilter !== '', fn ($q) => $q->where('category', $this->systemFilter))
            ->orderBy('system_type')
            ->orderBy('name')
            ->get(['id', 'name', 'asset_tag', 'category', 'system_type']);
    }

    /**
     * Optionally collapse the asset list to only rows that still need work
     * (gaps, failures, or in-flight tests). Keeps Cx walkthroughs focused.
     */
    public function getVisibleAssetsProperty()
    {
        $assets = $this->assets;

        if (! $this->onlyGaps) {
            return $assets;
        }

        $cells = $this->cells;
        $scripts = $this->scripts;

        return $assets->filter(function ($asset) use ($cells, $scripts) {
            foreach ($scripts as $script) {
                $status = $cells[$asset->id][$script->id]['status'] ?? 'not_run';
                if (in_array($status, ['not_run', 'failed', 'in_progress', 'aborted', 'on_hold'], true)) {
                    return true;
                }
            }

            return false;
        })->values();
    }

    public function getScriptsProperty()
    {
        $tenantId = $this->project->tenant_id;

        return TestScript::availableTo($tenantId)
            ->published()
            ->when($this->levelFilter !== '', fn ($q) => $q->where('cx_level', $this->levelFilter))
            ->orderBy('cx_level')
            ->orderBy('name')
            ->get(['id', 'name', 'version', 'system_type', 'cx_level', 'is_system']);
    }

    /**
     * @return array<int, array<int, array<string, mixed>>>
     *                                                      Nested map keyed by [asset_id][script_id] → cell descriptor.
     */
    public function getCellsProperty(): array
    {
        $assets = $this->assets;
        $scripts = $this->scripts;

        if ($assets->isEmpty() || $scripts->isEmpty()) {
            return [];
        }

        // Pull the latest execution per (asset, script) pair for this project.
        $executions = TestExecution::where('tenant_id', $this->project->tenant_id)
            ->where('project_id', $this->project->id)
            ->whereIn('asset_id', $assets->pluck('id'))
            ->whereIn('test_script_id', $scripts->pluck('id'))
            ->orderByDesc('started_at')
            ->get([
                'id', 'asset_id', 'test_script_id', 'status',
                'pass_count', 'fail_count', 'total_count',
                'started_at', 'completed_at', 'parent_execution_id',
                'witness_signed_at',
            ]);

        $matrix = [];
        foreach ($assets as $asset) {
            foreach ($scripts as $script) {
                $match = $executions->first(
                    fn ($e) => $e->asset_id === $asset->id && $e->test_script_id === $script->id,
                );

                if ($match === null) {
                    $matrix[$asset->id][$script->id] = [
                        'status' => 'not_run',
                        'execution_id' => null,
                        'label' => '—',
                        'retested' => false,
                        'witnessed' => false,
                        'started_at' => null,
                    ];

                    continue;
                }

                $allRetests = $executions->where('asset_id', $asset->id)
                    ->where('test_script_id', $script->id)
                    ->whereNotNull('parent_execution_id');

                $matrix[$asset->id][$script->id] = [
                    'status' => $match->status,
                    'execution_id' => $match->id,
                    'label' => $this->cellLabel($match->status),
                    'retested' => $allRetests->isNotEmpty(),
                    'witnessed' => $match->witness_signed_at !== null,
                    'started_at' => $match->started_at,
                ];
            }
        }

        return $matrix;
    }

    /**
     * @return array<string, int>
     */
    public function getSummaryProperty(): array
    {
        $cells = $this->cells;
        $counts = ['passed' => 0, 'failed' => 0, 'in_progress' => 0, 'not_run' => 0, 'witnessed' => 0, 'total' => 0];

        foreach ($cells as $row) {
            foreach ($row as $cell) {
                $counts['total']++;
                if (isset($counts[$cell['status']])) {
                    $counts[$cell['status']]++;
                }
                if (! empty($cell['witnessed'])) {
                    $counts['witnessed']++;
                }
            }
        }

        return $counts;
    }

    public function getCategoriesProperty()
    {
        return Asset::where('tenant_id', $this->project->tenant_id)
            ->where('project_id', $this->project->id)
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');
    }

    public function clearFilters(): void
    {
        $this->levelFilter = '';
        $this->systemFilter = '';
        $this->onlyGaps = false;
    }

    /**
     * Launch a fresh execution directly from an empty matrix cell and deep-link
     * the tester into the runner — removes the "where do I start?" friction
     * that usually plagues Cx handovers.
     */
    public function startExecution(int $assetId, int $scriptId, TestExecutionService $service)
    {
        $user = auth()->user();
        $tenantId = $this->project->tenant_id;

        $asset = Asset::where('tenant_id', $tenantId)
            ->where('project_id', $this->project->id)
            ->findOrFail($assetId);

        $script = TestScript::availableTo($tenantId)
            ->published()
            ->findOrFail($scriptId);

        $execution = $service->start(script: $script, asset: $asset, startedBy: $user);

        return redirect()->route('fpt.run', $execution->id);
    }

    /**
     * Chain a retest from a failed execution. This preserves the parent link
     * so the matrix can show a "RT" badge and the full history is traceable.
     */
    public function retest(int $executionId, TestExecutionService $service)
    {
        $user = auth()->user();

        $failed = TestExecution::where('tenant_id', $this->project->tenant_id)
            ->where('project_id', $this->project->id)
            ->findOrFail($executionId);

        $retest = $service->retest($failed, $user);

        return redirect()->route('fpt.run', $retest->id);
    }

    private function cellLabel(string $status): string
    {
        return match ($status) {
            TestExecution::STATUS_PASSED => 'Passed',
            TestExecution::STATUS_FAILED => 'Failed',
            TestExecution::STATUS_IN_PROGRESS => 'Running',
            TestExecution::STATUS_ABORTED => 'Aborted',
            TestExecution::STATUS_ON_HOLD => 'On Hold',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    /**
     * Visual configuration for a cell given its status. Extracted here so the
     * Blade template doesn't need an inline @php block (which Livewire v4's
     * extended Blade compiler doesn't always parse cleanly when the body
     * contains a match expression).
     *
     * @return array{bg: string, text: string, icon: string}
     */
    public function cellConfig(string $status): array
    {
        return match ($status) {
            'passed' => ['bg' => 'bg-emerald-100 hover:bg-emerald-200', 'text' => 'text-emerald-800', 'icon' => '✓'],
            'failed' => ['bg' => 'bg-red-100 hover:bg-red-200', 'text' => 'text-red-800', 'icon' => '✗'],
            'in_progress' => ['bg' => 'bg-blue-100 hover:bg-blue-200', 'text' => 'text-blue-800', 'icon' => '●'],
            'aborted', 'on_hold' => ['bg' => 'bg-amber-100 hover:bg-amber-200', 'text' => 'text-amber-800', 'icon' => '!'],
            default => ['bg' => 'bg-gray-50 hover:bg-gray-100', 'text' => 'text-gray-400', 'icon' => '—'],
        };
    }

    /**
     * Stream the matrix as CSV — a perennial commissioning-manager request
     * because owner reps, general contractors, and BIM managers each want to
     * slice the grid in their own spreadsheet. The export respects the
     * currently applied level / system / gap filters.
     */
    public function exportCsv(): StreamedResponse
    {
        $assets = $this->visibleAssets;
        $scripts = $this->scripts;
        $cells = $this->cells;

        $filename = sprintf(
            'cx-matrix-%s-%s.csv',
            preg_replace('/[^a-z0-9]+/i', '-', strtolower($this->project->name ?? 'project')),
            now()->format('Y-m-d'),
        );

        return response()->streamDownload(function () use ($assets, $scripts, $cells): void {
            $out = fopen('php://output', 'wb');

            $header = ['Asset', 'Asset Tag', 'System Type', 'Category'];
            foreach ($scripts as $script) {
                $header[] = sprintf('%s (v%s, %s)', $script->name, $script->version, $script->cx_level ?: 'L?');
            }
            fputcsv($out, $header);

            foreach ($assets as $asset) {
                $row = [
                    $asset->name,
                    $asset->asset_tag,
                    $asset->system_type,
                    $asset->category,
                ];

                foreach ($scripts as $script) {
                    $cell = $cells[$asset->id][$script->id] ?? null;
                    if ($cell === null) {
                        $row[] = 'not_run';

                        continue;
                    }

                    $status = $cell['status'];
                    $extras = [];
                    if (! empty($cell['witnessed'])) {
                        $extras[] = 'witnessed';
                    }
                    if (! empty($cell['retested'])) {
                        $extras[] = 'retested';
                    }

                    $row[] = $extras === []
                        ? $status
                        : $status.' ['.implode(',', $extras).']';
                }

                fputcsv($out, $row);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function render()
    {
        return view('livewire.cx-test-matrix');
    }
}
