<div class="min-h-screen bg-slate-50">
    {{-- Page Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-1">
            <div class="w-2 h-8 bg-emerald-500 rounded-full"></div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Asset Registry</h1>
        </div>
        <p class="text-sm text-slate-500 ml-5 pl-0.5">Track and manage facility equipment &mdash; NexusOps Platform</p>
    </div>

    {{-- Filters Bar --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
            {{-- Search --}}
            <div class="relative flex-1 w-full sm:w-auto">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search assets by name, model, or QR code..."
                    class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors placeholder:text-slate-400">
            </div>

            {{-- System Type Filter --}}
            <div class="relative">
                <select wire:model.live="systemFilter"
                    class="appearance-none pl-4 pr-10 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors text-slate-700 cursor-pointer">
                    <option value="">All Systems</option>
                    <option value="HVAC">HVAC</option>
                    <option value="Electrical">Electrical</option>
                    <option value="Fire/Life Safety">Fire/Life Safety</option>
                    <option value="Plumbing">Plumbing</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                    </svg>
                </div>
            </div>

            {{-- Condition Filter --}}
            <div class="relative">
                <select wire:model.live="conditionFilter"
                    class="appearance-none pl-4 pr-10 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors text-slate-700 cursor-pointer">
                    <option value="">All Conditions</option>
                    @foreach(['excellent','good','fair','poor','critical'] as $c)
                    <option value="{{ $c }}">{{ ucfirst($c) }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Asset Card Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($assets as $asset)
        <a href="{{ route('assets.show', $asset) }}" class="block group bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
            {{-- Card Header --}}
            <div class="px-5 pt-5 pb-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-bold text-slate-900 truncate">{{ $asset->name }}</h3>
                        <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $asset->system_type }} &middot; {{ $asset->manufacturer }} {{ $asset->model_number }}</p>
                    </div>
                    @php
                        $conditionStyles = match($asset->condition) {
                            'excellent' => 'bg-emerald-100 text-emerald-700 ring-emerald-600/10',
                            'good' => 'bg-green-100 text-green-700 ring-green-600/10',
                            'fair' => 'bg-amber-100 text-amber-700 ring-amber-600/10',
                            'poor' => 'bg-orange-100 text-orange-700 ring-orange-600/10',
                            'critical' => 'bg-red-100 text-red-700 ring-red-600/10',
                            default => 'bg-slate-100 text-slate-600 ring-slate-500/10',
                        };
                    @endphp
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide ring-1 ring-inset flex-shrink-0 {{ $conditionStyles }}">
                        {{ ucfirst($asset->condition) }}
                    </span>
                </div>
            </div>

            {{-- Stats Grid --}}
            <div class="px-5 pb-4">
                <div class="grid grid-cols-2 gap-2">
                    <div class="bg-slate-50 rounded-lg p-3">
                        <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Location</div>
                        <div class="text-xs font-medium text-slate-700 truncate">{{ $asset->location?->name ?? '---' }}</div>
                    </div>
                    <div class="bg-slate-50 rounded-lg p-3">
                        <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Runtime</div>
                        <div class="text-xs font-medium text-slate-700">
                            <span class="font-mono">{{ number_format($asset->runtime_hours) }}</span> hrs
                        </div>
                    </div>
                    <div class="bg-slate-50 rounded-lg p-3">
                        <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">QR Code</div>
                        <div class="text-xs font-mono font-medium text-slate-700 truncate">{{ $asset->qr_code ?? '---' }}</div>
                    </div>
                    <div class="bg-slate-50 rounded-lg p-3">
                        <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Warranty</div>
                        <div class="text-xs font-medium">
                            @if($asset->isWarrantyActive())
                                <span class="flex items-center gap-1 text-emerald-700">
                                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                    Active
                                </span>
                            @else
                                <span class="flex items-center gap-1 text-red-600">
                                    <span class="w-1.5 h-1.5 bg-red-400 rounded-full"></span>
                                    Expired
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card Footer --}}
            <div class="px-5 py-3 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    @if($asset->sensorSources->count() > 0)
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-600">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.348 14.651a3.75 3.75 0 010-5.303m5.304 0a3.75 3.75 0 010 5.303m-7.425 2.122a6.75 6.75 0 010-9.546m9.546 0a6.75 6.75 0 010 9.546"/>
                        </svg>
                        {{ $asset->sensorSources->count() }} {{ Str::plural('sensor', $asset->sensorSources->count()) }}
                    </span>
                    @else
                    <span class="text-xs text-slate-400">No sensors</span>
                    @endif
                </div>
                @if($asset->project?->name)
                <span class="inline-flex items-center gap-1 text-xs text-slate-400">
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
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-16 text-center">
        <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
            </svg>
        </div>
        <p class="text-slate-500 font-medium">No assets found</p>
        <p class="text-sm text-slate-400 mt-1">Try adjusting your search or filter criteria</p>
    </div>
    @endif

    {{-- Pagination --}}
    @if($assets->hasPages())
    <div class="mt-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 px-5 py-3">
            {{ $assets->links() }}
        </div>
    </div>
    @endif
</div>
