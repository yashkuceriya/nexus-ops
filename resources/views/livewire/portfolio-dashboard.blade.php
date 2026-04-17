<div class="space-y-6" wire:poll.60s="refreshKpis">
    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <p class="label-kicker">System Status · Operational</p>
            <h1 class="text-2xl font-bold tracking-tight text-ink mt-1">Portfolio Dashboard</h1>
            <p class="text-[13px] text-ink-muted mt-0.5">Live rollup across {{ count($this->projects) }} active commissioning projects.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="mono text-ink-soft">Updated {{ \Carbon\Carbon::parse($lastUpdated)->diffForHumans() }}</span>
            <a href="{{ route('reports.export-pdf') }}" class="btn-ghost inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10.5v6m3-3H9m4.06-7.19l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"/></svg>
                Generate PDF
            </a>
        </div>
    </div>

    @php
        $fpt = $this->commissioningSnapshot;
        $pfc = $this->pfcSnapshot;
        $avgReadiness = collect($this->projects)->avg('readiness_score') ?? 0;
        $openIssues = $this->kpis['open_issues'];
        $slaBreached = $this->kpis['sla_breached'];
    @endphp

    {{-- KPI row --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card kpi">
            <p class="label-kicker">Readiness %</p>
            <div class="kpi-value text-ink mt-2">{{ number_format($avgReadiness, 1) }}<span class="text-lg text-ink-soft">%</span></div>
            <p class="text-[11px] text-emerald-600 mt-1 font-semibold">↑ Portfolio average</p>
        </div>
        <div class="card kpi">
            <div class="flex items-center justify-between">
                <p class="label-kicker">Open Deficiencies</p>
                @if($openIssues > 0)<span class="dot dot-fail"></span>@endif
            </div>
            <div class="kpi-value text-ink mt-2">{{ $openIssues }}</div>
            <p class="text-[11px] text-ink-soft mt-1 mono">{{ $fpt['failed'] }} from FPT · {{ $pfc['failed'] }} from PFC</p>
        </div>
        <div class="card kpi">
            <p class="label-kicker">FPT Pass Rate</p>
            <div class="kpi-value text-ink mt-2">{{ number_format($fpt['pass_rate'], 1) }}<span class="text-lg text-ink-soft">%</span></div>
            <p class="text-[11px] text-ink-soft mt-1 mono">{{ $fpt['passed'] }}/{{ $fpt['total'] }} executions</p>
        </div>
        <div class="card kpi">
            <div class="flex items-center justify-between">
                <p class="label-kicker">SLA Breaches</p>
                @if($slaBreached > 0)<span class="dot dot-warn"></span>@endif
            </div>
            <div class="kpi-value {{ $slaBreached > 0 ? 'text-red-600' : 'text-ink' }} mt-2">{{ str_pad($slaBreached, 2, '0', STR_PAD_LEFT) }}</div>
            <p class="text-[11px] text-ink-soft mt-1 mono">{{ $this->kpis['open_work_orders'] }} open work orders</p>
        </div>
    </div>

    {{-- Readiness + activity --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="card lg:col-span-2 p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-[15px] font-semibold text-ink">Readiness by Project</h2>
                    <p class="text-[12px] text-ink-muted">Active commissioning portfolio</p>
                </div>
                <a href="{{ route('projects.index') }}" class="text-[12px] font-semibold text-accent-700 hover:text-accent-800">View all →</a>
            </div>
            <div class="space-y-3">
                @foreach($this->projects->take(6) as $project)
                    <a href="{{ route('projects.show', $project->id) }}" class="block group">
                        <div class="flex items-center justify-between text-[12px] mb-1">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="font-semibold text-ink uppercase tracking-wide truncate">{{ \Illuminate\Support\Str::limit($project->name, 40) }}</span>
                                <span class="chip chip-pending shrink-0">{{ str_replace('_', ' ', $project->status) }}</span>
                            </div>
                            <span class="mono text-ink-soft shrink-0 ml-2">{{ number_format($project->readiness_score, 1) }}% complete</span>
                        </div>
                        <div class="h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full bg-accent-600 group-hover:bg-accent-700 transition-all" style="width: {{ min(100, $project->readiness_score) }}%"></div>
                        </div>
                    </a>
                @endforeach
                @if($this->projects->isEmpty())
                    <p class="text-[12px] text-ink-soft">No active projects.</p>
                @endif
            </div>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-[15px] font-semibold text-ink">Live Activity</h2>
                    <p class="text-[12px] text-ink-muted">Recent portfolio events</p>
                </div>
                <span class="dot dot-pass animate-pulse"></span>
            </div>
            <ul class="space-y-3">
                @foreach($this->recentWorkOrders->take(5) as $wo)
                    @php
                        $chipClass = match($wo->status) {
                            'completed','verified' => 'chip-pass',
                            'in_progress' => 'chip-run',
                            'on_hold' => 'chip-warn',
                            'cancelled' => 'chip-pending',
                            default => 'chip-pending',
                        };
                    @endphp
                    <li class="flex gap-3">
                        <span class="chip {{ $chipClass }} shrink-0 mt-0.5 text-[10px]">{{ str_replace('_',' ', $wo->status) }}</span>
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('work-orders.show', $wo->id) }}" class="block text-[13px] font-semibold text-ink hover:text-accent-700 truncate">{{ $wo->title }}</a>
                            <p class="text-[11px] text-ink-soft mono truncate">{{ $wo->wo_number }} · {{ $wo->project?->name }}</p>
                        </div>
                        <span class="text-[11px] text-ink-soft shrink-0">{{ $wo->updated_at->diffForHumans(null, true) }}</span>
                    </li>
                @endforeach
                @if($this->recentWorkOrders->isEmpty())
                    <li class="text-[12px] text-ink-soft">No recent activity.</li>
                @endif
            </ul>
            <a href="{{ route('audit-log.index') }}" class="mt-4 btn-ghost w-full inline-flex items-center justify-center gap-1 text-[12px]">View Audit Trail</a>
        </div>
    </div>

    {{-- Commissioning breakdown --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="card p-5">
            <p class="label-kicker">Commissioning Performance</p>
            <div class="flex items-end gap-6 mt-2">
                <div>
                    <div class="kpi-value text-ink">{{ $fpt['total'] }}</div>
                    <p class="text-[11px] text-ink-soft mt-1">Total executions</p>
                </div>
                <div class="flex-1 grid grid-cols-3 gap-3">
                    <div>
                        <div class="flex items-center gap-1.5"><span class="dot dot-pass"></span><span class="mono text-[11px] text-ink-soft">PASS</span></div>
                        <div class="text-[18px] font-bold text-emerald-700 mt-0.5 tabular-nums">{{ $fpt['passed'] }}</div>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5"><span class="dot dot-fail"></span><span class="mono text-[11px] text-ink-soft">FAIL</span></div>
                        <div class="text-[18px] font-bold text-red-700 mt-0.5 tabular-nums">{{ $fpt['failed'] }}</div>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5"><span class="dot dot-run"></span><span class="mono text-[11px] text-ink-soft">RUN</span></div>
                        <div class="text-[18px] font-bold text-accent-700 mt-0.5 tabular-nums">{{ $fpt['in_flight'] }}</div>
                    </div>
                </div>
            </div>
            <div class="mt-4 pt-3 hairline-t flex items-center justify-between text-[11px]">
                <span class="text-ink-soft">Witness coverage</span>
                <span class="mono font-semibold text-ink">{{ $fpt['witness_pct'] }}%</span>
            </div>
        </div>

        <div class="card p-5">
            <p class="label-kicker">Pre-Functional Checklists</p>
            <div class="flex items-end gap-6 mt-2">
                <div>
                    <div class="kpi-value text-ink">{{ $pfc['completion_rate'] }}<span class="text-lg text-ink-soft">%</span></div>
                    <p class="text-[11px] text-ink-soft mt-1">Completion rate</p>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between text-[11px] mono text-ink-soft mb-1">
                        <span>Clean rate</span><span class="text-ink">{{ $pfc['clean_rate'] }}%</span>
                    </div>
                    <div class="h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-full bg-emerald-500" style="width: {{ $pfc['clean_rate'] }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-[11px] mono text-ink-soft mt-3 mb-1">
                        <span>Item pass</span><span class="text-ink">{{ $pfc['item_passed'] }}/{{ $pfc['item_total'] }}</span>
                    </div>
                    <div class="h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-full bg-accent-600" style="width: {{ $pfc['item_total'] > 0 ? round($pfc['item_passed'] / $pfc['item_total'] * 100, 1) : 0 }}%"></div>
                    </div>
                </div>
            </div>
            <div class="mt-4 pt-3 hairline-t flex items-center justify-between text-[11px]">
                <span class="text-ink-soft">{{ $pfc['in_progress'] }} in progress</span>
                <a href="{{ route('reports.commissioning') }}" class="font-semibold text-accent-700 hover:text-accent-800">Open analytics →</a>
            </div>
        </div>
    </div>
</div>
