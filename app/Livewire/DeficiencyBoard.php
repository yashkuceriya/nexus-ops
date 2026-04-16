<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\Issue;
use App\Models\Project;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Kanban-style punch-list board for open deficiencies — the view a
 * commissioning authority uses every morning on a live project.
 *
 * Cards are `Issue` records (including those auto-opened by a failed FPT
 * step). Columns are lifecycle states. Moving a card mutates the issue
 * status, writes to the audit log, and re-keys the view so Livewire can
 * patch the DOM without flicker.
 */
class DeficiencyBoard extends Component
{
    public const COLUMNS = [
        'open' => ['label' => 'Open', 'color' => 'red', 'next' => 'in_progress'],
        'in_progress' => ['label' => 'In Progress', 'color' => 'amber', 'next' => 'work_completed'],
        'work_completed' => ['label' => 'Work Complete', 'color' => 'indigo', 'next' => 'closed'],
        'closed' => ['label' => 'Closed', 'color' => 'emerald', 'next' => null],
    ];

    private const PRIORITY_CHIPS = [
        'critical' => 'bg-red-600 text-white',
        'emergency' => 'bg-red-700 text-white',
        'high' => 'bg-amber-500 text-white',
        'medium' => 'bg-indigo-100 text-indigo-800',
        'low' => 'bg-gray-100 text-gray-700',
    ];

    private const COLUMN_TONES = [
        'red' => ['head' => 'bg-red-50 border-red-200', 'badge' => 'bg-red-600'],
        'amber' => ['head' => 'bg-amber-50 border-amber-200', 'badge' => 'bg-amber-500'],
        'indigo' => ['head' => 'bg-indigo-50 border-indigo-200', 'badge' => 'bg-indigo-600'],
        'emerald' => ['head' => 'bg-emerald-50 border-emerald-200', 'badge' => 'bg-emerald-600'],
    ];

    /**
     * View-level helpers so the Blade template doesn't need `match` or
     * nested ternaries — Livewire's Blade compiler chokes on complex
     * `@php` blocks inside `@foreach` loops.
     */
    public function columnTone(string $color): array
    {
        return self::COLUMN_TONES[$color] ?? self::COLUMN_TONES['indigo'];
    }

    public function priorityChip(?string $priority): string
    {
        return self::PRIORITY_CHIPS[$priority] ?? 'bg-gray-100 text-gray-700';
    }

    public function ageClass(int $days): string
    {
        if ($days > 30) {
            return 'text-red-600 font-semibold';
        }
        if ($days > 7) {
            return 'text-amber-600 font-semibold';
        }

        return 'text-gray-500';
    }

    #[Url(history: true)]
    public ?int $projectFilter = null;

    #[Url(history: true)]
    public string $priorityFilter = '';

    #[Url(history: true)]
    public bool $autoOpenedOnly = false;

    public function mount(?int $projectId = null): void
    {
        if ($projectId !== null) {
            $this->projectFilter = $projectId;
        }
    }

    #[Computed]
    public function projects()
    {
        return Project::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    #[Computed]
    public function board(): array
    {
        $issues = Issue::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->when($this->projectFilter, fn ($q) => $q->where('project_id', $this->projectFilter))
            ->when($this->priorityFilter !== '', fn ($q) => $q->where('priority', $this->priorityFilter))
            ->when($this->autoOpenedOnly, fn ($q) => $q->where('source_system', 'fpt'))
            ->with(['asset:id,name,asset_tag', 'assignee:id,name', 'project:id,name'])
            ->orderBy('priority')
            ->orderBy('created_at')
            ->limit(500)
            ->get();

        $grouped = array_fill_keys(array_keys(self::COLUMNS), []);

        foreach ($issues as $issue) {
            $status = $issue->status;
            if (! array_key_exists($status, $grouped)) {
                continue;
            }
            $grouped[$status][] = [
                'id' => $issue->id,
                'title' => $issue->title,
                'priority' => $issue->priority,
                'age_days' => (int) $issue->created_at->diffInDays(now()),
                'asset' => $issue->asset?->name,
                'asset_tag' => $issue->asset?->asset_tag,
                'asset_id' => $issue->asset_id,
                'project' => $issue->project?->name,
                'assignee' => $issue->assignee?->name,
                'source_system' => $issue->source_system,
                'due_date' => $issue->due_date?->format('M d'),
                'overdue' => $issue->due_date && $issue->due_date->isPast(),
            ];
        }

        return $grouped;
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function columnCounts(): array
    {
        return array_map(fn ($rows) => count($rows), $this->board);
    }

    /**
     * Advance an issue to its next column (or close it). Records the
     * transition in the audit log with the originating status so nothing is
     * lost to history.
     */
    public function advance(int $issueId): void
    {
        $user = auth()->user();

        $issue = Issue::query()
            ->where('tenant_id', $user->tenant_id)
            ->findOrFail($issueId);

        $column = self::COLUMNS[$issue->status] ?? null;
        if ($column === null || $column['next'] === null) {
            return;
        }

        $from = $issue->status;
        $issue->status = $column['next'];
        if ($issue->status === 'closed' || $issue->status === 'work_completed') {
            $issue->resolved_at = $issue->resolved_at ?: now();
        }
        $issue->save();

        AuditLog::record(
            action: 'issue_status_advanced',
            model: $issue,
            oldValues: ['status' => $from],
            newValues: ['status' => $issue->status],
        );

        unset($this->board, $this->columnCounts);
    }

    /**
     * Reverse an issue one column to the left (undo button on a card).
     */
    public function rewind(int $issueId): void
    {
        $user = auth()->user();

        $issue = Issue::query()
            ->where('tenant_id', $user->tenant_id)
            ->findOrFail($issueId);

        $reverseMap = array_flip(array_filter(array_map(fn ($c) => $c['next'], self::COLUMNS)));
        $prev = $reverseMap[$issue->status] ?? null;
        if ($prev === null) {
            return;
        }

        $from = $issue->status;
        $issue->status = $prev;
        if ($prev !== 'closed' && $prev !== 'work_completed') {
            $issue->resolved_at = null;
        }
        $issue->save();

        AuditLog::record(
            action: 'issue_status_reverted',
            model: $issue,
            oldValues: ['status' => $from],
            newValues: ['status' => $issue->status],
        );

        unset($this->board, $this->columnCounts);
    }

    /**
     * Claim an unassigned issue. Common field-tech micro-interaction —
     * "I've got this one", then keep moving.
     */
    public function assignToMe(int $issueId): void
    {
        $user = auth()->user();

        $issue = Issue::query()
            ->where('tenant_id', $user->tenant_id)
            ->findOrFail($issueId);

        if ($issue->assigned_to === $user->id) {
            return;
        }

        $from = $issue->assigned_to;
        $issue->assigned_to = $user->id;
        $issue->save();

        AuditLog::record(
            action: 'issue_assigned',
            model: $issue,
            oldValues: ['assigned_to' => $from],
            newValues: ['assigned_to' => $user->id],
        );

        unset($this->board);
    }

    public function render()
    {
        return view('livewire.deficiency-board')
            ->layout('layouts.app', [
                'title' => 'Deficiency Board',
                'subtitle' => 'Punch-list kanban across projects & assets',
            ]);
    }
}
