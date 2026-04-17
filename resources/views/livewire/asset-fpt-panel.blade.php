<div class="card p-5">
    <div class="flex items-start justify-between mb-5 flex-wrap gap-3">
        <div>
            <p class="label-kicker">Commissioning</p>
            <h2 class="text-[15px] font-semibold text-ink mt-0.5">Functional Performance Tests</h2>
            <p class="text-[12px] text-ink-muted">Cx test execution history for this asset with full audit trail.</p>
        </div>
        <div class="flex items-center gap-2">
            <select wire:model="scriptToRun"
                class="rounded-md border border-gray-300 bg-white px-3 py-2 text-[13px] min-w-[240px]">
                <option value="">Select test script…</option>
                @foreach($this->availableScripts as $script)
                    <option value="{{ $script->id }}">
                        {{ $script->name }} · v{{ $script->version }}
                        @if($script->is_system) (system) @endif
                    </option>
                @endforeach
            </select>
            <button wire:click="runScript"
                @if(! $scriptToRun) disabled @endif
                class="btn-primary inline-flex items-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Run FPT
            </button>
        </div>
    </div>

    @if($this->availableScripts->isEmpty())
        <div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-[13px] text-amber-800 mb-4">
            No published test scripts available for system type
            <span class="font-semibold mono">{{ $asset->system_type ?? '—' }}</span>.
            Visit the <a href="{{ route('fpt.scripts.index') }}" wire:navigate class="underline font-semibold">script library</a> to author or seed one.
        </div>
    @endif

    @php
        // Roll-up mini-stats
        $exAll = $this->executions;
        $total = $exAll->count();
        $passed = $exAll->where('status','passed')->count();
        $failed = $exAll->where('status','failed')->count();
        $witnessed = $exAll->whereNotNull('witness_signed_at')->count();
        $scorePct = $total > 0 ? round(($passed / $total) * 100, 0) : 0;
        $witnessPct = $total > 0 ? round(($witnessed / $total) * 100, 0) : 0;
        $ringCirc = 2 * M_PI * 28; // r=28
        $ringOffset = $ringCirc * (1 - $scorePct / 100);
    @endphp

    @if($total > 0)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
            {{-- Score ring --}}
            <div class="card p-4 flex items-center gap-4">
                <div class="relative w-[72px] h-[72px] shrink-0">
                    <svg class="w-full h-full -rotate-90" viewBox="0 0 64 64">
                        <circle cx="32" cy="32" r="28" stroke="#E5E7EB" stroke-width="6" fill="none"/>
                        <circle cx="32" cy="32" r="28"
                            stroke="{{ $scorePct >= 95 ? '#10B981' : ($scorePct >= 80 ? '#F59E0B' : '#EF4444') }}"
                            stroke-width="6" fill="none" stroke-linecap="round"
                            stroke-dasharray="{{ $ringCirc }}" stroke-dashoffset="{{ $ringOffset }}"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-[14px] font-bold text-ink tabular-nums">{{ $scorePct }}%</span>
                    </div>
                </div>
                <div>
                    <p class="label-kicker">Pass Score</p>
                    <p class="text-[12px] text-ink-muted mt-1 mono">{{ $passed }}/{{ $total }} passed</p>
                </div>
            </div>
            {{-- Witness coverage --}}
            <div class="card p-4">
                <p class="label-kicker">Witness Coverage</p>
                <div class="kpi-value text-ink mt-2">{{ $witnessPct }}<span class="text-lg text-ink-soft">%</span></div>
                <p class="text-[11px] text-ink-soft mt-1 mono">{{ $witnessed }} of {{ $total }} countersigned</p>
            </div>
            {{-- Failures --}}
            <div class="card p-4">
                <p class="label-kicker">Failures</p>
                <div class="kpi-value {{ $failed > 0 ? 'text-red-700' : 'text-ink' }} mt-2">{{ $failed }}</div>
                <p class="text-[11px] text-ink-soft mt-1 mono">{{ $failed > 0 ? 'Open deficiency risk' : 'No failures on record' }}</p>
            </div>
        </div>
    @endif

    @if($this->executions->isEmpty())
        <div class="text-center py-10 text-[13px] text-ink-muted">
            No FPT executions on record for this asset yet.
        </div>
    @else
        <div class="card overflow-hidden">
            <table class="min-w-full text-[13px]">
                <thead>
                    <tr class="hairline-b">
                        <th class="px-4 py-2.5 text-left label-kicker">Script</th>
                        <th class="px-4 py-2.5 text-left label-kicker">Status</th>
                        <th class="px-4 py-2.5 text-left label-kicker">Started</th>
                        <th class="px-4 py-2.5 text-left label-kicker">Run By</th>
                        <th class="px-4 py-2.5 text-right label-kicker">Complete</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->executions as $ex)
                        @php
                            $chipCls = match($ex->status) {
                                'passed'      => 'chip-pass',
                                'failed'      => 'chip-fail',
                                'aborted'     => 'chip-pending',
                                'in_progress' => 'chip-run',
                                default       => 'chip-pending',
                            };
                        @endphp
                        <tr class="hairline-b last:border-b-0 hover:bg-slate-50/60">
                            <td class="px-4 py-3">
                                <a href="{{ route('fpt.run', $ex->id) }}" wire:navigate class="block">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-[13px] font-semibold text-ink hover:text-accent-700">{{ $ex->test_script_name }}</span>
                                        <span class="mono text-[11px] text-ink-soft">v{{ $ex->test_script_version }}</span>
                                        @if($ex->parent_execution_id)
                                            <span class="chip chip-warn">Retest</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-ink-soft mono mt-0.5">
                                        {{ $ex->pass_count }}/{{ $ex->total_count }} pass
                                        @if($ex->fail_count > 0) · <span class="text-red-600">{{ $ex->fail_count }} fail</span> @endif
                                    </div>
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <span class="chip {{ $chipCls }}">{{ ucfirst(str_replace('_', ' ', $ex->status)) }}</span>
                                @if($ex->witness_signed_at)
                                    <div class="text-[10px] text-emerald-700 mt-1 font-semibold">Witnessed</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-[12px] text-ink-soft mono">{{ $ex->started_at?->diffForHumans() ?? '—' }}</td>
                            <td class="px-4 py-3 text-[13px] text-ink">{{ $ex->starter?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <span class="mono text-[13px] font-semibold text-ink tabular-nums">{{ $ex->progressPercent() }}%</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
