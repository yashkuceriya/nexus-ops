<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <p class="label-kicker">Portfolio</p>
            <h1 class="text-2xl font-bold tracking-tight text-ink mt-1">Projects</h1>
            <p class="text-[13px] text-ink-muted mt-0.5">Manage and track high-priority facility handovers.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            {{-- Search --}}
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-ink-soft" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search projects..."
                    class="w-56 rounded-lg border border-slate-200 bg-white text-[13px] pl-9 pr-3 py-2 focus:ring-2 focus:ring-accent-500/20 focus:border-accent-500 transition">
            </div>
            <select wire:model.live="statusFilter"
                class="rounded-lg border border-slate-200 bg-white text-[13px] px-3 py-2 focus:ring-2 focus:ring-accent-500/20 focus:border-accent-500 transition">
                <option value="">All Statuses</option>
                <option value="planning">Planning</option>
                <option value="commissioning">Commissioning</option>
                <option value="closeout">Closeout</option>
                <option value="operational">Operational</option>
                <option value="archived">Archived</option>
            </select>
            <a href="{{ route('work-orders.index') }}" class="btn-primary inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                New Request
            </a>
        </div>
    </div>

    {{-- Filter Pills --}}
    <div class="flex items-center gap-2">
        <button wire:click="$set('statusFilter', '')"
            class="rounded-full px-3.5 py-1 text-[11px] font-semibold transition
            {{ $statusFilter === '' ? 'bg-ink text-white' : 'bg-white text-ink-muted border border-slate-200 hover:bg-slate-50' }}">
            All Status
        </button>
        <button wire:click="$set('statusFilter', 'commissioning')"
            class="rounded-full px-3.5 py-1 text-[11px] font-semibold transition
            {{ $statusFilter === 'commissioning' ? 'bg-ink text-white' : 'bg-white text-ink-muted border border-slate-200 hover:bg-slate-50' }}">
            Active
        </button>
        <button wire:click="$set('statusFilter', 'operational')"
            class="rounded-full px-3.5 py-1 text-[11px] font-semibold transition
            {{ $statusFilter === 'operational' ? 'bg-ink text-white' : 'bg-white text-ink-muted border border-slate-200 hover:bg-slate-50' }}">
            IoT Enabled
        </button>
    </div>

    {{-- Project Card Grid --}}
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        @foreach($this->projects as $project)
        @php
            $score = $project->readiness_score;
            $scoreColor = $score >= 80 ? 'text-emerald-600' : ($score >= 50 ? 'text-amber-600' : 'text-red-600');
        @endphp
        <div class="card p-5">
            {{-- Top Row --}}
            <div class="flex items-start justify-between mb-4">
                <span class="chip chip-accent">{{ strtoupper(str_replace('_', ' ', $project->project_type ?? 'General')) }}</span>
                <div class="text-right">
                    <span class="text-[30px] font-bold tabular-nums leading-none {{ $scoreColor }}">{{ number_format($score, 0) }}%</span>
                    <p class="label-kicker mt-1">Readiness</p>
                </div>
            </div>

            {{-- Project Name & Location --}}
            <h3 class="text-[15px] font-semibold text-ink">{{ $project->name }}</h3>
            <p class="text-[12px] text-ink-muted mt-0.5 flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-ink-soft flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                </svg>
                {{ $project->city }}, {{ $project->state }}
            </p>

            {{-- Stats Row --}}
            <div class="grid grid-cols-3 gap-3 mt-5">
                <div class="text-center p-3 bg-slate-50 rounded-lg border border-slate-100">
                    <p class="text-[18px] font-bold tabular-nums {{ $project->open_issues > 0 ? 'text-red-600' : 'text-ink' }}">{{ $project->open_issues }}</p>
                    <p class="label-kicker mt-1">Issues Open</p>
                </div>
                <div class="text-center p-3 bg-slate-50 rounded-lg border border-slate-100">
                    <p class="text-[18px] font-bold tabular-nums text-ink">{{ $project->completed_tests }}<span class="text-[13px] text-ink-soft font-normal">/{{ $project->total_tests }}</span></p>
                    <p class="label-kicker mt-1">Tests</p>
                </div>
                <div class="text-center p-3 bg-slate-50 rounded-lg border border-slate-100">
                    <p class="text-[18px] font-bold tabular-nums text-ink">{{ $project->completed_closeout_docs }}<span class="text-[13px] text-ink-soft font-normal">/{{ $project->total_closeout_docs }}</span></p>
                    <p class="label-kicker mt-1">Docs</p>
                </div>
            </div>

            {{-- Card Footer --}}
            <div class="mt-4 pt-3 hairline-t flex items-center justify-between">
                <div class="flex items-center gap-3">
                    @if($project->open_issues > 5)
                        <span class="chip chip-fail">
                            <span class="dot dot-fail animate-pulse"></span>
                            Critical
                        </span>
                    @endif
                    @if($project->target_handover_date)
                        <span class="text-[11px] text-ink-soft mono flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-ink-soft" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                            {{ $project->target_handover_date->format('M d, Y') }}
                        </span>
                    @endif
                </div>
                <a href="{{ route('projects.show', $project) }}" class="text-[12px] font-semibold text-accent-700 hover:text-accent-800">
                    View Details &rarr;
                </a>
            </div>
        </div>
        @endforeach

        {{-- Initialize New Project Card --}}
        <div class="rounded-xl border border-dashed border-slate-300 bg-white/50 hover:border-accent-400 hover:bg-accent-50/30 transition-all group cursor-pointer flex items-center justify-center min-h-[300px]">
            <div class="text-center px-8 py-12">
                <div class="mx-auto w-14 h-14 rounded-full bg-slate-100 group-hover:bg-accent-100 flex items-center justify-center transition-colors mb-4">
                    <svg class="w-7 h-7 text-ink-soft group-hover:text-accent-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </div>
                <h3 class="text-[15px] font-semibold text-ink group-hover:text-accent-700 transition-colors">Initialize New Project</h3>
                <p class="text-[12px] text-ink-muted mt-1.5 max-w-[220px]">Set up a new facility handover project with readiness tracking.</p>
            </div>
        </div>
    </div>
</div>
