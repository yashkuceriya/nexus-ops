<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\LessonLearned;
use App\Models\Project;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Browse, filter, and create organisational lessons learned.
 *
 * Acts as a cross-project knowledge base: after any significant issue the
 * team captures the root cause + preventive actions here so future projects
 * don't repeat the same mistake.
 */
class LessonsLearnedList extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $category = '';

    #[Url(history: true)]
    public string $severity = '';

    #[Url(history: true)]
    public ?int $projectFilter = null;

    public bool $showCreateForm = false;

    public string $title = '';

    public string $newCategory = LessonLearned::CATEGORY_COMMISSIONING;

    public string $newSeverity = 'medium';

    public ?int $newProjectId = null;

    public ?int $newIssueId = null;

    public string $problemSummary = '';

    public string $rootCause = '';

    public string $correctiveAction = '';

    public string $preventiveAction = '';

    public string $recommendation = '';

    public string $tagsInput = '';

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'newCategory' => ['required', 'string'],
            'newSeverity' => ['required', 'string', 'in:low,medium,high,critical'],
            'newProjectId' => ['nullable', 'integer'],
            'newIssueId' => ['nullable', 'integer'],
            'problemSummary' => ['required', 'string', 'min:10'],
            'rootCause' => ['required', 'string', 'min:10'],
            'correctiveAction' => ['required', 'string', 'min:10'],
            'preventiveAction' => ['nullable', 'string'],
            'recommendation' => ['nullable', 'string'],
            'tagsInput' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function updating(): void
    {
        $this->resetPage();
    }

    public function getLessonsProperty()
    {
        $tenantId = auth()->user()->tenant_id;

        return LessonLearned::query()
            ->where('tenant_id', $tenantId)
            ->where('is_published', true)
            ->when($this->search !== '', function ($q): void {
                $q->where(function ($inner): void {
                    $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $this->search).'%';
                    $inner->where('title', 'like', $term)
                        ->orWhere('problem_summary', 'like', $term)
                        ->orWhere('root_cause', 'like', $term)
                        ->orWhere('corrective_action', 'like', $term);
                });
            })
            ->when($this->category !== '', fn ($q) => $q->where('category', $this->category))
            ->when($this->severity !== '', fn ($q) => $q->where('severity', $this->severity))
            ->when($this->projectFilter, fn ($q) => $q->where('project_id', $this->projectFilter))
            ->with(['project:id,name', 'author:id,name', 'issue:id,title'])
            ->latest()
            ->paginate(12);
    }

    public function getProjectsProperty()
    {
        return Project::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getCategoriesProperty(): array
    {
        return LessonLearned::categories();
    }

    public function getSeveritiesProperty(): array
    {
        return LessonLearned::severities();
    }

    public function save(): void
    {
        $data = $this->validate();

        $tags = collect(explode(',', $this->tagsInput))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->unique()
            ->values()
            ->all();

        LessonLearned::create([
            'tenant_id' => auth()->user()->tenant_id,
            'created_by' => auth()->id(),
            'title' => $data['title'],
            'category' => $data['newCategory'],
            'severity' => $data['newSeverity'],
            'project_id' => $data['newProjectId'] ?: null,
            'issue_id' => $data['newIssueId'] ?: null,
            'problem_summary' => $data['problemSummary'],
            'root_cause' => $data['rootCause'],
            'corrective_action' => $data['correctiveAction'],
            'preventive_action' => $data['preventiveAction'] ?: null,
            'recommendation' => $data['recommendation'] ?: null,
            'tags' => $tags ?: null,
            'occurred_at' => now(),
        ]);

        $this->reset([
            'title', 'problemSummary', 'rootCause', 'correctiveAction',
            'preventiveAction', 'recommendation', 'tagsInput',
            'newProjectId', 'newIssueId', 'showCreateForm',
        ]);
        $this->newCategory = LessonLearned::CATEGORY_COMMISSIONING;
        $this->newSeverity = 'medium';

        session()->flash('success', 'Lesson learned captured.');
        $this->dispatch('toast', type: 'success', message: 'Lesson captured for future projects.');
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'category', 'severity', 'projectFilter']);
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.lessons-learned-list');
    }
}
