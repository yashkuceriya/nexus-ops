<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <p class="label-kicker">Facility Operations</p>
            <h1 class="text-2xl font-bold tracking-tight text-ink mt-1">Asset Registry</h1>
            <p class="text-[13px] text-ink-muted mt-0.5">Track and manage facility equipment.</p>
        </div>
    </div>

    {{-- Filters Bar --}}
    <div class="card p-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
            <div class="relative flex-1 w-full sm:w-auto">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-ink-soft" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search assets by name, model, or QR code..."
                    class="w-full pl-10 pr-4 py-2 text-[13px] bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-accent-500/20 focus:border-accent-500 transition-colors placeholder:text-slate-400">
            </div>

            <select wire:model.live="systemFilter"
                class="pl-3 pr-8 py-2 text-[13px] bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-accent-500/20 focus:border-accent-500 transition-colors text-ink cursor-pointer">
                <option value="">All Systems</option>
                <option value="HVAC">HVAC</option>
                <option value="Electrical">Electrical</option>
                <option value="Fire/Life Safety">Fire/Life Safety</option>
                <option value="Plumbing">Plumbing</option>
            </select>

            <select wire:model.live="conditionFilter"
                class="pl-3 pr-8 py-2 text-[13px] bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-accent-500/20 focus:border-accent-500 transition-colors text-ink cursor-pointer">
                <option value="">All Conditions</option>
                @foreach(['excellent','good','fair','poor','critical'] as $c)
                <option value="{{ $c }}">{{ ucfirst($c) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Asset Card Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($assets as $asset)
        @php
            $conditionChip = match($asset->condition) {
                'excellent','good' => 'chip-pass',
                'fair','poor' => 'chip-warn',
                'critical' => 'chip-fail',
                default => 'chip-pending',
            };
        @endphp
        <a href="{{ route('assets.show', $asset) }}" class="card block overflow-hidden p-0 hover:border-slate-300 transition-all duration-200">
            <div class="px-5 pt-5 pb-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <h3 class="text-[13px] font-semibold text-ink truncate">{{ $asset->name }}</h3>
                        <p class="text-[11px] text-ink-soft mono mt-0.5 truncate">{{ $asset->system_type }} &middot; {{ $asset->manufacturer }} {{ $asset->model_number }}</p>
                    </div>
                    <span class="chip {{ $conditionChip }} flex-shrink-0">{{ ucfirst($asset->condition) }}</span>
                </div>
            </div>

            <div class="px-5 pb-4">
                <div class="grid grid-cols-2 gap-2">
                    <div class="bg-slate-50 rounded-lg p-3">
                        <div class="label-kicker mb-1">Location</div>
                        <div class="text-[12px] font-semibold text-ink-muted truncate">{{ $asset->location?->name ?? '---' }}</div>
                    </div>
                    <div class="bg-slate-50 rounded-lg p-3">
                        <div class="label-kicker mb-1">Runtime</div>
                        <div class="text-[12px] font-semibold text-ink-muted">
                            <span class="mono tabular-nums">{{ number_format($asset->runtime_hours) }}</span> hrs
                        </div>
                    </div>
                    <div class="bg-slate-50 rounded-lg p-3">
                        <div class="label-kicker mb-1">QR Code</div>
                        <div class="text-[12px] mono font-semibold text-ink-muted truncate">{{ $asset->qr_code ?? '---' }}</div>
                    </div>
                    <div class="bg-slate-50 rounded-lg p-3">
                        <div class="label-kicker mb-1">Warranty</div>
                        <div class="text-[12px] font-semibold">
                            @if($asset->isWarrantyActive())
                                <span class="flex items-center gap-1 text-emerald-700">
                                    <span class="dot dot-pass"></span>
                                    Active
                                </span>
                            @else
                                <span class="flex items-center gap-1 text-red-600">
                                    <span class="dot dot-fail"></span>
                                    Expired
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-5 py-3 bg-slate-50/50 hairline-t flex items-center justify-between">
                <div class="flex items-center gap-2">
                    @if($asset->sensorSources->count() > 0)
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-accent-700">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.348 14.651a3.75 3.75 0 010-5.303m5.304 0a3.75 3.75 0 010 5.303m-7.425 2.122a6.75 6.75 0 010-9.546m9.546 0a6.75 6.75 0 010 9.546"/>
                        </svg>
                        {{ $asset->sensorSources->count() }} {{ Str::plural('sensor', $asset->sensorSources->count()) }}
                    </span>
                    @else
                    <span class="text-[11px] text-ink-soft">No sensors</span>
                    @endif
                </div>
                @if($asset->project?->name)
                <span class="inline-flex items-center gap-1 text-[11px] text-ink-soft">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"/>
                    </svg>
                    {{ $asset->project->name }}
                </span>
                @endif
            </div>
        </a>
        @endforeach
    </div>

    {{-- Empty State --}}
    @if($assets->isEmpty())
    <div class="card p-16 text-center">
        <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
            </svg>
        </div>
        <p class="text-[13px] text-ink-muted font-semibold">No assets found</p>
        <p class="text-[11px] text-ink-soft mt-1">Try adjusting your search or filter criteria.</p>
    </div>
    @endif

    {{-- Pagination --}}
    @if($assets->hasPages())
    <div class="card px-5 py-3">
        {{ $assets->links() }}
    </div>
    @endif
</div>
