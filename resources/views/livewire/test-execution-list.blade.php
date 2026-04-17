<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <p class="label-kicker">Commissioning · FPT</p>
            <h1 class="text-2xl font-bold tracking-tight text-ink mt-1">Test Executions</h1>
            <p class="text-[13px] text-ink-muted mt-0.5">All Functional Performance Test runs, across every asset and project.</p>
        </div>
        <a href="{{ route('fpt.scripts.index') }}" wire:navigate class="btn-primary inline-flex items-center gap-2">
            Script Library
        </a>
    </div>

    @php
        $s = $this->stats;
        $rate = (float) $s['pass_rate'];
        $barColor = $rate >= 95 ? 'bg-emerald-500' : ($rate >= 80 ? 'bg-amber-500' : 'bg-red-500');
        $cards = [
            ['label' => 'Total', 'value' => $s['total'], 'tone' => 'text-ink'],
            ['label' => 'Passed', 'value' => $s['passed'], 'tone' => 'text-emerald-700'],
            ['label' => 'Failed', 'value' => $s['failed'], 'tone' => 'text-red-700'],
            ['label' => 'Running', 'value' => $s['running'], 'tone' => 'text-accent-700'],
            ['label' => 'Witnessed', 'value' => $s['witnessed'], 'tone' => 'text-accent-700'],
        ];
    @endphp

    {{-- KPI row --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        @foreach($cards as $c)
            <div class="card kpi">
                <p class="label-kicker">{{ $c['label'] }}</p>
                <div class="kpi-value {{ $c['tone'] }} mt-2">{{ $c['value'] }}</div>
            </div>
        @endforeach
    </div>

    @if($s['total'] > 0)
        <div class="card p-4 flex items-center gap-3 text-[12px] text-ink-soft">
            <span>Execution pass rate</span>
            <div class="h-1.5 flex-1 max-w-xs overflow-hidden rounded-full bg-slate-100">
                <div class="h-full {{ $barColor }}" style="width: {{ $rate }}%;"></div>
            </div>
            <span class="mono font-semibold text-ink tabular-nums">{{ number_format($rate, 1) }}%</span>
        </div>
    @endif

    {{-- Filters --}}
    <div class="card p-4">
        <div class="flex flex-wrap items-center gap-3">
            <select wire:model.live="statusFilter" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-[13px]">
                <option value="">All Statuses</option>
                <option value="in_progress">In Progress</option>
                <option value="passed">Passed</option>
                <option value="failed">Failed</option>
                <option value="aborted">Aborted</option>
                <option value="on_hold">On Hold</option>
            </select>
            <select wire:model.live="projectFilter" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-[13px]">
                <option value="">All Projects</option>
                @foreach($this->projects as $project)
                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="cxLevelFilter" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-[13px]">
                <option value="">All Cx Levels</option>
                <option value="L1">L1 — Installation Verification</option>
                <option value="L2">L2 — Start-Up</option>
                <option value="L3">L3 — Functional Performance</option>
                <option value="L4">L4 — Integrated Systems</option>
                <option value="L5">L5 — Occupant Verification</option>
            </select>
            <label class="inline-flex items-center gap-2 text-[13px] text-ink">
                <input type="checkbox" wire:model.live="witnessedOnly" class="rounded border-gray-300 text-accent-600">
                Witnessed only
            </label>
            <button wire:click="clearFilters" class="text-[12px] text-ink-soft hover:text-ink underline">Reset</button>
        </div>
    </div>

    @if($this->executions->isEmpty())
        <div class="card p-12 text-center">
            <h3 class="text-[15px] font-semibold text-ink mb-1">No executions yet</h3>
            <p class="text-[13px] text-ink-muted">Pick a script from the library and run it against an asset.</p>
        </div>
    @else
        <div class="card overflow-hidden">
            <table class="min-w-full">
                <thead>
                    <tr class="hairline-b">
                        <th class="px-4 py-3 text-left label-kicker">Script</th>
                        <th class="px-4 py-3 text-left label-kicker">Asset / Project</th>
                        <th class="px-4 py-3 text-left label-kicker">Status</th>
                        <th class="px-4 py-3 text-left label-kicker">Progress</th>
                        <th class="px-4 py-3 text-left label-kicker">Run By</th>
                        <th class="px-4 py-3 text-left label-kicker">Started</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->executions as $ex)
                        @php
                            $chipClass = match($ex->status) {
                                'passed'      => 'chip-pass',
                                'failed'      => 'chip-fail',
                                'aborted'     => 'chip-pending',
                                'on_hold'     => 'chip-warn',
                                'in_progress' => 'chip-run',
                                default       => 'chip-pending',
                            };
                        @endphp
                        <tr class="hairline-b hover:bg-slate-50/60 last:border-b-0">
                            <td class="px-4 py-3">
                                <a href="{{ route('fpt.run', $ex->id) }}" wire:navigate class="text-[13px] font-semibold text-accent-700 hover:text-accent-800">
                                    {{ $ex->test_script_name }}
                                </a>
                                <div class="text-[11px] text-ink-soft mono">v{{ $ex->test_script_version }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-[13px] text-ink">{{ $ex->asset?->name }}</div>
                                <div class="text-[11px] text-ink-soft">{{ $ex->project?->name }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="chip {{ $chipClass }}">{{ ucfirst(str_replace('_', ' ', $ex->status)) }}</span>
                                @if($ex->witness_signed_at)
                                    <div class="text-[10px] text-emerald-700 mt-1 font-semibold">Witnessed</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-[12px] text-ink-muted mono">
                                <div>{{ $ex->pass_count }}/{{ $ex->total_count }} pass</div>
                                @if($ex->fail_count > 0)
                                    <div class="text-red-600">{{ $ex->fail_count }} fail</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-[13px] text-ink">{{ $ex->starter?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-[12px] text-ink-soft mono">{{ $ex->started_at?->diffForHumans() ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div>{{ $this->executions->links() }}</div>
    @endif
</div>
