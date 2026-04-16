<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

    <div class="flex items-start justify-between mb-5 flex-wrap gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 tracking-tight">Functional Performance Tests</h2>
            <p class="text-sm text-gray-500 mt-1">
                Cx test execution history for this asset with full audit trail.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <select wire:model="scriptToRun"
                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm min-w-[240px]">
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
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-40 disabled:cursor-not-allowed">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Run FPT
            </button>
        </div>
    </div>

    @if($this->availableScripts->isEmpty())
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 mb-4">
            No published test scripts available for system type
            <span class="font-medium">{{ $asset->system_type ?? '—' }}</span>.
            Visit the <a href="{{ route('fpt.scripts.index') }}" wire:navigate class="underline font-medium">script library</a> to author or seed one.
        </div>
    @endif

    @if($this->executions->isEmpty())
        <div class="text-center py-10 text-sm text-gray-500">
            No FPT executions on record for this asset yet.
        </div>
    @else
        <div class="space-y-3">
            @foreach($this->executions as $ex)
                @php
                    $statusConfig = match($ex->status) {
                        'passed'      => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'label' => 'Passed'],
                        'failed'      => ['bg' => 'bg-red-50',     'text' => 'text-red-700',     'label' => 'Failed'],
                        'aborted'     => ['bg' => 'bg-gray-100',   'text' => 'text-gray-700',    'label' => 'Aborted'],
                        'in_progress' => ['bg' => 'bg-blue-50',    'text' => 'text-blue-700',    'label' => 'In Progress'],
                        default       => ['bg' => 'bg-slate-50',   'text' => 'text-slate-700',   'label' => ucfirst($ex->status)],
                    };
                @endphp
                <a href="{{ route('fpt.run', $ex->id) }}" wire:navigate
                    class="block rounded-lg border border-gray-200 p-4 hover:border-indigo-300 hover:shadow-sm transition">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-semibold text-gray-900">{{ $ex->test_script_name }}</span>
                                <span class="text-xs text-gray-500">v{{ $ex->test_script_version }}</span>
                                <span class="inline-flex items-center rounded-full {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} px-2.5 py-0.5 text-xs font-medium uppercase">
                                    {{ $statusConfig['label'] }}
                                </span>
                                @if($ex->witness_signed_at)
                                    <span class="inline-flex items-center rounded-md bg-emerald-50 text-emerald-700 px-2 py-0.5 text-[10px] font-medium uppercase">Witnessed</span>
                                @endif
                                @if($ex->parent_execution_id)
                                    <span class="inline-flex items-center rounded-md bg-amber-50 text-amber-700 px-2 py-0.5 text-[10px] font-medium uppercase">Retest</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Started by {{ $ex->starter?->name ?? '—' }}
                                {{ $ex->started_at?->diffForHumans() }}
                                · {{ $ex->pass_count }}/{{ $ex->total_count }} pass
                                @if($ex->fail_count > 0) · <span class="text-red-600">{{ $ex->fail_count }} fail</span> @endif
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-gray-900">{{ $ex->progressPercent() }}%</div>
                            <div class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Complete</div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
