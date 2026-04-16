@php
    $p = $this->payload;
    $fpt = $p['fpt'];
    $scorePct = (float) $p['readiness_score'];
    $scoreRing = $scorePct >= 95 ? 'text-emerald-500' : ($scorePct >= 80 ? 'text-amber-500' : 'text-red-500');
    $scoreBg = $scorePct >= 95 ? 'from-emerald-50 to-emerald-100' : ($scorePct >= 80 ? 'from-amber-50 to-amber-100' : 'from-red-50 to-red-100');
@endphp
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <div class="flex items-center gap-2 text-xs uppercase tracking-wider text-brand-600 font-semibold">
                <span>Accelerated Turnover Package</span>
                <span class="text-gray-400">·</span>
                <a href="{{ route('projects.show', $project->id) }}" wire:navigate class="text-gray-500 hover:text-brand-600">
                    {{ $project->name }}
                </a>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Handover Readiness</h1>
            <p class="text-sm text-gray-500 mt-1">Preview every data point the PDF will contain, then generate & attest the package for handoff to the operations team.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('projects.cx-matrix', $project->id) }}" wire:navigate
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Cx Matrix
            </a>
            <a href="{{ route('projects.turnover-package', $project->id) }}"
               class="inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                </svg>
                Download Package (PDF)
            </a>
        </div>
    </div>

    {{-- Stakeholder share link --}}
    <div x-data="{ copied: false }" class="rounded-xl bg-white border border-indigo-200 p-5">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div class="flex-1 min-w-[240px]">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/>
                    </svg>
                    <div class="text-sm font-semibold text-gray-900">Stakeholder Share Link</div>
                </div>
                <p class="text-xs text-gray-600 mt-1">Generate a signed, expiring link for the GC, owner-rep, or AHJ to preview readiness and download the PDF — no login required, every access audit-logged.</p>
            </div>
            <div class="flex items-center gap-2">
                <select wire:model="shareExpiryDays" class="rounded-lg border border-gray-300 bg-white px-2.5 py-2 text-sm">
                    <option value="7">Expires in 7d</option>
                    <option value="14">Expires in 14d</option>
                    <option value="30">Expires in 30d</option>
                    <option value="60">Expires in 60d</option>
                    <option value="90">Expires in 90d</option>
                </select>
                <button wire:click="generateShareLink"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-600 bg-white px-3.5 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">
                    Generate Link
                </button>
            </div>
        </div>
        @if($shareLink)
            <div class="mt-4 flex items-center gap-2">
                <input type="text" readonly value="{{ $shareLink }}"
                    x-ref="shareInput"
                    class="flex-1 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-mono text-gray-700" />
                <button type="button"
                    x-on:click="$refs.shareInput.select(); navigator.clipboard.writeText($refs.shareInput.value); copied = true; setTimeout(() => copied = false, 1800)"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                    <span x-show="!copied">Copy</span>
                    <span x-show="copied" x-cloak>Copied!</span>
                </button>
                <a href="{{ $shareLink }}" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Preview
                </a>
            </div>
        @endif
    </div>

    {{-- Score card + blockers --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="col-span-1 rounded-xl bg-gradient-to-br {{ $scoreBg }} border border-gray-200 p-6 flex flex-col items-center justify-center">
            <div class="text-xs uppercase tracking-wider text-gray-600 font-semibold">Readiness Score</div>
            <div class="text-6xl font-extrabold {{ $scoreRing }} tabular-nums mt-2">{{ number_format($scorePct, 0) }}<span class="text-3xl">%</span></div>
            <div class="text-lg font-bold text-gray-700 mt-1">Grade {{ $p['readiness_grade'] }}</div>
            <div class="text-xs text-gray-500 mt-3 text-center">
                Target Handover: <strong>{{ $project->target_handover_date?->format('M d, Y') ?? 'TBD' }}</strong>
                @if($project->actual_handover_date)
                    <div class="mt-0.5">Actual: <strong>{{ $project->actual_handover_date->format('M d, Y') }}</strong></div>
                @endif
            </div>
        </div>

        <div class="col-span-2 rounded-xl bg-white border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-gray-900">Handover Blockers</div>
                <span class="text-xs text-gray-500">{{ count($p['handover_blockers']) }} open</span>
            </div>
            @if(count($p['handover_blockers']) === 0)
                <div class="mt-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-6 text-center">
                    <div class="text-emerald-700 font-semibold">No blockers</div>
                    <div class="text-xs text-emerald-600 mt-1">This project is ready to hand over.</div>
                </div>
            @else
                <ul class="mt-3 space-y-2">
                    @foreach($p['handover_blockers'] as $blocker)
                        <li class="flex items-center gap-3 text-sm rounded-lg border border-red-100 bg-red-50/60 px-3 py-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                            <span class="flex-1 text-red-900">{{ $blocker['label'] }}</span>
                            @if(! empty($blocker['count']))
                                <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-700 uppercase tracking-wide">{{ $blocker['count'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- FPT scorecard --}}
    @if($fpt['executions_total'] > 0)
        <div class="rounded-xl bg-white border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3">
                <div>
                    <div class="text-sm font-semibold text-gray-900">Commissioning Performance</div>
                    <div class="text-xs text-gray-500">FPT execution + witness + step-level statistics</div>
                </div>
                <a href="{{ route('fpt.executions.index', ['projectFilter' => $project->id]) }}" wire:navigate
                   class="text-xs font-semibold text-brand-600 hover:text-brand-700">View all executions →</a>
            </div>
            @php
                $metrics = [
                    ['label' => 'Executions', 'value' => $fpt['executions_total'], 'detail' => $fpt['executions_passed'].' passed · '.$fpt['executions_failed'].' failed'],
                    ['label' => 'Pass Rate', 'value' => number_format($fpt['execution_pass_rate'], 1).'%', 'detail' => 'executions'],
                    ['label' => 'Step Pass Rate', 'value' => number_format($fpt['step_pass_rate'], 1).'%', 'detail' => $fpt['step_passed'].' of '.$fpt['step_total'].' steps'],
                    ['label' => 'Witnessed', 'value' => $fpt['executions_witnessed'].' / '.$fpt['executions_total'], 'detail' => 'signed attestations'],
                ];
            @endphp
            <div class="grid grid-cols-2 md:grid-cols-4 gap-0 border-b border-gray-100">
                @foreach($metrics as $m)
                    <div class="px-5 py-4 border-r border-gray-100 last:border-r-0 last:md:border-r-0">
                        <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold">{{ $m['label'] }}</div>
                        <div class="text-2xl font-extrabold text-gray-900 tabular-nums mt-1">{{ $m['value'] }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">{{ $m['detail'] }}</div>
                    </div>
                @endforeach
            </div>
            @if(count($fpt['by_level']) > 0)
                <div class="px-5 py-4">
                    <div class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">By Cx Level</div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2">
                        @foreach($fpt['by_level'] as $lvl)
                            @php
                                $rate = $lvl['pass_rate'];
                                $chipClass = $rate >= 95 ? 'bg-emerald-100 text-emerald-800' : ($rate >= 80 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800');
                            @endphp
                            <div class="rounded-lg border border-gray-200 bg-gray-50/40 px-3 py-2">
                                <div class="flex items-center justify-between">
                                    <div class="text-xs font-bold text-gray-700">{{ $lvl['level'] }}</div>
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full {{ $chipClass }}">{{ number_format($rate, 0) }}%</span>
                                </div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ $lvl['passed'] }}✓ / {{ $lvl['failed'] }}✗ of {{ $lvl['total'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Package contents summary + history --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 rounded-xl bg-white border border-gray-200 overflow-hidden">
            <div class="border-b border-gray-100 px-5 py-3">
                <div class="text-sm font-semibold text-gray-900">Package Contents</div>
                <div class="text-xs text-gray-500">Everything the PDF will include, sourced live from this workspace.</div>
            </div>
            @php
                $contents = [
                    ['label' => 'Asset inventory', 'count' => $p['asset_count'], 'suffix' => 'assets with QR codes, warranty & PM data'],
                    ['label' => 'Closeout requirements', 'count' => collect($p['closeout_by_category'])->sum('total'), 'suffix' => collect($p['closeout_by_category'])->sum('completed').' complete across '.count($p['closeout_by_category']).' categories'],
                    ['label' => 'Outstanding punch list', 'count' => count($p['outstanding_issues']), 'suffix' => 'open deficiencies'],
                    ['label' => 'Commissioning tests (WOs)', 'count' => count($p['completed_tests']), 'suffix' => 'completed inspection work orders'],
                    ['label' => 'FPT executions', 'count' => $fpt['executions_total'], 'suffix' => 'with full step-level audit trail'],
                    ['label' => 'Turnover documents', 'count' => count($p['documents']), 'suffix' => 'attached files'],
                ];
            @endphp
            <dl class="divide-y divide-gray-100 text-sm">
                @foreach($contents as $c)
                    <div class="flex items-center gap-4 px-5 py-3">
                        <dt class="text-gray-700 font-medium flex-1">{{ $c['label'] }}</dt>
                        <dd class="font-mono text-xs font-bold text-gray-900 tabular-nums">{{ $c['count'] }}</dd>
                        <dd class="text-xs text-gray-500 hidden sm:block sm:w-64 text-right">{{ $c['suffix'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        <div class="rounded-xl bg-white border border-gray-200 overflow-hidden">
            <div class="border-b border-gray-100 px-5 py-3">
                <div class="text-sm font-semibold text-gray-900">Generation History</div>
                <div class="text-xs text-gray-500">Last 10 downloads of this package.</div>
            </div>
            @if(count($this->history) === 0)
                <div class="px-5 py-8 text-center text-xs text-gray-500">
                    This package has not been generated yet. Click <strong>Download Package</strong> above to create the first revision.
                </div>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach($this->history as $h)
                        <li class="px-5 py-3">
                            <div class="flex items-center justify-between">
                                <div class="text-xs font-semibold text-gray-900 truncate">{{ $h['filename'] ?? 'Turnover Package' }}</div>
                                @if($h['readiness_score'] !== null)
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-bold text-gray-700 tabular-nums">
                                        {{ number_format((float) $h['readiness_score'], 0) }}%
                                    </span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                {{ $h['generated_at'] ?? '—' }}@if($h['generated_by']) · by {{ $h['generated_by'] }}@endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
