@php
    $columns = \App\Livewire\DeficiencyBoard::COLUMNS;
    $board = $this->board;
    $counts = $this->columnCounts;
@endphp

<div class="space-y-6">

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-3">
        <select wire:model.live="projectFilter" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
            <option value="">All Projects</option>
            @foreach($this->projects as $project)
                <option value="{{ $project->id }}">{{ $project->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="priorityFilter" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
            <option value="">All Priorities</option>
            <option value="critical">Critical</option>
            <option value="emergency">Emergency</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
        </select>
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" wire:model.live="autoOpenedOnly" class="rounded border-gray-300">
            FPT-generated deficiencies only
        </label>
        <span class="ml-auto text-xs text-gray-500">
            Total: <strong class="text-gray-900">{{ array_sum($counts) }}</strong> deficiencies across {{ count($counts) }} stages.
        </span>
    </div>

    {{-- Columns --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach($columns as $key => $col)
            <div class="rounded-xl border {{ $this->columnTone($col['color'])['head'] }} overflow-hidden flex flex-col min-h-[300px]">
                <div class="px-4 py-3 flex items-center justify-between border-b border-gray-200/70">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center h-6 min-w-[24px] px-1.5 rounded-full {{ $this->columnTone($col['color'])['badge'] }} text-white text-[11px] font-bold tabular-nums">{{ $counts[$key] ?? 0 }}</span>
                        <div class="text-sm font-semibold text-gray-900">{{ $col['label'] }}</div>
                    </div>
                </div>
                <div class="p-3 space-y-2 flex-1 bg-white/50">
                    @forelse($board[$key] ?? [] as $card)
                        <div wire:key="issue-{{ $card['id'] }}" class="rounded-lg border border-gray-200 bg-white shadow-sm hover:shadow-md transition p-3 text-sm">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-900 leading-snug">{{ $card['title'] }}</div>
                                    <div class="text-[11px] text-gray-500 mt-0.5">
                                        #{{ $card['id'] }}
                                        @if($card['source_system'] === 'fpt')
                                            · <span class="text-indigo-600">FPT-auto</span>
                                        @endif
                                        @if($card['project']) · {{ $card['project'] }} @endif
                                    </div>
                                </div>
                                <span class="inline-flex items-center rounded-full {{ $this->priorityChip($card['priority']) }} px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide">{{ $card['priority'] }}</span>
                            </div>

                            @if($card['asset_id'])
                                <a href="{{ route('assets.show', $card['asset_id']) }}" wire:navigate
                                    class="mt-2 inline-flex items-center gap-1 text-[11px] text-gray-700 hover:text-indigo-600">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                                    {{ $card['asset'] }} @if($card['asset_tag']) · {{ $card['asset_tag'] }} @endif
                                </a>
                            @endif

                            <div class="mt-3 flex items-center justify-between text-[11px]">
                                <div class="flex items-center gap-2">
                                    <span class="{{ $this->ageClass($card['age_days']) }}">{{ $card['age_days'] }}d old</span>
                                    @if($card['overdue'])
                                        <span class="inline-flex items-center rounded-full bg-red-100 px-1.5 py-0.5 text-[10px] font-bold text-red-700 uppercase">Overdue</span>
                                    @elseif($card['due_date'])
                                        <span class="text-gray-500">Due {{ $card['due_date'] }}</span>
                                    @endif
                                </div>
                                <div>
                                    @if($card['assignee'])
                                        <span class="text-gray-600">{{ $card['assignee'] }}</span>
                                    @else
                                        <button wire:click="assignToMe({{ $card['id'] }})"
                                            class="text-[10px] font-semibold uppercase tracking-wide text-indigo-600 hover:text-indigo-700">Claim</button>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-3 flex items-center justify-between pt-2 border-t border-gray-100">
                                <button wire:click="rewind({{ $card['id'] }})"
                                    class="text-[11px] font-medium text-gray-500 hover:text-gray-800 disabled:opacity-30"
                                    @if($key === 'open') disabled @endif>
                                    ← Back
                                </button>
                                @if($col['next'])
                                    <button wire:click="advance({{ $card['id'] }})"
                                        class="text-[11px] font-semibold text-{{ $col['color'] }}-700 hover:text-{{ $col['color'] }}-900">
                                        Advance →
                                    </button>
                                @else
                                    <span class="text-[11px] text-emerald-600 font-semibold">Resolved</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-xs text-gray-400">No items.</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>
