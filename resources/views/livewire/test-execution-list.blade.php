<div class="min-h-screen bg-gray-50">
    <div class="px-6 py-6 max-w-[1400px] mx-auto">

        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Test Executions</h1>
                <p class="mt-1 text-sm text-gray-500">All Functional Performance Test runs, across every asset and project.</p>
            </div>
            <a href="{{ route('fpt.scripts.index') }}" wire:navigate
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                Script Library
            </a>
        </div>

        {{-- Stats strip --}}
        @php
            $s = $this->stats;
            $rate = (float) $s['pass_rate'];
            $barColor = $rate >= 95 ? 'bg-emerald-500' : ($rate >= 80 ? 'bg-amber-500' : 'bg-red-500');
            $cards = [
                ['label' => 'Total', 'value' => $s['total'], 'color' => 'text-gray-900'],
                ['label' => 'Passed', 'value' => $s['passed'], 'color' => 'text-emerald-600'],
                ['label' => 'Failed', 'value' => $s['failed'], 'color' => 'text-red-600'],
                ['label' => 'Running', 'value' => $s['running'], 'color' => 'text-blue-600'],
                ['label' => 'Witnessed', 'value' => $s['witnessed'], 'color' => 'text-indigo-600'],
            ];
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-5">
            @foreach($cards as $c)
                <div class="rounded-xl border border-gray-200 bg-white px-4 py-3">
                    <div class="text-[10px] uppercase tracking-wider font-semibold text-gray-500">{{ $c['label'] }}</div>
                    <div class="mt-0.5 text-2xl font-extrabold tabular-nums {{ $c['color'] }}">{{ $c['value'] }}</div>
                </div>
            @endforeach
        </div>
        @if($s['total'] > 0)
            <div class="mb-5 flex items-center gap-2 text-xs text-gray-500">
                <span>Execution pass rate</span>
                <div class="h-1.5 flex-1 max-w-xs overflow-hidden rounded-full bg-gray-100">
                    <div class="h-full {{ $barColor }}" style="width: {{ $rate }}%;"></div>
                </div>
                <span class="tabular-nums font-semibold text-gray-800">{{ number_format($rate, 1) }}%</span>
            </div>
        @endif

        <div class="flex flex-wrap items-center gap-3 mb-5">
            <select wire:model.live="statusFilter" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">All Statuses</option>
                <option value="in_progress">In Progress</option>
                <option value="passed">Passed</option>
                <option value="failed">Failed</option>
                <option value="aborted">Aborted</option>
                <option value="on_hold">On Hold</option>
            </select>
            <select wire:model.live="projectFilter" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">All Projects</option>
                @foreach($this->projects as $project)
                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="cxLevelFilter" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">All Cx Levels</option>
                <option value="L1">L1 — Installation Verification</option>
                <option value="L2">L2 — Start-Up</option>
                <option value="L3">L3 — Functional Performance</option>
                <option value="L4">L4 — Integrated Systems</option>
                <option value="L5">L5 — Occupant Verification</option>
            </select>
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" wire:model.live="witnessedOnly" class="rounded border-gray-300">
                Witnessed only
            </label>
            <button wire:click="clearFilters" class="text-sm text-gray-500 hover:text-gray-700 underline">Reset</button>
        </div>

        @if($this->executions->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <h3 class="text-sm font-semibold text-gray-900 mb-1">No executions yet</h3>
                <p class="text-sm text-gray-500">Pick a script from the library and run it against an asset.</p>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Script</th>
                            <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Asset / Project</th>
                            <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Progress</th>
                            <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Run By</th>
                            <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Started</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($this->executions as $ex)
                            @php
                                $statusConfig = match($ex->status) {
                                    'passed'      => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700'],
                                    'failed'      => ['bg' => 'bg-red-50',     'text' => 'text-red-700'],
                                    'aborted'     => ['bg' => 'bg-gray-100',   'text' => 'text-gray-700'],
                                    'on_hold'     => ['bg' => 'bg-amber-50',   'text' => 'text-amber-700'],
                                    'in_progress' => ['bg' => 'bg-blue-50',    'text' => 'text-blue-700'],
                                    default       => ['bg' => 'bg-slate-50',   'text' => 'text-slate-700'],
                                };
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <a href="{{ route('fpt.run', $ex->id) }}" wire:navigate class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                        {{ $ex->test_script_name }}
                                    </a>
                                    <div class="text-xs text-gray-500">v{{ $ex->test_script_version }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-gray-900">{{ $ex->asset?->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $ex->project?->name }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} px-2.5 py-0.5 text-xs font-medium uppercase">
                                        {{ str_replace('_', ' ', $ex->status) }}
                                    </span>
                                    @if($ex->witness_signed_at)
                                        <div class="text-[10px] text-emerald-600 mt-0.5">witnessed</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    <div>{{ $ex->pass_count }}/{{ $ex->total_count }} pass</div>
                                    @if($ex->fail_count > 0)
                                        <div class="text-red-600">{{ $ex->fail_count }} fail</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $ex->starter?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $ex->started_at?->diffForHumans() ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-6">{{ $this->executions->links() }}</div>
        @endif
    </div>
</div>
