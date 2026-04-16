<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turnover Package · {{ $project->name }}</title>
    @if(file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css'])
    @else
        {{-- Fallback CDN so the stakeholder preview still renders in local/CI where assets haven't been built. --}}
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <meta name="robots" content="noindex,nofollow">
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-indigo-50 text-gray-900 antialiased">
    @php
        $score = (float) ($payload['readiness_score'] ?? 0);
        $scoreColor = $score >= 85 ? 'emerald' : ($score >= 70 ? 'amber' : 'red');
        $fpt = $payload['fpt'] ?? [];
        $pfc = $payload['pfc'] ?? [];
        $blockers = $payload['handover_blockers'] ?? [];
        $assetCount = (int) ($payload['asset_count'] ?? 0);
        $issueCount = count($payload['outstanding_issues'] ?? []);
    @endphp

    <div class="mx-auto max-w-5xl px-6 py-10">

        {{-- Hero --}}
        <div class="rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 text-white p-8 shadow-xl relative overflow-hidden">
            <div class="absolute -top-10 -right-10 h-56 w-56 bg-white/10 rounded-full blur-3xl"></div>
            <div class="flex flex-wrap items-start justify-between gap-6 relative">
                <div>
                    <div class="text-[11px] uppercase tracking-[0.2em] font-bold text-indigo-200">Accelerated Turnover Package</div>
                    <h1 class="text-3xl font-extrabold mt-2">{{ $project->name }}</h1>
                    <p class="text-indigo-100 text-sm mt-1">{{ $project->tenant->name ?? '' }} · Shared on {{ now()->format('M d, Y') }}</p>
                </div>
                <div class="flex flex-col items-end gap-2">
                    <div class="rounded-xl bg-white/15 backdrop-blur px-5 py-3 text-center">
                        <div class="text-[10px] uppercase tracking-wider text-indigo-100 font-semibold">Readiness Score</div>
                        <div class="text-4xl font-extrabold tabular-nums text-white mt-1">{{ number_format($score, 1) }}</div>
                        <div class="text-[11px] text-indigo-100">{{ $payload['readiness_grade'] ?? 'out of 100' }}</div>
                    </div>
                    <a href="{{ $downloadUrl }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-white text-indigo-700 px-4 py-2 text-sm font-bold hover:bg-indigo-50 shadow-md">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        Download Full PDF
                    </a>
                </div>
            </div>
        </div>

        {{-- Trust bar --}}
        <div class="rounded-xl bg-white border border-gray-200 mt-6 p-4 flex flex-wrap items-center gap-6 text-xs text-gray-600">
            <div class="flex items-center gap-2">
                <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"/></svg>
                Signed link — tamper-proof & expires automatically
            </div>
            <div class="flex items-center gap-2">
                <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
                Every preview &amp; download is audit-logged
            </div>
            <div class="ml-auto flex items-center gap-2">
                <span class="inline-flex items-center rounded-full bg-{{ $scoreColor }}-100 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wide text-{{ $scoreColor }}-700">
                    {{ $score >= 85 ? 'Ready' : ($score >= 70 ? 'Nearly Ready' : 'In Progress') }}
                </span>
            </div>
        </div>

        {{-- KPI grid --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mt-6">
            @php
                $tiles = [
                    ['label' => 'Assets', 'value' => $assetCount, 'accent' => 'indigo'],
                    ['label' => 'PFC clean rate', 'value' => ($pfc['total'] ?? 0) > 0 ? number_format((float) ($pfc['clean_rate'] ?? 0), 1).'%' : '—', 'accent' => ($pfc['total'] ?? 0) > 0 ? 'indigo' : 'gray'],
                    ['label' => 'FPT Pass Rate', 'value' => ($fpt['execution_pass_rate'] ?? 0).'%', 'accent' => 'emerald'],
                    ['label' => 'Witnessed', 'value' => ($fpt['executions_witnessed'] ?? 0), 'accent' => 'purple'],
                    ['label' => 'Open Deficiencies', 'value' => $issueCount, 'accent' => $issueCount > 0 ? 'red' : 'gray'],
                ];
            @endphp
            @foreach($tiles as $t)
                <div class="rounded-xl bg-white border border-gray-200 p-5">
                    <div class="text-[11px] uppercase tracking-wider font-semibold text-gray-500">{{ $t['label'] }}</div>
                    <div class="mt-2 text-3xl font-extrabold tabular-nums text-{{ $t['accent'] }}-600">{{ $t['value'] }}</div>
                </div>
            @endforeach
        </div>

        {{-- FPT scorecard --}}
        @if(!empty($fpt) && ($fpt['executions_total'] ?? 0) > 0)
            <div class="rounded-xl bg-white border border-gray-200 mt-6 p-6">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Functional Performance Testing</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <div class="text-[11px] uppercase tracking-wider text-gray-500 font-semibold">Executions</div>
                        <div class="text-2xl font-bold tabular-nums text-gray-900">{{ $fpt['executions_total'] ?? 0 }}</div>
                    </div>
                    <div>
                        <div class="text-[11px] uppercase tracking-wider text-gray-500 font-semibold">Exec Pass Rate</div>
                        <div class="text-2xl font-bold tabular-nums text-emerald-600">{{ number_format((float) ($fpt['execution_pass_rate'] ?? 0), 1) }}%</div>
                    </div>
                    <div>
                        <div class="text-[11px] uppercase tracking-wider text-gray-500 font-semibold">Step Pass Rate</div>
                        <div class="text-2xl font-bold tabular-nums text-emerald-600">{{ number_format((float) ($fpt['step_pass_rate'] ?? 0), 1) }}%</div>
                    </div>
                    <div>
                        <div class="text-[11px] uppercase tracking-wider text-gray-500 font-semibold">Witnessed</div>
                        <div class="text-2xl font-bold tabular-nums text-indigo-600">{{ $fpt['executions_witnessed'] ?? 0 }}</div>
                    </div>
                </div>
                @if(!empty($fpt['by_level']))
                    <div class="space-y-2 border-t border-gray-100 pt-4">
                        @foreach($fpt['by_level'] as $lvl)
                            @php
                                $pct = (float) ($lvl['pass_rate'] ?? 0);
                                $tone = $pct >= 85 ? 'emerald' : ($pct >= 70 ? 'amber' : 'red');
                            @endphp
                            <div class="flex items-center gap-3 text-sm">
                                <span class="inline-flex items-center justify-center w-10 h-6 rounded bg-gray-100 text-gray-700 font-semibold text-xs">{{ $lvl['level'] }}</span>
                                <div class="flex-1 h-2 rounded-full bg-gray-100 overflow-hidden">
                                    <div class="h-full bg-{{ $tone }}-500" style="width: {{ $pct }}%;"></div>
                                </div>
                                <span class="text-xs text-gray-500 tabular-nums w-16 text-right">{{ $lvl['passed'] }}/{{ $lvl['total'] }}</span>
                                <span class="text-xs font-bold tabular-nums text-{{ $tone }}-600 w-14 text-right">{{ number_format($pct, 1) }}%</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        {{-- PFC scorecard --}}
        @if(!empty($pfc) && ($pfc['total'] ?? 0) > 0)
            <div class="rounded-xl bg-white border border-gray-200 mt-6 p-6">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Pre-Functional Checklists (L1 / L2)</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <div class="text-[11px] uppercase tracking-wider text-gray-500 font-semibold">Completions</div>
                        <div class="text-2xl font-bold tabular-nums text-gray-900">{{ $pfc['total'] ?? 0 }}</div>
                    </div>
                    <div>
                        <div class="text-[11px] uppercase tracking-wider text-gray-500 font-semibold">Clean rate</div>
                        <div class="text-2xl font-bold tabular-nums text-indigo-600">{{ number_format((float) ($pfc['clean_rate'] ?? 0), 1) }}%</div>
                    </div>
                    <div>
                        <div class="text-[11px] uppercase tracking-wider text-gray-500 font-semibold">Item pass rate</div>
                        <div class="text-2xl font-bold tabular-nums text-emerald-600">{{ number_format((float) ($pfc['item_pass_rate'] ?? 0), 1) }}%</div>
                    </div>
                    <div>
                        <div class="text-[11px] uppercase tracking-wider text-gray-500 font-semibold">In-flight</div>
                        <div class="text-2xl font-bold tabular-nums text-indigo-600">{{ $pfc['in_progress'] ?? 0 }}</div>
                    </div>
                </div>
                @if(!empty($pfc['by_level']))
                    <div class="space-y-2 border-t border-gray-100 pt-4">
                        @foreach($pfc['by_level'] as $lvl)
                            @php
                                $pct = (float) ($lvl['clean_rate'] ?? 0);
                                $tone = $pct >= 95 ? 'emerald' : ($pct >= 80 ? 'amber' : 'red');
                            @endphp
                            <div class="flex items-center gap-3 text-sm">
                                <span class="inline-flex items-center justify-center w-10 h-6 rounded bg-gray-100 text-gray-700 font-semibold text-xs">{{ $lvl['level'] }}</span>
                                <div class="flex-1 h-2 rounded-full bg-gray-100 overflow-hidden">
                                    <div class="h-full bg-{{ $tone }}-500" style="width: {{ $pct }}%;"></div>
                                </div>
                                <span class="text-xs text-gray-500 tabular-nums w-24 text-right">{{ $lvl['clean'] }}/{{ $lvl['total'] }} clean</span>
                                <span class="text-xs font-bold tabular-nums text-{{ $tone }}-600 w-14 text-right">{{ number_format($pct, 1) }}%</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        {{-- Blockers --}}
        @if(count($blockers) > 0)
            <div class="rounded-xl bg-white border border-red-200 mt-6 overflow-hidden">
                <div class="bg-red-50 border-b border-red-100 px-6 py-3">
                    <h2 class="text-sm font-semibold text-red-900">Outstanding Items Before Handover</h2>
                    <p class="text-xs text-red-700 mt-0.5">{{ count($blockers) }} item{{ count($blockers) === 1 ? '' : 's' }} gating final turnover.</p>
                </div>
                <ul class="divide-y divide-gray-100">
                    @foreach(array_slice($blockers, 0, 10) as $b)
                        <li class="px-6 py-3 flex items-start gap-3">
                            <span class="mt-1 inline-block h-2 w-2 rounded-full bg-red-500 flex-shrink-0"></span>
                            <span class="text-sm text-gray-800">{{ is_array($b) ? ($b['message'] ?? $b['title'] ?? '') : $b }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Footer --}}
        <div class="mt-10 text-center text-xs text-gray-400">
            This is a read-only stakeholder preview. For full interactive access, contact the project commissioning authority.
        </div>
    </div>
</body>
</html>
