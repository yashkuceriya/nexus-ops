@php
    $columns = \App\Livewire\DeficiencyBoard::COLUMNS;
    $board = $this->board;
    $counts = $this->columnCounts;

    // Map column tone color key -> chip class
    $columnChip = fn ($color) => match($color) {
        'red', 'rose' => 'chip-fail',
        'emerald', 'green' => 'chip-pass',
        'blue', 'sky', 'indigo' => 'chip-run',
        'amber', 'yellow' => 'chip-warn',
        default => 'chip-pending',
    };
    // Priority -> chip class
    $priorityChipCls = fn ($p) => match(strtolower((string) $p)) {
        'critical', 'emergency' => 'chip-fail',
        'high' => 'chip-warn',
        'medium' => 'chip-accent',
        'low' => 'chip-pending',
        default => 'chip-pending',
    };
@endphp

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <p class="label-kicker">Commissioning · Deficiencies</p>
            <h1 class="text-2xl font-bold tracking-tight text-ink mt-1">Deficiency Board</h1>
            <p class="text-[13px] text-ink-muted mt-0.5">Total: <strong class="text-ink mono">{{ array_sum($counts) }}</strong> deficiencies across {{ count($counts) }} stages.</p>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="card p-4">
        <div class="flex flex-wrap items-center gap-3">
            <select wire:model.live="projectFilter" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-[13px]">
                <option value="">All Projects</option>
                @foreach($this->projects as $project)
                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="priorityFilter" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-[13px]">
                <option value="">All Priorities</option>
                <option value="critical">Critical</option>
                <option value="emergency">Emergency</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
            </select>
            <label class="inline-flex items-center gap-2 text-[13px] text-ink">
                <input type="checkbox" wire:model.live="autoOpenedOnly" class="rounded border-gray-300 text-accent-600">
                FPT-generated deficiencies only
            </label>
        </div>
    </div>

    {{-- Columns --}}
    @if(array_sum($counts) === 0)
        <div class="card p-10 text-center">
            <div class="mx-auto w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center">
                <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                </svg>
            </div>
            <h2 class="mt-4 text-base font-semibold text-ink">No open deficiencies</h2>
            <p class="mt-1 text-[13px] text-ink-muted max-w-sm mx-auto">
                Either there are no tracked projects with open issues right now, or every deficiency has been closed out. Failed FPT steps will auto-open new cards here.
            </p>
            <a href="{{ route('fpt.executions.index') }}" class="mt-5 btn-ghost inline-flex items-center gap-2 text-[12px]">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Review test executions
            </a>
        </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach($columns as $key => $col)
            <div class="card p-0 overflow-hidden flex flex-col min-h-[300px]">
                <div class="px-4 py-3 hairline-b flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <p class="label-kicker">{{ $col['label'] }}</p>
                    </div>
                    <span class="chip {{ $columnChip($col['color']) }} mono tabular-nums">{{ $counts[$key] ?? 0 }}</span>
                </div>
                <div class="p-3 space-y-2 flex-1">
                    @forelse($board[$key] ?? [] as $card)
                        <div wire:key="issue-{{ $card['id'] }}" class="card p-3 text-[13px]">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-ink leading-snug">{{ $card['title'] }}</div>
                                    <div class="text-[11px] text-ink-soft mt-0.5 mono">
                                        #{{ $card['id'] }}
                                        @if($card['source_system'] === 'fpt')
                                            · <span class="text-accent-700">FPT-auto</span>
                                        @endif
                                        @if($card['project']) · {{ $card['project'] }} @endif
                                    </div>
                                </div>
                                <span class="chip {{ $priorityChipCls($card['priority']) }} shrink-0">{{ ucfirst($card['priority']) }}</span>
                            </div>

                            @if($card['asset_id'])
                                <a href="{{ route('assets.show', $card['asset_id']) }}" wire:navigate
                                    class="mt-2 inline-flex items-center gap-1 text-[11px] text-ink-muted hover:text-accent-700">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                                    <span>{{ $card['asset'] }}</span>
                                    @if($card['asset_tag']) <span class="mono text-[11px]">· {{ $card['asset_tag'] }}</span> @endif
                                </a>
                            @endif

                            <div class="mt-3 flex items-center justify-between text-[11px]">
                                <div class="flex items-center gap-2">
                                    <span class="mono {{ $this->ageClass($card['age_days']) }}">{{ $card['age_days'] }}d old</span>
                                    @if($card['overdue'])
                                        <span class="chip chip-fail">Overdue</span>
                                    @elseif($card['due_date'])
                                        <span class="text-ink-soft mono">Due {{ $card['due_date'] }}</span>
                                    @endif
                                </div>
                                <div>
                                    @if($card['assignee'])
                                        <span class="text-ink-muted">{{ $card['assignee'] }}</span>
                                    @else
                                        <button wire:click="assignToMe({{ $card['id'] }})"
                                            class="text-[10px] font-semibold uppercase tracking-wide text-accent-700 hover:text-accent-800">Claim</button>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-3 flex items-center justify-between pt-2 hairline-t">
                                <button wire:click="rewind({{ $card['id'] }})"
                                    class="text-[11px] font-medium text-ink-soft hover:text-ink disabled:opacity-30"
                                    @if($key === 'open') disabled @endif>
                                    ← Back
                                </button>
                                @if($col['next'])
                                    <button wire:click="advance({{ $card['id'] }})"
                                        class="text-[11px] font-semibold text-accent-700 hover:text-accent-800">
                                        Advance →
                                    </button>
                                @else
                                    <span class="text-[11px] text-emerald-700 font-semibold">Resolved</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-[11px] text-ink-soft">No items.</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
    @endif
</div>
