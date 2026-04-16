<div>
    {{-- Header --}}
    <div class="sm:flex sm:items-start sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-100">Projects</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">Manage and track high-priority facility handovers</p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center gap-3">
            {{-- Search --}}
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search projects..."
                    class="w-56 rounded-lg border border-gray-300 bg-white shadow-sm text-sm pl-9 pr-3 py-2.5 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
            </div>
            {{-- Status Filter --}}
            <select wire:model.live="statusFilter"
                class="rounded-lg border border-gray-300 bg-white shadow-sm text-sm px-3 py-2.5 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                <option value="">All Statuses</option>
                <option value="planning">Planning</option>
                <option value="commissioning">Commissioning</option>
                <option value="closeout">Closeout</option>
                <option value="operational">Operational</option>
                <option value="archived">Archived</option>
            </select>
            {{-- New Request Button --}}
            <a href="{{ route('work-orders.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-500/20 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                New Request
            </a>
        </div>
    </div>

    {{-- Filter Pills --}}
    <div class="flex items-center gap-2 mb-6">
        <button wire:click="$set('statusFilter', '')"
            class="rounded-full px-4 py-1.5 text-xs font-semibold transition
            {{ $statusFilter === '' ? 'bg-gray-900 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
            All Status
        </button>
        <button wire:click="$set('statusFilter', 'commissioning')"
            class="rounded-full px-4 py-1.5 text-xs font-semibold transition
            {{ $statusFilter === 'commissioning' ? 'bg-gray-900 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
            Active
        </button>
        <button wire:click="$set('statusFilter', 'operational')"
            class="rounded-full px-4 py-1.5 text-xs font-semibold transition
            {{ $statusFilter === 'operational' ? 'bg-gray-900 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
            IoT Enabled
        </button>
    </div>

    {{-- Project Card Grid --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @foreach($this->projects as $project)
        @php
            $score = $project->readiness_score;
            $scoreColor = $score >= 80 ? 'emerald' : ($score >= 50 ? 'amber' : 'red');

            $typeColors = [
                'data_infrastructure' => ['bg-emerald-100', 'text-emerald-700', 'border-emerald-200'],
                'data infrastructure' => ['bg-emerald-100', 'text-emerald-700', 'border-emerald-200'],
                'commissioning' => ['bg-blue-100', 'text-blue-700', 'border-blue-200'],
                'closeout' => ['bg-amber-100', 'text-amber-700', 'border-amber-200'],
                'operational' => ['bg-purple-100', 'text-purple-700', 'border-purple-200'],
                'planning' => ['bg-cyan-100', 'text-cyan-700', 'border-cyan-200'],
            ];
            $typeKey = strtolower($project->project_type ?? 'general');
            $badgeClasses = $typeColors[$typeKey] ?? ['bg-gray-100', 'text-gray-700', 'border-gray-200'];
        @endphp
        <div class="overflow-hidden rounded-xl bg-white dark:bg-zinc-800 shadow-sm border border-gray-100 dark:border-zinc-700 hover:shadow-md transition-shadow">
            <div class="p-6">
                {{-- Top Row: Badge + Readiness Score --}}
                <div class="flex items-start justify-between mb-4">
                    <span class="inline-flex items-center rounded-md px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $badgeClasses[0] }} {{ $badgeClasses[1] }} border {{ $badgeClasses[2] }}">
                        {{ strtoupper(str_replace('_', ' ', $project->project_type ?? 'General')) }}
                    </span>
                    <div class="text-right">
                        <span class="text-4xl font-extrabold text-{{ $scoreColor }}-600 leading-none">{{ number_format($score, 0) }}%</span>
                        <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide mt-0.5">Readiness</p>
                    </div>
                </div>

                {{-- Project Name & Location --}}
                <h3 class="text-lg font-semibold text-gray-900 dark:text-zinc-100">{{ $project->name }}</h3>
                <p class="text-sm text-gray-500 mt-0.5 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                    </svg>
                    {{ $project->city }}, {{ $project->state }}
                </p>

                {{-- Stats Row --}}
                <div class="grid grid-cols-3 gap-3 mt-5">
                    <div class="text-center p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <p class="text-xl font-bold {{ $project->open_issues > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $project->open_issues }}</p>
                        <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wide mt-0.5">Issues Open</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <p class="text-xl font-bold text-gray-900">{{ $project->completed_tests }}<span class="text-sm text-gray-400 font-normal">/{{ $project->total_tests }}</span></p>
                        <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wide mt-0.5">Tests</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <p class="text-xl font-bold text-gray-900">{{ $project->completed_closeout_docs }}<span class="text-sm text-gray-400 font-normal">/{{ $project->total_closeout_docs }}</span></p>
                        <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wide mt-0.5">Docs</p>
                    </div>
                </div>
            </div>

            {{-- Card Footer --}}
            <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    @if($project->open_issues > 5)
                        <span class="inline-flex items-center gap-1 rounded-md bg-red-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-red-700 border border-red-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                            Critical
                        </span>
                    @endif
                    @if($project->target_handover_date)
                        <span class="text-xs text-gray-500 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                            {{ $project->target_handover_date->format('M d, Y') }}
                        </span>
                    @endif
                </div>
                <a href="{{ route('projects.show', $project) }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition-colors">
                    View Details &rarr;
                </a>
            </div>
        </div>
        @endforeach

        {{-- Initialize New Project Card --}}
        <div class="overflow-hidden rounded-xl border-2 border-dashed border-gray-200 bg-white/50 hover:border-emerald-300 hover:bg-emerald-50/30 transition-all group cursor-pointer flex items-center justify-center min-h-[320px]">
            <div class="text-center px-8 py-12">
                <div class="mx-auto w-14 h-14 rounded-full bg-gray-100 group-hover:bg-emerald-100 flex items-center justify-center transition-colors mb-4">
                    <svg class="w-7 h-7 text-gray-400 group-hover:text-emerald-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-gray-700 group-hover:text-emerald-700 transition-colors">Initialize New Project</h3>
                <p class="text-sm text-gray-500 mt-1.5 max-w-[220px]">Set up a new facility handover project with readiness tracking</p>
            </div>
        </div>
    </div>
</div>
