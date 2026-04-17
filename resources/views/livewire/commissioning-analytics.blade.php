@php
    $h = $this->headline;
    $pfc = $this->pfcSnapshot;
    $trend = $this->monthlyTrend;
    $topFailing = $this->topFailingScripts;
    $byLevel = $this->byCxLevel;
    $aging = $this->deficiencyAging;

    $passLabels = array_map(fn ($b) => $b['label'], $trend);
    $passValues = array_map(fn ($b) => $b['pass_rate'], $trend);
    $volume = array_map(fn ($b) => $b['total'], $trend);
    $witnessSeries = array_map(fn ($b) => $b['witness_coverage'], $trend);

    $passTone = $h['pass_rate'] >= 95 ? 'text-emerald-700' : ($h['pass_rate'] >= 80 ? 'text-amber-600' : 'text-red-700');
    $witnessTone = $h['witness_coverage'] >= 80 ? 'text-emerald-700' : ($h['witness_coverage'] >= 50 ? 'text-amber-600' : 'text-red-700');

    $levelLabels = array_map(fn ($r) => $r['level'], $byLevel);
    $levelPassed = array_map(fn ($r) => $r['passed'], $byLevel);
    $levelFailed = array_map(fn ($r) => $r['failed'], $byLevel);
@endphp
<div class="space-y-6"
     x-data="cxAnalytics({
        trendLabels: @js($passLabels),
        passRate: @js($passValues),
        volume: @js($volume),
        witnessCoverage: @js($witnessSeries),
        levelLabels: @js($levelLabels),
        levelPassed: @js($levelPassed),
        levelFailed: @js($levelFailed),
     })"
     x-init="$nextTick(() => render())">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <p class="label-kicker">Commissioning · Analytics</p>
            <h1 class="text-2xl font-bold tracking-tight text-ink mt-1">Commissioning Analytics</h1>
            <p class="text-[13px] text-ink-muted mt-0.5">Trend, level breakdown and deficiency aging across your Cx programme.</p>
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
            <select wire:model.live="lookbackMonths" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-[13px]">
                <option value="3">Last 3 months</option>
                <option value="6">Last 6 months</option>
                <option value="9">Last 9 months</option>
                <option value="12">Last 12 months</option>
            </select>
            <span class="ml-auto text-[11px] text-ink-soft">Data refreshes live with every action.</span>
        </div>
    </div>

    {{-- Headline KPIs --}}
    @php
        $cards = [
            ['label' => 'Executions', 'value' => $h['total'], 'sub' => $h['passed'].' passed · '.$h['failed'].' failed', 'tone' => 'text-ink'],
            ['label' => 'Pass Rate', 'value' => number_format((float) $h['pass_rate'], 1).'%', 'sub' => 'of completed', 'tone' => $passTone],
            ['label' => 'Witness Coverage', 'value' => number_format((float) $h['witness_coverage'], 1).'%', 'sub' => $h['witnessed'].' signed', 'tone' => $witnessTone],
            ['label' => 'Retests', 'value' => $h['retests'], 'sub' => 'corrective runs', 'tone' => 'text-accent-700'],
            ['label' => 'Deficiencies', 'value' => array_sum(array_column($aging, 'count')), 'sub' => 'open', 'tone' => 'text-red-700'],
        ];
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        @foreach($cards as $c)
            <div class="card kpi">
                <p class="label-kicker">{{ $c['label'] }}</p>
                <div class="kpi-value {{ $c['tone'] }} mt-2">{{ $c['value'] }}</div>
                <p class="text-[11px] text-ink-soft mt-1 mono">{{ $c['sub'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Pre-functional checklists (L1/L2) --}}
    @if(($pfc['total'] ?? 0) > 0)
        @php
            $pfcClean = (float) ($pfc['clean_rate'] ?? 0);
            $pfcCleanTone = $pfcClean >= 95 ? 'text-emerald-700' : ($pfcClean >= 80 ? 'text-amber-600' : 'text-red-700');
        @endphp
        <div class="card p-5">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-[15px] font-semibold text-ink">Pre-Functional Checklists</h2>
                    <p class="text-[12px] text-ink-muted">L1 / L2 asset readiness before functional testing (same filter as above)</p>
                </div>
                <div class="text-right">
                    <p class="label-kicker">Clean rate</p>
                    <div class="kpi-value {{ $pfcCleanTone }} mt-1">{{ number_format($pfcClean, 1) }}<span class="text-lg text-ink-soft">%</span></div>
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-4">
                @foreach([
                    ['label' => 'Completions', 'value' => $pfc['total'], 'tone' => 'text-ink'],
                    ['label' => 'Clean', 'value' => $pfc['completed'], 'tone' => 'text-emerald-700'],
                    ['label' => 'W/ gaps', 'value' => $pfc['failed'], 'tone' => 'text-amber-600'],
                    ['label' => 'In-flight', 'value' => $pfc['in_progress'], 'tone' => 'text-accent-700'],
                    ['label' => 'Item pass', 'value' => number_format((float) ($pfc['item_pass_rate'] ?? 0), 1).'%', 'tone' => 'text-emerald-700'],
                    ['label' => 'Done', 'value' => number_format((float) ($pfc['completion_rate'] ?? 0), 1).'%', 'tone' => 'text-ink'],
                ] as $row)
                    <div class="rounded-lg hairline px-3 py-2">
                        <p class="label-kicker">{{ $row['label'] }}</p>
                        <div class="text-lg font-bold tabular-nums mt-0.5 {{ $row['tone'] }}">{{ $row['value'] }}</div>
                    </div>
                @endforeach
            </div>
            @if(!empty($pfc['by_level']))
                <div class="space-y-2 hairline-t pt-4">
                    <p class="label-kicker mb-2">By Cx level</p>
                    @foreach($pfc['by_level'] as $lvl)
                        @php
                            $pct = (float) ($lvl['clean_rate'] ?? 0);
                            $toneBar = $pct >= 95 ? 'bg-emerald-500' : ($pct >= 80 ? 'bg-amber-500' : 'bg-red-500');
                            $toneTxt = $pct >= 95 ? 'text-emerald-700' : ($pct >= 80 ? 'text-amber-600' : 'text-red-700');
                        @endphp
                        <div class="flex items-center gap-3 text-[13px]">
                            <span class="chip chip-pending">{{ $lvl['level'] }}</span>
                            <div class="flex-1 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full {{ $toneBar }}" style="width: {{ min(100, $pct) }}%;"></div>
                            </div>
                            <span class="mono text-[11px] text-ink-soft tabular-nums w-20 text-right">{{ $lvl['clean'] }}/{{ $lvl['total'] }} clean</span>
                            <span class="mono text-[12px] font-semibold tabular-nums {{ $toneTxt }} w-14 text-right">{{ number_format($pct, 1) }}%</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    {{-- Trend chart --}}
    <div class="card p-5">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h2 class="text-[15px] font-semibold text-ink">Pass Rate &amp; Volume Trend</h2>
                <p class="text-[12px] text-ink-muted">Monthly execution volume, pass rate %, and witness coverage %</p>
            </div>
        </div>
        <div class="relative" style="height: 280px;">
            <canvas x-ref="trendChart"></canvas>
        </div>
    </div>

    {{-- Two columns: top failing scripts + aging --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 card overflow-hidden">
            <div class="hairline-b px-5 py-3">
                <h2 class="text-[15px] font-semibold text-ink">Top Failing Scripts</h2>
                <p class="text-[12px] text-ink-muted">Where your deficiencies cluster — address these first to lift overall pass rate.</p>
            </div>
            @if(count($topFailing) === 0)
                <div class="px-5 py-10 text-center text-[13px] text-ink-muted">
                    No failures in the current filter — every completed execution passed.
                </div>
            @else
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="hairline-b">
                            <th class="text-left px-5 py-2 label-kicker">Script</th>
                            <th class="text-left px-5 py-2 label-kicker">System</th>
                            <th class="text-left px-5 py-2 label-kicker">Runs</th>
                            <th class="text-left px-5 py-2 label-kicker">Failures</th>
                            <th class="text-left px-5 py-2 label-kicker">Fail Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topFailing as $row)
                            <tr class="hairline-b last:border-b-0 hover:bg-slate-50/60">
                                <td class="px-5 py-3">
                                    <div class="font-semibold text-ink">{{ $row['name'] }}</div>
                                    @if($row['cx_level'])
                                        <div class="mt-0.5"><span class="chip chip-pending">{{ $row['cx_level'] }}</span></div>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-[12px] text-ink-soft mono">{{ $row['system_type'] ?? '—' }}</td>
                                <td class="px-5 py-3 text-ink mono tabular-nums">{{ $row['runs'] }}</td>
                                <td class="px-5 py-3 text-red-700 mono tabular-nums font-semibold">{{ $row['failed'] }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="h-1.5 flex-1 max-w-[120px] overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full bg-red-500" style="width: {{ $row['fail_rate'] }}%;"></div>
                                        </div>
                                        <span class="mono text-[12px] font-semibold text-red-700 tabular-nums">{{ number_format((float) $row['fail_rate'], 1) }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="card overflow-hidden">
            <div class="hairline-b px-5 py-3">
                <h2 class="text-[15px] font-semibold text-ink">Deficiency Aging</h2>
                <p class="text-[12px] text-ink-muted">Open issues bucketed by age</p>
            </div>
            <ul>
                @foreach($aging as $bucket)
                    <li class="flex items-center gap-3 px-5 py-3 hairline-b last:border-b-0">
                        <div class="flex-1">
                            <div class="text-[13px] text-ink font-semibold">{{ $bucket['label'] }}</div>
                            @if($bucket['critical'] > 0)
                                <div class="mt-1"><span class="chip chip-fail">{{ $bucket['critical'] }} high/critical</span></div>
                            @endif
                        </div>
                        <div class="text-2xl font-extrabold tabular-nums text-ink mono">{{ $bucket['count'] }}</div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Cx Level distribution chart --}}
    @if(count($byLevel) > 0)
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h2 class="text-[15px] font-semibold text-ink">Executions by Cx Level</h2>
                    <p class="text-[12px] text-ink-muted">ASHRAE Guideline 0 — depth of commissioning programme</p>
                </div>
            </div>
            <div class="relative" style="height: 240px;">
                <canvas x-ref="levelChart"></canvas>
            </div>
        </div>
    @endif
</div>

<script>
    function cxAnalytics(data) {
        return {
            trendChart: null,
            levelChart: null,
            render() {
                this.$nextTick(() => {
                    if (this.trendChart) this.trendChart.destroy();
                    if (this.levelChart) this.levelChart.destroy();
                    this.trendChart = new Chart(this.$refs.trendChart, {
                        type: 'bar',
                        data: {
                            labels: data.trendLabels,
                            datasets: [
                                {
                                    type: 'bar',
                                    label: 'Executions',
                                    data: data.volume,
                                    backgroundColor: 'rgba(79, 70, 229, 0.25)',
                                    borderColor: 'rgb(79, 70, 229)',
                                    borderWidth: 1,
                                    yAxisID: 'yVolume',
                                },
                                {
                                    type: 'line',
                                    label: 'Pass Rate %',
                                    data: data.passRate,
                                    borderColor: 'rgb(16, 185, 129)',
                                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                    tension: 0.3,
                                    fill: true,
                                    yAxisID: 'yPct',
                                },
                                {
                                    type: 'line',
                                    label: 'Witness Coverage %',
                                    data: data.witnessCoverage,
                                    borderColor: 'rgb(79, 70, 229)',
                                    backgroundColor: 'transparent',
                                    tension: 0.3,
                                    borderDash: [5, 4],
                                    yAxisID: 'yPct',
                                },
                            ],
                        },
                        options: {
                            maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            plugins: { legend: { position: 'top', align: 'end' } },
                            scales: {
                                yPct: {
                                    type: 'linear',
                                    position: 'left',
                                    min: 0, max: 100,
                                    title: { display: true, text: 'Rate (%)' },
                                },
                                yVolume: {
                                    type: 'linear',
                                    position: 'right',
                                    beginAtZero: true,
                                    title: { display: true, text: 'Executions' },
                                    grid: { drawOnChartArea: false },
                                },
                            },
                        },
                    });

                    if (this.$refs.levelChart) {
                        this.levelChart = new Chart(this.$refs.levelChart, {
                            type: 'bar',
                            data: {
                                labels: data.levelLabels,
                                datasets: [
                                    { label: 'Passed', data: data.levelPassed, backgroundColor: 'rgba(16,185,129,0.85)' },
                                    { label: 'Failed', data: data.levelFailed, backgroundColor: 'rgba(239,68,68,0.85)' },
                                ],
                            },
                            options: {
                                maintainAspectRatio: false,
                                scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } },
                                plugins: { legend: { position: 'top', align: 'end' } },
                            },
                        });
                    }
                });
            },
        };
    }
</script>
