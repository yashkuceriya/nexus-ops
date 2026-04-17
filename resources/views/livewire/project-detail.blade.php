<div class="space-y-6" x-data="{ tab: 'issues' }">

    {{-- Top Bar --}}
    <div class="card px-5 py-3">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-2 text-[13px] text-ink-muted">
                <a href="{{ route('projects.index') }}" class="hover:text-ink transition-colors">Projects</a>
                <svg class="h-4 w-4 text-ink-soft" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-ink font-semibold">{{ $project->city }}, {{ $project->state }}</span>
            </div>
            <div class="flex items-center gap-5">
                <button class="btn-ghost inline-flex items-center gap-2">
                    <svg class="h-4 w-4 text-ink-soft" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/></svg>
                    View Blueprint
                </button>
                <div class="text-right">
                    <span class="text-[30px] font-bold tabular-nums tracking-tight {{ $project->readiness_score >= 80 ? 'text-emerald-600' : ($project->readiness_score >= 50 ? 'text-amber-600' : 'text-red-600') }}">
                        {{ number_format($project->readiness_score, 0) }}%
                    </span>
                    <span class="ml-1 label-kicker">Readiness</span>
                </div>
            </div>
        </div>
    </div>

    <div class="flex gap-6">
        {{-- Main Content --}}
        <div class="flex-1 min-w-0 space-y-6">

            {{-- Project Title --}}
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <p class="label-kicker">Project</p>
                    <h1 class="text-2xl font-bold tracking-tight text-ink mt-1">{{ $project->name }}</h1>
                    <p class="mt-1 text-[13px] text-ink-muted">Full Facility Modernization &amp; Expansion Project</p>
                    <p class="mt-0.5 text-[11px] text-ink-soft mono">{{ $project->address }}, {{ $project->city }}, {{ $project->state }} {{ $project->zip }}</p>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('projects.turnover', $project->id) }}" wire:navigate class="btn-primary inline-flex items-center gap-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-3-3v6m-9 1V5a2 2 0 012-2h4l2 2h8a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                        Turnover Package
                    </a>
                    <a href="{{ route('projects.pfc', $project->id) }}" wire:navigate class="btn-ghost inline-flex items-center gap-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Pre-Functional
                    </a>
                    <a href="{{ route('projects.cx-matrix', $project->id) }}" wire:navigate class="btn-ghost inline-flex items-center gap-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        Cx Test Matrix
                    </a>
                    <a href="{{ route('deficiencies.index', ['projectFilter' => $project->id]) }}" wire:navigate class="btn-ghost inline-flex items-center gap-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                        Deficiency Board
                    </a>
                    <a href="{{ route('projects.closeout', $project->id) }}" wire:navigate class="btn-ghost">Closeout</a>
                </div>
            </div>

            {{-- Critical Handover Blockers Banner --}}
            @if(count($this->blockers) > 0)
            <div class="card p-5 bg-red-600 border-red-600">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="h-5 w-5 text-red-200" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.168 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                            <h3 class="text-[11px] font-bold text-white uppercase tracking-wider">Critical Handover Blockers</h3>
                        </div>
                        <ul class="space-y-1.5">
                            @foreach($this->blockers as $blocker)
                            <li class="flex items-center text-[13px] text-red-100">
                                <svg class="h-4 w-4 mr-2 text-red-300 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
                                </svg>
                                {{ $blocker['label'] }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    <button class="flex-shrink-0 ml-4 inline-flex items-center rounded-lg bg-white/20 backdrop-blur px-4 py-2 text-[13px] font-semibold text-white hover:bg-white/30 transition-colors">
                        View Blockers
                        <svg class="ml-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
            @else
            <div class="card p-5 bg-emerald-50 border-emerald-200">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                    <p class="text-[13px] text-emerald-800 font-semibold">All handover requirements met. Project is ready for turnover.</p>
                </div>
            </div>
            @endif

            {{-- Tab Bar --}}
            <div class="card overflow-hidden p-0">
                <div class="hairline-b px-1">
                    <nav class="flex">
                        <button @click="tab = 'issues'"
                            :class="tab === 'issues' ? 'border-accent-600 text-accent-700' : 'border-transparent text-ink-muted hover:text-ink hover:border-slate-300'"
                            class="relative px-6 py-3.5 border-b-2 text-[13px] font-semibold transition-colors focus:outline-none">
                            Issues
                            <span :class="tab === 'issues' ? 'bg-accent-100 text-accent-700' : 'bg-slate-100 text-ink-muted'" class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold tabular-nums">{{ $this->issues->count() }}</span>
                        </button>
                        <button @click="tab = 'assets'"
                            :class="tab === 'assets' ? 'border-accent-600 text-accent-700' : 'border-transparent text-ink-muted hover:text-ink hover:border-slate-300'"
                            class="relative px-6 py-3.5 border-b-2 text-[13px] font-semibold transition-colors focus:outline-none">
                            Assets
                            <span :class="tab === 'assets' ? 'bg-accent-100 text-accent-700' : 'bg-slate-100 text-ink-muted'" class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold tabular-nums">{{ $this->assets->count() }}</span>
                        </button>
                        <button @click="tab = 'closeout'"
                            :class="tab === 'closeout' ? 'border-accent-600 text-accent-700' : 'border-transparent text-ink-muted hover:text-ink hover:border-slate-300'"
                            class="relative px-6 py-3.5 border-b-2 text-[13px] font-semibold transition-colors focus:outline-none">
                            Closeout Docs
                            <span :class="tab === 'closeout' ? 'bg-accent-100 text-accent-700' : 'bg-slate-100 text-ink-muted'" class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold tabular-nums">{{ $this->closeout->count() }}</span>
                        </button>
                    </nav>
                </div>

                {{-- Issues Tab --}}
                <div x-show="tab === 'issues'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="px-5 py-3 text-left label-kicker">Title</th>
                                    <th class="px-5 py-3 text-left label-kicker">Priority</th>
                                    <th class="px-5 py-3 text-left label-kicker">Status</th>
                                    <th class="px-5 py-3 text-left label-kicker">Asset</th>
                                    <th class="px-5 py-3 text-left label-kicker">Assigned To</th>
                                    <th class="px-5 py-3 text-right label-kicker">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($this->issues as $issue)
                                @php
                                    $priorityChip = match($issue->priority) {
                                        'critical' => 'chip-fail',
                                        'high' => 'chip-warn',
                                        'medium' => 'chip-accent',
                                        'low' => 'chip-pending',
                                        default => 'chip-pending',
                                    };
                                    $issueStatusChip = match($issue->status) {
                                        'open' => 'chip-fail',
                                        'in_progress' => 'chip-run',
                                        'work_completed' => 'chip-accent',
                                        'closed' => 'chip-pass',
                                        'deferred' => 'chip-warn',
                                        default => 'chip-pending',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-5 py-4 text-[13px] font-semibold text-ink">{{ $issue->title }}</td>
                                    <td class="px-5 py-4">
                                        <span class="chip {{ $priorityChip }}">{{ ucfirst($issue->priority) }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="chip {{ $issueStatusChip }}">
                                            <span class="dot {{ str_replace('chip-','dot-',$issueStatusChip) }}"></span>
                                            {{ str_replace('_', ' ', ucfirst($issue->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-[13px] text-ink-muted">{{ $issue->asset?->name ?? '—' }}</td>
                                    <td class="px-5 py-4">
                                        @if($issue->assignee)
                                        <div class="flex items-center gap-2.5">
                                            <div class="h-7 w-7 rounded-full bg-accent-100 flex items-center justify-center text-[11px] font-semibold text-accent-700 flex-shrink-0">
                                                {{ strtoupper(substr($issue->assignee->name, 0, 1)) }}{{ strtoupper(substr(strstr($issue->assignee->name, ' '), 1, 1)) }}
                                            </div>
                                            <span class="text-[13px] text-ink-muted">{{ $issue->assignee->name }}</span>
                                        </div>
                                        @else
                                        <span class="text-[13px] text-ink-soft">Unassigned</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div x-data="{ open: false }" class="relative inline-block text-left">
                                            <button @click="open = !open" class="rounded-lg p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4z"/></svg>
                                            </button>
                                            <div x-show="open" @click.away="open = false" x-transition
                                                class="absolute right-0 z-10 mt-1 w-44 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black/5 focus:outline-none">
                                                <div class="py-1">
                                                    @if($issue->workOrder)
                                                    <a href="{{ route('work-orders.show', $issue->workOrder) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">View Work Order</a>
                                                    @endif
                                                    <a href="{{ route('assets.show', $issue->asset_id) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">View Asset</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-12 text-center">
                                        <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        <p class="mt-2 text-sm font-medium text-gray-500">No issues found</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Assets Tab --}}
                <div x-show="tab === 'assets'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="px-5 py-3 text-left label-kicker">Asset</th>
                                    <th class="px-5 py-3 text-left label-kicker">System</th>
                                    <th class="px-5 py-3 text-left label-kicker">Location</th>
                                    <th class="px-5 py-3 text-left label-kicker">Condition</th>
                                    <th class="px-5 py-3 text-left label-kicker">Cx Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($this->assets as $asset)
                                @php
                                    $conditionChip = match($asset->condition) {
                                        'excellent','good' => 'chip-pass',
                                        'fair' => 'chip-warn',
                                        'poor' => 'chip-warn',
                                        'critical' => 'chip-fail',
                                        default => 'chip-pending',
                                    };
                                    $cxChip = match($asset->commissioning_status) {
                                        'completed' => 'chip-pass',
                                        'in_progress' => 'chip-run',
                                        'not_started' => 'chip-pending',
                                        'deferred' => 'chip-warn',
                                        default => 'chip-pending',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-5 py-4">
                                        <div class="text-[13px] font-semibold text-ink">{{ $asset->name }}</div>
                                        <div class="text-[11px] text-ink-soft mono mt-0.5">{{ $asset->qr_code }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-[13px] text-ink-muted">{{ $asset->system_type }}</td>
                                    <td class="px-5 py-4 text-[13px] text-ink-muted">{{ $asset->location?->name ?? '—' }}</td>
                                    <td class="px-5 py-4">
                                        <span class="chip {{ $conditionChip }}">
                                            <span class="dot {{ str_replace('chip-','dot-',$conditionChip) }}"></span>
                                            {{ ucfirst($asset->condition) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="chip {{ $cxChip }}">{{ str_replace('_', ' ', ucfirst($asset->commissioning_status)) }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-12 text-center">
                                        <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        <p class="mt-2 text-sm font-medium text-gray-500">No assets found</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Closeout Tab --}}
                <div x-show="tab === 'closeout'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="px-5 py-3 text-left label-kicker">Document</th>
                                    <th class="px-5 py-3 text-left label-kicker">Category</th>
                                    <th class="px-5 py-3 text-left label-kicker">Asset</th>
                                    <th class="px-5 py-3 text-left label-kicker">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($this->closeout as $req)
                                @php
                                    $closeoutChip = match($req->status) {
                                        'approved' => 'chip-pass',
                                        'submitted' => 'chip-run',
                                        'required' => 'chip-fail',
                                        'rejected' => 'chip-warn',
                                        default => 'chip-pending',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
                                                <svg class="h-4 w-4 text-ink-soft" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            </div>
                                            <span class="text-[13px] font-semibold text-ink">{{ $req->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-[13px] text-ink-muted">{{ str_replace('_', ' ', ucfirst($req->category)) }}</td>
                                    <td class="px-5 py-4 text-[13px] text-ink-muted">{{ $req->asset?->name ?? 'Project-wide' }}</td>
                                    <td class="px-5 py-4">
                                        <span class="chip {{ $closeoutChip }}">
                                            <span class="dot {{ str_replace('chip-','dot-',$closeoutChip) }}"></span>
                                            {{ ucfirst($req->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-12 text-center">
                                        <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <p class="mt-2 text-sm font-medium text-gray-500">No closeout documents found</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Sidebar --}}
        <div class="hidden lg:block w-80 flex-shrink-0 space-y-5">

            {{-- Project Map Card --}}
            <div class="card overflow-hidden p-0 sticky top-6">
                <div class="px-4 py-3 hairline-b">
                    <h3 class="text-[15px] font-semibold text-ink">Project Map</h3>
                </div>
                <div class="h-48 bg-slate-50 flex items-center justify-center">
                    <div class="text-center">
                        <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <p class="mt-1.5 text-[11px] text-ink-soft mono">{{ $project->city }}, {{ $project->state }}</p>
                    </div>
                </div>
            </div>

            {{-- Team Activity Card --}}
            <div class="card overflow-hidden p-0">
                <div class="px-4 py-3 hairline-b">
                    <h3 class="text-[15px] font-semibold text-ink">Team Activity</h3>
                </div>
                <div class="divide-y divide-slate-100">
                    <div class="px-4 py-3 flex items-start gap-3">
                        <div class="relative flex-shrink-0">
                            <div class="h-8 w-8 rounded-full bg-accent-100 flex items-center justify-center text-[11px] font-semibold text-accent-700">JD</div>
                            <span class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[12px] text-ink-muted"><span class="font-semibold text-ink">John Doe</span> updated issue status</p>
                            <p class="text-[11px] text-ink-soft mono mt-0.5">2 minutes ago</p>
                        </div>
                    </div>
                    <div class="px-4 py-3 flex items-start gap-3">
                        <div class="relative flex-shrink-0">
                            <div class="h-8 w-8 rounded-full bg-amber-100 flex items-center justify-center text-[11px] font-semibold text-amber-700">SM</div>
                            <span class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[12px] text-ink-muted"><span class="font-semibold text-ink">Sarah Miller</span> uploaded closeout doc</p>
                            <p class="text-[11px] text-ink-soft mono mt-0.5">15 minutes ago</p>
                        </div>
                    </div>
                    <div class="px-4 py-3 flex items-start gap-3">
                        <div class="relative flex-shrink-0">
                            <div class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center text-[11px] font-semibold text-ink-muted">MR</div>
                            <span class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full bg-slate-300 ring-2 ring-white"></span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[12px] text-ink-muted"><span class="font-semibold text-ink">Mike Ross</span> completed asset inspection</p>
                            <p class="text-[11px] text-ink-soft mono mt-0.5">1 hour ago</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
