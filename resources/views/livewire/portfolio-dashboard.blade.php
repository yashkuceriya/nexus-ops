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
        $sparks = $this->sparklines;

        // Inline SVG sparkline with Alpine-driven hover tooltip.
        $spark = function (array $values, string $stroke = '#4F46E5', string $fill = 'rgba(79,70,229,.08)', string $unit = ''): string {
            if (empty($values)) return '';
            $w = 120; $h = 32; $pad = 2;
            $max = max($values); $min = min($values);
            $range = max(0.001, $max - $min);
            $step = ($w - 2 * $pad) / max(1, count($values) - 1);
            $points = []; $dots = '';
            $dates = [];
            foreach ($values as $i => $v) {
                $x = round($pad + $i * $step, 2);
                $y = round($h - $pad - (($v - $min) / $range) * ($h - 2 * $pad), 2);
                $points[] = "$x,$y";
                $date = now()->subDays(count($values) - 1 - $i)->format('M j');
                $dates[] = $date;
                $label = htmlspecialchars($date.' · '.rtrim(rtrim(number_format($v, 1), '0'), '.').$unit, ENT_QUOTES);
                $dots .= '<circle cx="'.$x.'" cy="'.$y.'" r="5" fill="transparent" @mouseenter="tip=\''.$label.'\'; tipX='.$x.'; tipY='.$y.'" @mouseleave="tip=null" class="cursor-pointer" />';
            }
            $area = 'M'.$points[0].' L'.implode(' L', array_slice($points, 1))." L{$w},{$h} L0,{$h} Z";
            $line = 'M'.implode(' L', $points);
            return '<div class="relative" x-data="{ tip: null, tipX: 0, tipY: 0 }">'
                .'<svg viewBox="0 0 '.$w.' '.$h.'" class="w-full h-8 overflow-visible" preserveAspectRatio="none">'
                .'<path d="'.$area.'" fill="'.$fill.'" />'
                .'<path d="'.$line.'" fill="none" stroke="'.$stroke.'" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'
                .$dots
                .'</svg>'
                .'<div x-show="tip" x-cloak class="absolute -translate-x-1/2 -translate-y-full pointer-events-none bg-ink text-white text-[10px] mono px-1.5 py-0.5 rounded whitespace-nowrap" :style="`left:${(tipX/'.$w.')*100}%; top:${tipY - 6}px`"><span x-text="tip"></span></div>'
                .'</div>';
        };
    @endphp

    {{-- KPI row --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('projects.index') }}" class="card kpi group hover:border-accent-300 transition-colors">
            <p class="label-kicker flex items-center justify-between">
                <span>Readiness %</span>
                <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 text-accent-600 transition" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </p>
            <div class="flex items-end justify-between gap-3 mt-2">
                <div class="kpi-value text-ink">{{ number_format($avgReadiness, 1) }}<span class="text-lg text-ink-soft">%</span></div>
                <div class="w-24 opacity-90">{!! $spark($sparks['readiness'], '#4F46E5', 'rgba(79,70,229,.10)', '%') !!}</div>
            </div>
            <p class="text-[11px] text-emerald-600 mt-1 font-semibold flex items-center gap-1"><span class="dot dot-pass"></span>Portfolio average</p>
        </a>
        <a href="{{ route('deficiencies.index') }}" class="card kpi group hover:border-red-300 transition-colors">
            <p class="label-kicker flex items-center justify-between">
                <span class="flex items-center gap-1.5">Open Deficiencies @if($openIssues > 0)<span class="dot dot-fail"></span>@endif</span>
                <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 text-red-500 transition" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </p>
            <div class="flex items-end justify-between gap-3 mt-2">
                <div class="kpi-value text-ink">{{ $openIssues }}</div>
                <div class="w-24 opacity-90">{!! $spark($sparks['deficiencies'], '#EF4444', 'rgba(239,68,68,.10)') !!}</div>
            </div>
            <p class="text-[11px] text-ink-soft mt-1 mono">{{ $fpt['failed'] }} from FPT · {{ $pfc['failed'] }} from PFC</p>
        </a>
        <a href="{{ route('fpt.executions.index') }}" class="card kpi group hover:border-emerald-300 transition-colors">
            <p class="label-kicker flex items-center justify-between">
                <span>FPT Pass Rate</span>
                <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 text-emerald-600 transition" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </p>
            <div class="flex items-end justify-between gap-3 mt-2">
                <div class="kpi-value text-ink">{{ number_format($fpt['pass_rate'], 1) }}<span class="text-lg text-ink-soft">%</span></div>
                <div class="w-24 opacity-90">{!! $spark($sparks['fpt_pass'], '#10B981', 'rgba(16,185,129,.10)') !!}</div>
            </div>
            <p class="text-[11px] text-ink-soft mt-1 mono">{{ $fpt['passed'] }}/{{ $fpt['total'] }} executions</p>
        </a>
        <a href="{{ route('work-orders.index') }}?filter=sla_breached" class="card kpi group hover:border-amber-300 transition-colors">
            <p class="label-kicker flex items-center justify-between">
                <span class="flex items-center gap-1.5">SLA Breaches @if($slaBreached > 0)<span class="dot dot-warn"></span>@endif</span>
                <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 text-amber-600 transition" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </p>
            <div class="flex items-end justify-between gap-3 mt-2">
                <div class="kpi-value {{ $slaBreached > 0 ? 'text-red-600' : 'text-ink' }}">{{ str_pad($slaBreached, 2, '0', STR_PAD_LEFT) }}</div>
                <div class="w-24 opacity-90">{!! $spark($sparks['sla_breaches'], '#F59E0B', 'rgba(245,158,11,.10)') !!}</div>
            </div>
            <p class="text-[11px] text-ink-soft mt-1 mono">{{ $this->kpis['open_work_orders'] }} open work orders</p>
        </a>
    </div>

    {{-- Readiness heatmap strip --}}
    <div class="card p-5">
        <div class="flex items-center justify-between mb-3">
            <div>
                <p class="label-kicker">Portfolio Heatmap</p>
                <p class="text-[12px] text-ink-muted mt-0.5">Hover a tile for details — click to open the project.</p>
            </div>
            <div class="flex items-center gap-3 text-[10px] mono text-ink-soft">
                <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-red-200"></span>&lt;60</span>
                <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-amber-200"></span>60-79</span>
                <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-emerald-200"></span>80-94</span>
                <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-emerald-500"></span>95+</span>
            </div>
        </div>
        <div class="flex flex-wrap gap-1.5">
            @foreach($this->projects as $project)
                @php
                    $s = (float) $project->readiness_score;
                    $bg = $s >= 95 ? 'bg-emerald-500' : ($s >= 80 ? 'bg-emerald-200' : ($s >= 60 ? 'bg-amber-200' : 'bg-red-200'));
                @endphp
                <a href="{{ route('projects.show', $project->id) }}"
                   class="group relative"
                   title="{{ $project->name }} · {{ number_format($s, 1) }}%">
                    <div class="w-10 h-10 rounded-md {{ $bg }} transition-transform group-hover:scale-110 flex items-center justify-center">
                        <span class="text-[10px] font-bold text-ink/80 mono">{{ round($s) }}</span>
                    </div>
                    <div class="pointer-events-none absolute left-1/2 -translate-x-1/2 bottom-full mb-1 whitespace-nowrap bg-ink text-white text-[10px] mono px-1.5 py-0.5 rounded opacity-0 group-hover:opacity-100 transition">
                        {{ \Illuminate\Support\Str::limit($project->name, 30) }}
                    </div>
                </a>
            @endforeach
            @if($this->projects->isEmpty())
                <p class="text-[12px] text-ink-soft">No projects yet.</p>
            @endif
        </div>
    </div>

    {{-- Velocity chart + deficiency mix --}}
    @php
        $mix = $this->deficiencyMix;
        $order = ['critical' => '#DC2626', 'emergency' => '#B91C1C', 'high' => '#F59E0B', 'medium' => '#6366F1', 'low' => '#94A3B8'];
        $mixData = collect($order)->map(fn ($color, $key) => ['key' => $key, 'color' => $color, 'count' => (int) ($mix[$key] ?? 0)])->filter(fn ($r) => $r['count'] > 0)->values();
        $mixTotal = max(1, $mixData->sum('count'));
        // donut segments
        $circ = 2 * pi() * 40;
        $offset = 0;
        $segments = [];
        foreach ($mixData as $row) {
            $len = ($row['count'] / $mixTotal) * $circ;
            $segments[] = ['color' => $row['color'], 'len' => $len, 'gap' => $circ - $len, 'offset' => $offset, 'key' => $row['key'], 'count' => $row['count']];
            $offset -= $len;
        }
    @endphp
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="card lg:col-span-2 p-5">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h2 class="text-[15px] font-semibold text-ink">14-Day Commissioning Velocity</h2>
                    <p class="text-[12px] text-ink-muted">FPT completions vs new deficiencies opened, daily.</p>
                </div>
                <div class="flex items-center gap-4 text-[11px]">
                    <span class="flex items-center gap-1.5 text-ink-muted"><span class="w-3 h-1 rounded-full bg-emerald-500"></span>FPT pass</span>
                    <span class="flex items-center gap-1.5 text-ink-muted"><span class="w-3 h-1 rounded-full bg-red-500"></span>Deficiencies</span>
                </div>
            </div>
            <div class="relative h-56">
                <canvas id="velocityChart" wire:ignore></canvas>
            </div>
            <script>
                (() => {
                    const run = () => {
                        const el = document.getElementById('velocityChart');
                        if (!el || !window.Chart) return;
                        if (el._chart) el._chart.destroy();
                        const fpt = @json($sparks['fpt_pass']);
                        const def = @json($sparks['deficiencies']);
                        const labels = Array.from({ length: fpt.length }, (_, i) => {
                            const d = new Date(); d.setDate(d.getDate() - (fpt.length - 1 - i));
                            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                        });
                        el._chart = new Chart(el, {
                            type: 'line',
                            data: {
                                labels,
                                datasets: [
                                    { label: 'FPT pass', data: fpt, borderColor: '#10B981', backgroundColor: 'rgba(16,185,129,.08)', tension: .35, fill: true, borderWidth: 2, pointRadius: 0, pointHoverRadius: 4 },
                                    { label: 'Deficiencies', data: def, borderColor: '#EF4444', backgroundColor: 'rgba(239,68,68,.08)', tension: .35, fill: true, borderWidth: 2, pointRadius: 0, pointHoverRadius: 4 },
                                ]
                            },
                            options: {
                                maintainAspectRatio: false,
                                responsive: true,
                                interaction: { mode: 'index', intersect: false },
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        backgroundColor: '#0F172A', titleColor: '#fff', bodyColor: '#E5E7EB',
                                        titleFont: { size: 11, weight: 600 }, bodyFont: { size: 11, family: 'JetBrains Mono' },
                                        padding: 8, displayColors: true, boxPadding: 4, cornerRadius: 6,
                                    },
                                },
                                scales: {
                                    x: { grid: { display: false }, ticks: { color: '#94A3B8', font: { size: 10 } } },
                                    y: { grid: { color: '#F1F5F9' }, ticks: { color: '#94A3B8', font: { size: 10 }, precision: 0 }, border: { display: false } },
                                }
                            }
                        });
                    };
                    if (document.readyState !== 'loading') run();
                    else document.addEventListener('DOMContentLoaded', run);
                    document.addEventListener('livewire:navigated', run);
                })();
            </script>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h2 class="text-[15px] font-semibold text-ink">Deficiency Mix</h2>
                    <p class="text-[12px] text-ink-muted">Open issues by priority.</p>
                </div>
            </div>
            <div class="flex items-center gap-5">
                <div class="relative w-32 h-32 flex-shrink-0" x-data="{ hover: null }">
                    <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90">
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#F1F5F9" stroke-width="12"/>
                        @foreach($segments as $seg)
                            <circle cx="50" cy="50" r="40" fill="none"
                                stroke="{{ $seg['color'] }}"
                                stroke-width="12"
                                stroke-dasharray="{{ $seg['len'] }} {{ $seg['gap'] }}"
                                stroke-dashoffset="{{ $seg['offset'] }}"
                                class="transition-all cursor-pointer"
                                @mouseenter="hover='{{ $seg['key'] }}'" @mouseleave="hover=null"/>
                        @endforeach
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center flex-col pointer-events-none">
                        <div class="kpi-value text-ink leading-none" x-show="!hover">{{ (int) $mixTotal }}</div>
                        <div class="text-[9px] label-kicker mt-0.5" x-show="!hover">Open</div>
                        <template x-for="s in [1]" :key="s">
                            <div x-show="hover" class="text-center">
                                <div class="text-2xl font-bold text-ink tabular-nums" x-text="({{ json_encode($mixData->pluck('count', 'key')->toArray()) }})[hover]"></div>
                                <div class="text-[9px] label-kicker" x-text="hover"></div>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="flex-1 space-y-1.5">
                    @foreach($mixData as $row)
                        <a href="{{ route('deficiencies.index') }}?priority={{ $row['key'] }}" class="flex items-center gap-2 text-[12px] hover:bg-slate-50 rounded px-1.5 py-1 -mx-1.5">
                            <span class="w-2.5 h-2.5 rounded-sm shrink-0" style="background:{{ $row['color'] }}"></span>
                            <span class="flex-1 text-ink capitalize">{{ $row['key'] }}</span>
                            <span class="mono text-ink-soft tabular-nums">{{ $row['count'] }}</span>
                            <span class="mono text-ink-soft tabular-nums text-[10px] w-8 text-right">{{ round($row['count'] / $mixTotal * 100) }}%</span>
                        </a>
                    @endforeach
                    @if($mixData->isEmpty())
                        <p class="text-[12px] text-ink-soft flex items-center gap-1.5"><span class="dot dot-pass"></span>No open deficiencies.</p>
                    @endif
                </div>
            </div>
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
