<div class="space-y-6">
    <div>
        <a href="{{ route('projects.show', $project->id) }}" wire:navigate
            class="inline-flex items-center gap-1.5 text-[12px] text-ink-soft hover:text-ink">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Project
        </a>
    </div>

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <p class="label-kicker">Commissioning · Test Matrix</p>
            <h1 class="text-2xl font-bold tracking-tight text-ink mt-1">Cx Test Matrix</h1>
            <p class="text-[13px] text-ink-muted mt-0.5">{{ $project->name }} — every asset, every script, every outcome at a glance.</p>
        </div>
        <a href="{{ route('fpt.scripts.index') }}" wire:navigate class="btn-ghost">Script Library</a>
    </div>

    {{-- Filters --}}
    <div class="card p-4">
        <div class="flex flex-wrap items-center gap-3">
            <select wire:model.live="levelFilter" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-[13px]">
                <option value="">All Cx Levels</option>
                <option value="L1">L1 — Factory Witness</option>
                <option value="L2">L2 — Installation</option>
                <option value="L3">L3 — Component FPT</option>
                <option value="L4">L4 — System Integration</option>
                <option value="L5">L5 — Integrated Systems Test</option>
            </select>
            <select wire:model.live="systemFilter" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-[13px]">
                <option value="">All Asset Categories</option>
                @foreach($this->categories as $cat)
                    <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                @endforeach
            </select>
            <label class="inline-flex items-center gap-2 text-[13px] text-ink">
                <input type="checkbox" wire:model.live="onlyGaps" class="rounded border-gray-300 text-accent-600">
                Only show assets with gaps
            </label>
            <button wire:click="clearFilters" class="text-[12px] text-ink-soft hover:text-ink underline">Reset</button>
            <div class="ml-auto flex items-center gap-2">
                <a href="{{ route('projects.turnover', $project->id) }}" wire:navigate class="btn-ghost">Turnover →</a>
                <button wire:click="exportCsv" class="btn-primary inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                    Export CSV
                </button>
            </div>
        </div>
    </div>

    {{-- Legend --}}
    <div class="card p-4">
        <div class="flex flex-wrap items-center gap-4 text-[11px] text-ink-soft">
            <span class="inline-flex items-center gap-1.5"><span class="dot dot-pass"></span> Passed</span>
            <span class="inline-flex items-center gap-1.5"><span class="dot dot-fail"></span> Failed</span>
            <span class="inline-flex items-center gap-1.5"><span class="dot dot-run"></span> In progress</span>
            <span class="inline-flex items-center gap-1.5"><span class="dot dot-warn"></span> Aborted / hold</span>
            <span class="inline-flex items-center gap-1.5"><span class="dot dot-pending"></span> Not run</span>
            <span class="inline-flex items-center gap-1.5 ml-3 text-ink-soft">
                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a4 4 0 100 8 4 4 0 000-8z"/></svg>
                Witnessed
            </span>
        </div>
    </div>

    @php($assets = $this->visibleAssets)
    @php($scripts = $this->scripts)
    @php($cells = $this->cells)

    @if($assets->isEmpty() || $scripts->isEmpty())
        <div class="card p-12 text-center">
            <h3 class="text-[15px] font-semibold text-ink mb-1">Nothing to display</h3>
            <p class="text-[13px] text-ink-muted">
                @if($assets->isEmpty()) This project has no assets yet. @endif
                @if($scripts->isEmpty()) No published test scripts match the current filters. @endif
            </p>
        </div>
    @else
        <div class="card overflow-auto">
            <table class="min-w-full text-[13px]">
                <thead class="sticky top-0 bg-white z-10">
                    <tr class="hairline-b">
                        <th class="px-4 py-3 text-left label-kicker sticky left-0 bg-white z-20">Asset</th>
                        @foreach($scripts as $script)
                            <th class="px-3 py-3 text-center label-kicker min-w-[140px]">
                                <div class="flex flex-col items-center gap-1">
                                    @if($script->cx_level)
                                        <span class="chip chip-pending">{{ $script->cx_level }}</span>
                                    @endif
                                    <span class="text-[11px] normal-case tracking-normal font-semibold text-ink">{{ $script->name }}</span>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                @foreach($assets as $asset)
                    <tr class="hairline-b last:border-b-0 hover:bg-slate-50/60">
                        <td class="px-4 py-3 sticky left-0 bg-white z-10 hairline-r">
                            <div class="text-[13px] font-semibold text-ink">{{ $asset->name }}</div>
                            <div class="text-[11px] text-ink-soft mono">{{ $asset->asset_tag }} · {{ $asset->category }}</div>
                        </td>
                        @foreach($scripts as $script)
                            @php($cellData = $cells[$asset->id][$script->id] ?? null)
                            @php($s = $cellData['status'] ?? 'not_run')
                            @php($cellCls = match($s) {
                                'passed' => 'cell cell-pass',
                                'failed' => 'cell cell-fail',
                                'in_progress' => 'cell cell-run',
                                'aborted', 'on_hold' => 'cell cell-run',
                                default => 'cell cell-none',
                            })
                            @php($icon = match($s) {
                                'passed' => '✓',
                                'failed' => '✕',
                                'in_progress' => '●',
                                'aborted', 'on_hold' => '!',
                                default => '·',
                            })
                            <td class="p-2 text-center align-middle">
                                @if($cellData && $cellData['execution_id'])
                                    <div class="group relative inline-block">
                                        <a href="{{ route('fpt.run', $cellData['execution_id']) }}" wire:navigate
                                            class="{{ $cellCls }} relative inline-flex items-center justify-center">
                                            <span class="font-bold">{{ $icon }}</span>
                                            @if($cellData['witnessed'])
                                                <svg class="absolute top-0.5 right-0.5 h-2.5 w-2.5" fill="currentColor" viewBox="0 0 20 20" title="Witnessed">
                                                    <path d="M10 2a4 4 0 100 8 4 4 0 000-8z"/>
                                                </svg>
                                            @endif
                                            @if($cellData['retested'])
                                                <span class="absolute bottom-0 right-0.5 text-[8px] font-semibold uppercase opacity-70 mono">RT</span>
                                            @endif
                                        </a>
                                        @if($s === 'failed')
                                            <button
                                                type="button"
                                                wire:click="retest({{ $cellData['execution_id'] }})"
                                                wire:confirm="Start a retest for this failed execution?"
                                                title="Start retest"
                                                class="cell absolute inset-0 flex items-center justify-center bg-red-600 text-white text-[10px] font-bold uppercase tracking-wider opacity-0 group-hover:opacity-100 transition cursor-pointer">
                                                ↻ RT
                                            </button>
                                        @endif
                                    </div>
                                @else
                                    <button
                                        type="button"
                                        wire:click="startExecution({{ $asset->id }}, {{ $script->id }})"
                                        wire:confirm="Start this FPT on {{ $asset->name }}?"
                                        title="Start FPT"
                                        class="{{ $cellCls }} inline-flex items-center justify-center hover:bg-accent-50 hover:text-accent-700 transition cursor-pointer">
                                        <span>{{ $icon }}</span>
                                    </button>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <p class="text-[11px] text-ink-soft">
            Showing latest execution per cell. "RT" indicates the cell has a retest chain.
            A small witness dot means the execution was countersigned by a Cx authority.
        </p>

        {{-- KPI tiles: Total Matrix Progression --}}
        @php($summary = $this->summary)
        <div class="card p-5">
            <div class="mb-4">
                <h2 class="text-[15px] font-semibold text-ink">Total Matrix Progression</h2>
                <p class="text-[12px] text-ink-muted">Roll-up across every asset × script cell.</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                <div class="card kpi">
                    <p class="label-kicker">Total Cells</p>
                    <div class="kpi-value text-ink mt-2">{{ $summary['total'] }}</div>
                </div>
                <div class="card kpi">
                    <p class="label-kicker">Passed</p>
                    <div class="kpi-value text-emerald-700 mt-2">{{ $summary['passed'] }}</div>
                </div>
                <div class="card kpi">
                    <p class="label-kicker">Failed</p>
                    <div class="kpi-value text-red-700 mt-2">{{ $summary['failed'] }}</div>
                </div>
                <div class="card kpi">
                    <p class="label-kicker">Running</p>
                    <div class="kpi-value text-accent-700 mt-2">{{ $summary['in_progress'] }}</div>
                </div>
                <div class="card kpi">
                    <p class="label-kicker">Not Run</p>
                    <div class="kpi-value text-ink-soft mt-2">{{ $summary['not_run'] }}</div>
                </div>
                <div class="card kpi">
                    <p class="label-kicker">Witnessed</p>
                    <div class="kpi-value text-accent-700 mt-2">{{ $summary['witnessed'] }}</div>
                </div>
            </div>
        </div>
    @endif
</div>
