<div class="min-h-screen bg-gray-50">
    <div class="px-6 py-6 max-w-[1600px] mx-auto">

        <div class="mb-5">
            <a href="{{ route('projects.show', $project->id) }}" wire:navigate
                class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Project
            </a>
        </div>

        <div class="flex items-start justify-between mb-6 flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Commissioning Test Matrix</h1>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $project->name }} — every asset, every script, every outcome at a glance.
                </p>
            </div>
            <a href="{{ route('fpt.scripts.index') }}" wire:navigate
                class="inline-flex items-center gap-2 rounded-lg bg-white border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Script Library
            </a>
        </div>

        {{-- Summary --}}
        @php($summary = $this->summary)
        <div class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-5">
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total cells</div>
                <div class="text-2xl font-bold text-gray-900 mt-1">{{ $summary['total'] }}</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Passed</div>
                <div class="text-2xl font-bold text-emerald-600 mt-1">{{ $summary['passed'] }}</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="text-xs font-semibold uppercase tracking-wider text-red-700">Failed</div>
                <div class="text-2xl font-bold text-red-600 mt-1">{{ $summary['failed'] }}</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="text-xs font-semibold uppercase tracking-wider text-blue-700">Running</div>
                <div class="text-2xl font-bold text-blue-600 mt-1">{{ $summary['in_progress'] }}</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="text-xs font-semibold uppercase tracking-wider text-gray-500">Not run</div>
                <div class="text-2xl font-bold text-gray-400 mt-1">{{ $summary['not_run'] }}</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="text-xs font-semibold uppercase tracking-wider text-indigo-700">Witnessed</div>
                <div class="text-2xl font-bold text-indigo-600 mt-1">{{ $summary['witnessed'] }}</div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="flex flex-wrap items-center gap-3 mb-4">
            <select wire:model.live="levelFilter" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">All Cx Levels</option>
                <option value="L1">L1 — Factory Witness</option>
                <option value="L2">L2 — Installation</option>
                <option value="L3">L3 — Component FPT</option>
                <option value="L4">L4 — System Integration</option>
                <option value="L5">L5 — Integrated Systems Test</option>
            </select>
            <select wire:model.live="systemFilter" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">All Asset Categories</option>
                @foreach($this->categories as $cat)
                    <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                @endforeach
            </select>
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" wire:model.live="onlyGaps" class="rounded border-gray-300">
                Only show assets with gaps
            </label>
            <button wire:click="clearFilters" class="text-sm text-gray-500 hover:text-gray-700 underline">Reset</button>
            <div class="ml-auto flex items-center gap-2">
                <a href="{{ route('projects.turnover', $project->id) }}" wire:navigate
                   class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Turnover →
                </a>
                <button wire:click="exportCsv"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                    Export CSV
                </button>
            </div>
        </div>

        {{-- Legend --}}
        <div class="flex flex-wrap items-center gap-3 mb-4 text-xs text-gray-600">
            <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-emerald-500"></span> Passed</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-red-500"></span> Failed</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-blue-500"></span> In progress</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-amber-400"></span> Aborted / hold</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-gray-200 border border-gray-300"></span> Not run</span>
            <span class="inline-flex items-center gap-1.5 ml-3 text-gray-500">
                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a4 4 0 100 8 4 4 0 000-8z"/></svg>
                Witnessed
            </span>
        </div>

        @php($assets = $this->visibleAssets)
        @php($scripts = $this->scripts)
        @php($cells = $this->cells)

        @if($assets->isEmpty() || $scripts->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <h3 class="text-sm font-semibold text-gray-900 mb-1">Nothing to display</h3>
                <p class="text-sm text-gray-500">
                    @if($assets->isEmpty()) This project has no assets yet. @endif
                    @if($scripts->isEmpty()) No published test scripts match the current filters. @endif
                </p>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-auto">
                <table class="min-w-full text-sm">
                    <thead class="sticky top-0 bg-gray-50 z-10">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 border-b border-gray-200 sticky left-0 bg-gray-50 z-20">Asset</th>
                            @foreach($scripts as $script)
                                <th class="px-3 py-3 text-center border-b border-gray-200 border-l border-gray-100 font-medium text-gray-700 min-w-[140px]">
                                    <div class="flex flex-col items-center gap-1">
                                        @if($script->cx_level)
                                            <span class="inline-flex rounded bg-slate-100 text-slate-700 px-1.5 py-0.5 text-[10px] font-semibold">{{ $script->cx_level }}</span>
                                        @endif
                                        <span class="text-xs">{{ $script->name }}</span>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($assets as $asset)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3 border-b border-gray-100 sticky left-0 bg-white z-10">
                                <div class="text-sm font-medium text-gray-900">{{ $asset->name }}</div>
                                <div class="text-xs text-gray-500">{{ $asset->asset_tag }} · {{ $asset->category }}</div>
                            </td>
                            @foreach($scripts as $script)
                                @php($cell = $cells[$asset->id][$script->id] ?? null)
                                @php($s = $cell['status'] ?? 'not_run')
                                @php($config = $this->cellConfig($s))
                                <td class="p-0 border-b border-gray-100 border-l border-gray-100 text-center">
                                    @if($cell && $cell['execution_id'])
                                        <div class="group relative">
                                            <a href="{{ route('fpt.run', $cell['execution_id']) }}" wire:navigate
                                                class="relative flex items-center justify-center h-12 w-full {{ $config['bg'] }} {{ $config['text'] }} transition">
                                                <span class="text-lg font-bold">{{ $config['icon'] }}</span>
                                                @if($cell['witnessed'])
                                                    <svg class="absolute top-1 right-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20" title="Witnessed">
                                                        <path d="M10 2a4 4 0 100 8 4 4 0 000-8z"/>
                                                    </svg>
                                                @endif
                                                @if($cell['retested'])
                                                    <span class="absolute bottom-0.5 right-1 text-[8px] font-semibold uppercase opacity-70">RT</span>
                                                @endif
                                            </a>
                                            @if($s === 'failed')
                                                <button
                                                    type="button"
                                                    wire:click="retest({{ $cell['execution_id'] }})"
                                                    wire:confirm="Start a retest for this failed execution?"
                                                    title="Start retest"
                                                    class="absolute inset-0 flex items-center justify-center bg-red-600/90 text-white text-[10px] font-bold uppercase tracking-wider opacity-0 group-hover:opacity-100 transition cursor-pointer">
                                                    ↻ Retest
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <button
                                            type="button"
                                            wire:click="startExecution({{ $asset->id }}, {{ $script->id }})"
                                            wire:confirm="Start this FPT on {{ $asset->name }}?"
                                            title="Start FPT"
                                            class="flex items-center justify-center h-12 w-full {{ $config['bg'] }} {{ $config['text'] }} hover:bg-indigo-50 hover:text-indigo-700 transition cursor-pointer">
                                            <span class="text-lg group-hover:hidden">{{ $config['icon'] }}</span>
                                            <span class="text-[10px] font-bold uppercase tracking-wider opacity-0 hover:opacity-100">▶ Start</span>
                                        </button>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <p class="text-xs text-gray-500 mt-3">
                Showing latest execution per cell. "RT" indicates the cell has a retest chain.
                A small witness dot means the execution was countersigned by a Cx authority.
            </p>
        @endif
    </div>
</div>
