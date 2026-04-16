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

    $passColor = $h['pass_rate'] >= 95 ? 'emerald' : ($h['pass_rate'] >= 80 ? 'amber' : 'red');
    $witnessColor = $h['witness_coverage'] >= 80 ? 'emerald' : ($h['witness_coverage'] >= 50 ? 'amber' : 'red');

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

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-3">
        <select wire:model.live="projectFilter" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
            <option value="">All Projects</option>
            @foreach($this->projects as $project)
                <option value="{{ $project->id }}">{{ $project->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="lookbackMonths" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
            <option value="3">Last 3 months</option>
            <option value="6">Last 6 months</option>
            <option value="9">Last 9 months</option>
            <option value="12">Last 12 months</option>
        </select>
        <span class="ml-auto text-xs text-gray-500">Data refreshes live with every action.</span>
    </div>

    {{-- Headline KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        @php
            $cards = [
                ['label' => 'Executions', 'value' => $h['total'], 'sub' => $h['passed'].' passed · '.$h['failed'].' failed', 'color' => 'text-gray-900'],
                ['label' => 'Pass Rate', 'value' => number_format((float) $h['pass_rate'], 1).'%', 'sub' => 'of completed', 'color' => 'text-'.$passColor.'-600'],
                ['label' => 'Witness Coverage', 'value' => number_format((float) $h['witness_coverage'], 1).'%', 'sub' => $h['witnessed'].' signed', 'color' => 'text-'.$witnessColor.'-600'],
                ['label' => 'Retests', 'value' => $h['retests'], 'sub' => 'corrective runs', 'color' => 'text-indigo-600'],
                ['label' => 'Deficiencies', 'value' => array_sum(array_column($aging, 'count')), 'sub' => 'open', 'color' => 'text-red-600'],
            ];
        @endphp
        @foreach($cards as $c)
            <div class="rounded-xl border border-gray-200 bg-white px-5 py-4">
                <div class="text-[11px] uppercase tracking-wider font-semibold text-gray-500">{{ $c['label'] }}</div>
                <div class="mt-1 text-3xl font-extrabold tabular-nums {{ $c['color'] }}">{{ $c['value'] }}</div>
                <div class="text-xs text-gray-500 mt-0.5">{{ $c['sub'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Pre-functional checklists (L1/L2) --}}
    @if(($pfc['total'] ?? 0) > 0)
        @php
            $pfcClean = (float) ($pfc['clean_rate'] ?? 0);
            $pfcCleanColor = $pfcClean >= 95 ? 'emerald' : ($pfcClean >= 80 ? 'amber' : 'red');
        @endphp
        <div class="rounded-xl bg-white border border-indigo-200 p-5">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                <div>
                    <div class="text-sm font-semibold text-gray-900">Pre-Functional Checklists</div>
                    <div class="text-xs text-gray-500">L1 / L2 asset readiness before functional testing (same filter as above)</div>
                </div>
                <div class="text-right">
                    <div class="text-[11px] uppercase tracking-wider text-gray-500 font-semibold">Clean rate</div>
                    <div class="text-2xl font-extrabold tabular-nums text-{{ $pfcCleanColor }}-600">{{ number_format($pfcClean, 1) }}%</div>
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-4">
                @foreach([
                    ['label' => 'Completions', 'value' => $pfc['total'], 'tone' => 'text-gray-900'],
                    ['label' => 'Clean', 'value' => $pfc['completed'], 'tone' => 'text-emerald-600'],
                    ['label' => 'W/ gaps', 'value' => $pfc['failed'], 'tone' => 'text-amber-600'],
                    ['label' => 'In-flight', 'value' => $pfc['in_progress'], 'tone' => 'text-indigo-600'],
                    ['label' => 'Item pass', 'value' => number_format((float) ($pfc['item_pass_rate'] ?? 0), 1).'%', 'tone' => 'text-emerald-700'],
                    ['label' => 'Done', 'value' => number_format((float) ($pfc['completion_rate'] ?? 0), 1).'%', 'tone' => 'text-gray-800'],
                ] as $row)
                    <div class="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2">
                        <div class="text-[10px] uppercase tracking-wider text-gray-500 font-semibold">{{ $row['label'] }}</div>
                        <div class="text-lg font-bold tabular-nums {{ $row['tone'] }}">{{ $row['value'] }}</div>
                    </div>
                @endforeach
            </div>
            @if(!empty($pfc['by_level']))
                <div class="space-y-2 border-t border-gray-100 pt-4">
                    <div class="text-xs font-semibold text-gray-700 mb-2">By Cx level</div>
                    @foreach($pfc['by_level'] as $lvl)
                        @php
                            $pct = (float) ($lvl['clean_rate'] ?? 0);
                            $tone = $pct >= 95 ? 'emerald' : ($pct >= 80 ? 'amber' : 'red');
                        @endphp
                        <div class="flex items-center gap-3 text-sm">
                            <span class="inline-flex items-center justify-center min-w-[2.5rem] h-6 rounded bg-gray-100 text-gray-700 font-semibold text-xs">{{ $lvl['level'] }}</span>
                            <div class="flex-1 h-2 rounded-full bg-gray-100 overflow-hidden">
                                <div class="h-full bg-{{ $tone }}-500" style="width: {{ min(100, $pct) }}%;"></div>
                            </div>
                            <span class="text-xs text-gray-500 tabular-nums w-20 text-right">{{ $lvl['clean'] }}/{{ $lvl['total'] }} clean</span>
                            <span class="text-xs font-bold tabular-nums text-{{ $tone }}-600 w-14 text-right">{{ number_format($pct, 1) }}%</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    {{-- Trend chart --}}
    <div class="rounded-xl bg-white border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <div>
                <div class="text-sm font-semibold text-gray-900">Pass Rate & Volume Trend</div>
                <div class="text-xs text-gray-500">Monthly execution volume, pass rate %, and witness coverage %</div>
            </div>
        </div>
        <div class="relative" style="height: 280px;">
            <canvas x-ref="trendChart"></canvas>
        </div>
    </div>

    {{-- Two columns: top failing scripts + cx level + aging --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 rounded-xl bg-white border border-gray-200 overflow-hidden">
            <div class="border-b border-gray-100 px-5 py-3">
                <div class="text-sm font-semibold text-gray-900">Top Failing Scripts</div>
                <div class="text-xs text-gray-500">Where your deficiencies cluster — address these first to lift overall pass rate.</div>
            </div>
            @if(count($topFailing) === 0)
                <div class="px-5 py-10 text-center text-sm text-gray-500">
                    No failures in the current filter — every completed execution passed.
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-[10px] uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="text-left px-5 py-2 font-semibold">Script</th>
                            <th class="text-left px-5 py-2 font-semibold">System</th>
                            <th class="text-left px-5 py-2 font-semibold">Runs</th>
                            <th class="text-left px-5 py-2 font-semibold">Failures</th>
                            <th class="text-left px-5 py-2 font-semibold">Fail Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($topFailing as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3">
                                    <div class="font-semibold text-gray-900">{{ $row['name'] }}</div>
                                    @if($row['cx_level'])
                                        <div class="text-[10px] uppercase tracking-wider text-gray-500">{{ $row['cx_level'] }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-500">{{ $row['system_type'] ?? '—' }}</td>
                                <td class="px-5 py-3 text-gray-700 tabular-nums">{{ $row['runs'] }}</td>
                                <td class="px-5 py-3 text-red-600 tabular-nums font-semibold">{{ $row['failed'] }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="h-1.5 flex-1 max-w-[120px] overflow-hidden rounded-full bg-gray-100">
                                            <div class="h-full bg-red-500" style="width: {{ $row['fail_rate'] }}%;"></div>
                                        </div>
                                        <span class="text-xs font-semibold text-red-600 tabular-nums">{{ number_format((float) $row['fail_rate'], 1) }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="rounded-xl bg-white border border-gray-200 overflow-hidden">
            <div class="border-b border-gray-100 px-5 py-3">
                <div class="text-sm font-semibold text-gray-900">Deficiency Aging</div>
                <div class="text-xs text-gray-500">Open issues bucketed by age</div>
            </div>
            <ul class="divide-y divide-gray-100">
                @foreach($aging as $bucket)
                    <li class="flex items-center gap-3 px-5 py-3">
                        <div class="flex-1">
                            <div class="text-sm text-gray-800 font-medium">{{ $bucket['label'] }}</div>
                            @if($bucket['critical'] > 0)
                                <div class="text-[11px] text-red-600 font-semibold mt-0.5">{{ $bucket['critical'] }} high/critical</div>
                            @endif
                        </div>
                        <div class="text-2xl font-extrabold tabular-nums text-gray-900">{{ $bucket['count'] }}</div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Cx Level distribution chart --}}
    @if(count($byLevel) > 0)
        <div class="rounded-xl bg-white border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <div class="text-sm font-semibold text-gray-900">Executions by Cx Level</div>
                    <div class="text-xs text-gray-500">ASHRAE Guideline 0 — depth of commissioning programme</div>
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
                                    backgroundColor: 'rgba(99, 102, 241, 0.25)',
                                    borderColor: 'rgb(99, 102, 241)',
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
                                    borderColor: 'rgb(168, 85, 247)',
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
