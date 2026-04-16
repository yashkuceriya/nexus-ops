<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Asset;
use App\Models\ChecklistCompletion;
use App\Models\ChecklistTemplate;
use App\Models\Project;
use App\Services\Checklist\PreFunctionalChecklistService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Project-level Pre-Functional Checklist board: assets down the side,
 * PFC templates across the top, cells showing readiness status.
 *
 * Mirrors the Cx Test Matrix pattern so operators move through PFC →
 * FPT in a consistent mental model. Clicking into a cell starts or
 * resumes the checklist runner for that asset+template combination.
 */
class PreFunctionalChecklistBoard extends Component
{
    public Project $project;

    #[Url(history: true)]
    public ?int $activeTemplateId = null;

    #[Url(history: true)]
    public ?int $activeAssetId = null;

    #[Url(history: true)]
    public bool $onlyGaps = false;

    /** @var array<int, array<string, string|int|null>> */
    public array $responseDraft = [];

    public ?string $sessionNotice = null;

    public function mount(int $projectId): void
    {
        $this->project = Project::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($projectId);
    }

    #[Computed]
    public function templates()
    {
        return ChecklistTemplate::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->pfc()
            ->where('is_active', true)
            ->orderBy('cx_level')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function assets()
    {
        return Asset::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('project_id', $this->project->id)
            ->orderBy('system_type')
            ->orderBy('name')
            ->get();
    }

    /**
     * Latest completion for every (asset × template) pair so the matrix
     * can be rendered in a single query instead of N×M lookups.
     *
     * @return array<int, array<int, ChecklistCompletion>>
     */
    #[Computed]
    public function cells(): array
    {
        $completions = ChecklistCompletion::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('project_id', $this->project->id)
            ->where('type', ChecklistTemplate::TYPE_PFC)
            ->orderBy('updated_at', 'desc')
            ->get();

        $cells = [];
        foreach ($completions as $c) {
            $cells[$c->asset_id][$c->checklist_template_id] ??= $c;
        }

        return $cells;
    }

    /**
     * When `onlyGaps` is on, hide assets with a fully-clean completion
     * for every published PFC template.
     */
    #[Computed]
    public function visibleAssets()
    {
        if (! $this->onlyGaps) {
            return $this->assets;
        }

        $cells = $this->cells;
        $templateCount = $this->templates->count();

        return $this->assets->filter(function (Asset $a) use ($cells, $templateCount) {
            $completions = $cells[$a->id] ?? [];
            if (count($completions) < $templateCount) {
                return true;
            }
            foreach ($completions as $completion) {
                if (! $completion->isCleanPfc()) {
                    return true;
                }
            }

            return false;
        })->values();
    }

    /**
     * Tailwind-ready chip config for a given cell — tone, label, and the
     * action to trigger when the user clicks.
     *
     * @return array{status: string, label: string, tone: string, action: string}
     */
    public function cellConfig(?ChecklistCompletion $completion): array
    {
        if ($completion === null) {
            return ['status' => 'not_started', 'label' => 'Start', 'tone' => 'border-dashed border-gray-300 text-gray-500 hover:bg-gray-50', 'action' => 'start'];
        }

        return match ($completion->status) {
            ChecklistCompletion::STATUS_COMPLETED => $completion->fail_count > 0
                ? ['status' => 'completed', 'label' => '✓ w/ deficiencies', 'tone' => 'bg-amber-100 text-amber-800 border-amber-200', 'action' => 'resume']
                : ['status' => 'completed', 'label' => '✓ Clean', 'tone' => 'bg-emerald-100 text-emerald-800 border-emerald-200', 'action' => 'resume'],
            ChecklistCompletion::STATUS_FAILED => ['status' => 'failed', 'label' => '✗ Failed', 'tone' => 'bg-red-100 text-red-800 border-red-200', 'action' => 'resume'],
            ChecklistCompletion::STATUS_IN_PROGRESS => ['status' => 'in_progress', 'label' => 'In progress', 'tone' => 'bg-indigo-100 text-indigo-800 border-indigo-200', 'action' => 'resume'],
            default => ['status' => $completion->status, 'label' => ucfirst($completion->status), 'tone' => 'bg-gray-100 text-gray-800 border-gray-200', 'action' => 'resume'],
        };
    }

    public function openRunner(int $assetId, int $templateId): void
    {
        $this->activeAssetId = $assetId;
        $this->activeTemplateId = $templateId;
        $this->responseDraft = [];
        $this->sessionNotice = null;

        $completion = $this->activeCompletion();
        if ($completion === null) {
            $template = ChecklistTemplate::findOrFail($templateId);
            $asset = Asset::findOrFail($assetId);
            app(PreFunctionalChecklistService::class)->start($template, $asset, auth()->user());
        }

        $this->hydrateDraftFromCompletion();
    }

    public function closeRunner(): void
    {
        $this->activeAssetId = null;
        $this->activeTemplateId = null;
        $this->responseDraft = [];
        $this->sessionNotice = null;
    }

    /**
     * Explicit Tailwind classes so the Vite build includes every variant
     * (dynamic `bg-{{ $tone }}` strings are stripped by JIT).
     */
    public function pfcResponseButtonClass(string $key, bool $active): string
    {
        if (! $active) {
            return 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50';
        }

        return match ($key) {
            'pass' => 'bg-emerald-600 text-white border-emerald-700',
            'fail' => 'bg-red-600 text-white border-red-700',
            'na' => 'bg-gray-600 text-white border-gray-700',
            default => 'bg-white text-gray-700 border-gray-300',
        };
    }

    /**
     * Queue the user's response for a step on the in-memory draft. We
     * don't persist per-keystroke — the runner flushes on advance or
     * on "Complete".
     */
    public function setResponse(int $stepOrder, string $status, ?string $notes = null): void
    {
        $this->responseDraft[$stepOrder] = [
            'status' => $status,
            'notes' => $notes,
        ];
    }

    /**
     * Persist all queued responses, optionally completing the PFC.
     */
    public function saveRunner(bool $complete = false): void
    {
        $completion = $this->activeCompletion();
        if ($completion === null) {
            return;
        }

        $service = app(PreFunctionalChecklistService::class);
        foreach ($this->responseDraft as $stepOrder => $r) {
            $service->recordResponse(
                $completion,
                (int) $stepOrder,
                $r['status'],
                null,
                $r['notes'] ?? null,
            );
        }

        if ($complete) {
            $completed = $service->complete($completion->refresh(), auth()->user());
            $this->sessionNotice = $completed->status === ChecklistCompletion::STATUS_COMPLETED
                ? 'PFC completed cleanly — asset is clear for FPT.'
                : 'PFC completed with deficiencies — '.$completed->fail_count.' item(s) opened as issues.';
        } else {
            $this->sessionNotice = 'Progress saved.';
        }

        $this->hydrateDraftFromCompletion();
        unset($this->cells, $this->visibleAssets);
    }

    private function activeCompletion(): ?ChecklistCompletion
    {
        if ($this->activeAssetId === null || $this->activeTemplateId === null) {
            return null;
        }

        return ChecklistCompletion::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('project_id', $this->project->id)
            ->where('asset_id', $this->activeAssetId)
            ->where('checklist_template_id', $this->activeTemplateId)
            ->where('type', ChecklistTemplate::TYPE_PFC)
            ->latest('updated_at')
            ->first();
    }

    public function getRunnerTemplateProperty(): ?ChecklistTemplate
    {
        return $this->activeTemplateId
            ? ChecklistTemplate::find($this->activeTemplateId)
            : null;
    }

    public function getRunnerCompletionProperty(): ?ChecklistCompletion
    {
        return $this->activeCompletion()?->loadMissing('template');
    }

    public function getRunnerAssetProperty(): ?Asset
    {
        return $this->activeAssetId ? Asset::find($this->activeAssetId) : null;
    }

    private function hydrateDraftFromCompletion(): void
    {
        $completion = $this->activeCompletion();
        if ($completion === null) {
            return;
        }

        $this->responseDraft = collect($completion->responses ?? [])
            ->mapWithKeys(fn (array $r) => [
                (int) ($r['step_order'] ?? 0) => [
                    'status' => $r['status'] ?? null,
                    'notes' => $r['notes'] ?? null,
                ],
            ])
            ->all();
    }

    public function render()
    {
        return view('livewire.pre-functional-checklist-board')
            ->layout('layouts.app', [
                'title' => 'Pre-Functional Checklists',
                'subtitle' => $this->project->name,
            ]);
    }
}
