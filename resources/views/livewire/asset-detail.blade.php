<div class="min-h-screen bg-gray-50" x-data="{ tab: 'overview' }">

    {{-- Back Link --}}
    <div class="mb-5">
        <a href="{{ route('assets.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Assets
        </a>
    </div>

    {{-- Header --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ $asset->name }}</h1>
                    @if($asset->system_type)
                    <span class="inline-flex items-center rounded-lg bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200">{{ $asset->system_type }}</span>
                    @endif
                    @if($asset->condition)
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium
                        {{ match($asset->condition) {
                            'excellent' => 'bg-emerald-50 text-emerald-700',
                            'good' => 'bg-green-50 text-green-700',
                            'fair' => 'bg-amber-50 text-amber-700',
                            'poor' => 'bg-orange-50 text-orange-700',
                            'critical' => 'bg-red-50 text-red-700',
                            default => 'bg-gray-50 text-gray-700',
                        } }}">
                        <span class="h-1.5 w-1.5 rounded-full
                            {{ match($asset->condition) {
                                'excellent' => 'bg-emerald-500',
                                'good' => 'bg-green-500',
                                'fair' => 'bg-amber-500',
                                'poor' => 'bg-orange-500',
                                'critical' => 'bg-red-500',
                                default => 'bg-gray-500',
                            } }}"></span>
                        {{ ucfirst($asset->condition) }}
                    </span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $asset->project?->name ?? 'No Project' }}
                    @if($asset->location) &middot; {{ $asset->location->name }} @endif
                </p>
            </div>
            <div class="flex-shrink-0 ml-6">
                <div class="w-24 h-24 bg-white rounded-xl border border-gray-200 p-2 flex items-center justify-center">
                    @try
                        {!! $this->generateQrCode() !!}
                    @catch (\Throwable $e)
                        <div class="text-center">
                            <svg class="mx-auto h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/></svg>
                            <p class="text-[10px] text-gray-400 mt-1">QR</p>
                        </div>
                    @endtry
                </div>
            </div>
        </div>
    </div>

    {{-- Info Grid --}}
    <div class="grid grid-cols-4 gap-4 mb-6">
        {{-- Manufacturer --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Manufacturer</p>
            <p class="mt-1 text-sm font-medium text-gray-900">{{ $asset->manufacturer ?? '—' }}</p>
        </div>
        {{-- Model --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Model</p>
            <p class="mt-1 text-sm font-medium text-gray-900">{{ $asset->model_number ?? '—' }}</p>
        </div>
        {{-- Serial Number --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Serial Number</p>
            <p class="mt-1 text-sm font-medium text-gray-900 font-mono">{{ $asset->serial_number ?? '—' }}</p>
        </div>
        {{-- Asset Tag --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Asset Tag</p>
            <p class="mt-1 text-sm font-medium text-gray-900 font-mono">{{ $asset->asset_tag ?? '—' }}</p>
        </div>
        {{-- Install Date --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Install Date</p>
            <p class="mt-1 text-sm font-medium text-gray-900">{{ $asset->install_date?->format('M d, Y') ?? '—' }}</p>
        </div>
        {{-- Warranty --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Warranty</p>
            @if($asset->warranty_expiry)
                @if($asset->isWarrantyActive())
                    <p class="mt-1 text-sm font-medium text-emerald-600">
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-500 mr-1"></span>
                        Active &mdash; {{ $asset->warranty_expiry->format('M d, Y') }}
                    </p>
                @else
                    <p class="mt-1 text-sm font-medium text-red-600">
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-red-500 mr-1"></span>
                        Expired &mdash; {{ $asset->warranty_expiry->format('M d, Y') }}
                    </p>
                @endif
            @else
                <p class="mt-1 text-sm font-medium text-gray-900">—</p>
            @endif
        </div>
        {{-- Runtime Hours --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Runtime Hours</p>
            <p class="mt-1 text-sm font-medium text-gray-900">{{ $asset->runtime_hours ? number_format($asset->runtime_hours) . ' hrs' : '—' }}</p>
        </div>
        {{-- Location --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Location</p>
            <p class="mt-1 text-sm font-medium text-gray-900">{{ $asset->location?->name ?? '—' }}</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200 px-1">
            <nav class="flex space-x-0">
                <button @click="tab = 'overview'"
                    :class="tab === 'overview' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="relative px-6 py-3.5 border-b-2 text-sm font-semibold transition-colors focus:outline-none">
                    Overview
                </button>
                <button @click="tab = 'work-orders'"
                    :class="tab === 'work-orders' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="relative px-6 py-3.5 border-b-2 text-sm font-semibold transition-colors focus:outline-none">
                    Work Orders
                    <span :class="tab === 'work-orders' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600'" class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium">{{ $this->openWorkOrders }}</span>
                </button>
                <button @click="tab = 'sensors'"
                    :class="tab === 'sensors' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="relative px-6 py-3.5 border-b-2 text-sm font-semibold transition-colors focus:outline-none">
                    Sensors
                    <span :class="tab === 'sensors' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600'" class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium">{{ $asset->sensorSources->count() }}</span>
                </button>
                <button @click="tab = 'maintenance'"
                    :class="tab === 'maintenance' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="relative px-6 py-3.5 border-b-2 text-sm font-semibold transition-colors focus:outline-none">
                    Maintenance
                    <span :class="tab === 'maintenance' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600'" class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium">{{ $asset->maintenanceSchedules->count() }}</span>
                </button>
                <button @click="tab = 'documents'"
                    :class="tab === 'documents' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="relative px-6 py-3.5 border-b-2 text-sm font-semibold transition-colors focus:outline-none">
                    Documents
                </button>
            </nav>
        </div>

        {{-- Overview Tab --}}
        <div x-show="tab === 'overview'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="p-6 space-y-6">

                {{-- Summary Cards Row --}}
                <div class="grid grid-cols-3 gap-4">
                    {{-- Commissioning Status --}}
                    <div class="rounded-xl border border-gray-200 p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="h-10 w-10 rounded-lg flex items-center justify-center
                                {{ match($asset->commissioning_status) {
                                    'completed' => 'bg-emerald-100',
                                    'in_progress' => 'bg-blue-100',
                                    'not_started' => 'bg-gray-100',
                                    'deferred' => 'bg-purple-100',
                                    default => 'bg-gray-100',
                                } }}">
                                <svg class="h-5 w-5
                                    {{ match($asset->commissioning_status) {
                                        'completed' => 'text-emerald-600',
                                        'in_progress' => 'text-blue-600',
                                        'not_started' => 'text-gray-500',
                                        'deferred' => 'text-purple-600',
                                        default => 'text-gray-500',
                                    } }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Commissioning</p>
                                <p class="text-sm font-semibold text-gray-900">{{ str_replace('_', ' ', ucfirst($asset->commissioning_status ?? 'Unknown')) }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Replacement Cost --}}
                    <div class="rounded-xl border border-gray-200 p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="h-10 w-10 rounded-lg bg-slate-100 flex items-center justify-center">
                                <svg class="h-5 w-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Replacement Cost</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $asset->replacement_cost ? '$' . number_format($asset->replacement_cost, 2) : '—' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Expected Life --}}
                    <div class="rounded-xl border border-gray-200 p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="h-10 w-10 rounded-lg bg-amber-100 flex items-center justify-center">
                                <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Expected Life</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $asset->expected_life_years ? $asset->expected_life_years . ' years' : '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Component Hierarchy --}}
                @if($asset->isComponent() || $asset->isSystem())
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Component Hierarchy</h3>

                    {{-- Breadcrumb path if this is a child component --}}
                    @if($asset->isComponent())
                    <div class="mb-4">
                        <nav class="flex items-center gap-1.5 text-sm">
                            @foreach(array_reverse($asset->hierarchyPath()) as $ancestor)
                            <a href="{{ route('assets.show', $ancestor->id) }}" class="text-emerald-600 hover:text-emerald-700 font-medium transition-colors">{{ $ancestor->name }}</a>
                            <svg class="h-4 w-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            @endforeach
                            <span class="text-gray-900 font-semibold">{{ $asset->name }}</span>
                        </nav>
                    </div>
                    @endif

                    {{-- Child component cards if this is a system --}}
                    @if($asset->isSystem())
                    <div class="grid grid-cols-3 gap-3">
                        @foreach($this->children as $child)
                        <a href="{{ route('assets.show', $child->id) }}" class="group block rounded-lg border border-gray-200 p-4 hover:border-emerald-300 hover:shadow-sm transition-all relative">
                            {{-- Tree connector line --}}
                            <div class="absolute -top-3 left-6 w-px h-3 bg-gray-300"></div>
                            <div class="absolute -top-3 left-6 w-4 h-px bg-gray-300" style="top: -1px;"></div>

                            <div class="flex items-start justify-between">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 group-hover:text-emerald-700 truncate transition-colors">{{ $child->name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $child->category }}</p>
                                </div>
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-medium flex-shrink-0 ml-2
                                    {{ match($child->condition) {
                                        'excellent' => 'bg-emerald-50 text-emerald-700',
                                        'good' => 'bg-green-50 text-green-700',
                                        'fair' => 'bg-amber-50 text-amber-700',
                                        'poor' => 'bg-orange-50 text-orange-700',
                                        'critical' => 'bg-red-50 text-red-700',
                                        default => 'bg-gray-50 text-gray-700',
                                    } }}">
                                    <span class="h-1 w-1 rounded-full
                                        {{ match($child->condition) {
                                            'excellent' => 'bg-emerald-500',
                                            'good' => 'bg-green-500',
                                            'fair' => 'bg-amber-500',
                                            'poor' => 'bg-orange-500',
                                            'critical' => 'bg-red-500',
                                            default => 'bg-gray-500',
                                        } }}"></span>
                                    {{ ucfirst($child->condition) }}
                                </span>
                            </div>
                            <div class="mt-2">
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-medium
                                    {{ match($child->commissioning_status) {
                                        'completed' => 'bg-emerald-50 text-emerald-700',
                                        'in_progress' => 'bg-blue-50 text-blue-700',
                                        'not_started' => 'bg-gray-100 text-gray-600',
                                        'deferred' => 'bg-purple-50 text-purple-700',
                                        default => 'bg-gray-100 text-gray-600',
                                    } }}">
                                    Cx: {{ str_replace('_', ' ', ucfirst($child->commissioning_status ?? 'Unknown')) }}
                                </span>
                            </div>
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endif

                {{-- Recent Issues --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Recent Issues</h3>
                    @if($asset->issues->count() > 0)
                    <div class="space-y-2">
                        @foreach($asset->issues->sortByDesc('created_at')->take(5) as $issue)
                        <div class="flex items-center justify-between rounded-lg border border-gray-100 px-4 py-3 hover:bg-gray-50/50 transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="h-2 w-2 rounded-full flex-shrink-0
                                    {{ match($issue->status) {
                                        'open' => 'bg-red-500',
                                        'in_progress' => 'bg-yellow-500',
                                        'work_completed' => 'bg-blue-500',
                                        'closed' => 'bg-emerald-500',
                                        'deferred' => 'bg-purple-500',
                                        default => 'bg-gray-500',
                                    } }}"></span>
                                <span class="text-sm text-gray-900 truncate">{{ $issue->title }}</span>
                            </div>
                            <div class="flex items-center gap-3 flex-shrink-0 ml-4">
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium
                                    {{ match($issue->priority) {
                                        'critical' => 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20',
                                        'high' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20',
                                        'medium' => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20',
                                        'low' => 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/20',
                                        default => 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/20',
                                    } }}">{{ ucfirst($issue->priority) }}</span>
                                <span class="text-xs text-gray-400">{{ $issue->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="rounded-xl border border-gray-100 py-8 text-center">
                        <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="mt-2 text-sm font-medium text-gray-500">No issues reported</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Work Orders Tab --}}
        <div x-show="tab === 'work-orders'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50/80">
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">WO Number</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Priority</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Assigned To</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Completed</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($this->allWorkOrders as $wo)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-4 text-sm font-mono text-gray-600">{{ $wo->wo_number }}</td>
                            <td class="px-5 py-4 text-sm font-medium text-gray-900">{{ $wo->title }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium
                                    {{ match($wo->status) {
                                        'open' => 'bg-red-50 text-red-700',
                                        'in_progress' => 'bg-yellow-50 text-yellow-700',
                                        'on_hold' => 'bg-purple-50 text-purple-700',
                                        'completed' => 'bg-emerald-50 text-emerald-700',
                                        'cancelled' => 'bg-gray-50 text-gray-500',
                                        default => 'bg-gray-50 text-gray-700',
                                    } }}">
                                    <span class="h-1.5 w-1.5 rounded-full
                                        {{ match($wo->status) {
                                            'open' => 'bg-red-500',
                                            'in_progress' => 'bg-yellow-500',
                                            'on_hold' => 'bg-purple-500',
                                            'completed' => 'bg-emerald-500',
                                            'cancelled' => 'bg-gray-400',
                                            default => 'bg-gray-500',
                                        } }}"></span>
                                    {{ str_replace('_', ' ', ucfirst($wo->status)) }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold
                                    {{ match($wo->priority) {
                                        'critical' => 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20',
                                        'high' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20',
                                        'medium' => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20',
                                        'low' => 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/20',
                                        default => 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/20',
                                    } }}">{{ ucfirst($wo->priority) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                @if($wo->assignee)
                                <div class="flex items-center gap-2">
                                    <div class="h-6 w-6 rounded-full bg-emerald-100 flex items-center justify-center text-[10px] font-semibold text-emerald-700 flex-shrink-0">
                                        {{ strtoupper(substr($wo->assignee->name, 0, 1)) }}{{ strtoupper(substr(strstr($wo->assignee->name, ' '), 1, 1)) }}
                                    </div>
                                    <span class="text-sm text-gray-700">{{ $wo->assignee->name }}</span>
                                </div>
                                @else
                                <span class="text-sm text-gray-400">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-500">{{ $wo->created_at->format('M d, Y') }}</td>
                            <td class="px-5 py-4 text-sm text-gray-500">{{ $wo->completed_at?->format('M d, Y') ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75"/></svg>
                                <p class="mt-2 text-sm font-medium text-gray-500">No work orders for this asset</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Sensors Tab --}}
        <div x-show="tab === 'sensors'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="p-6">
                @if($asset->sensorSources->count() > 0)
                <div class="space-y-4">
                    @foreach($asset->sensorSources as $sensor)
                    @php
                        $isAnomaly = $sensor->last_value !== null && $sensor->isValueOutOfRange((float) $sensor->last_value);
                        $percentage = null;
                        if ($sensor->threshold_min !== null && $sensor->threshold_max !== null && $sensor->last_value !== null) {
                            $range = (float)$sensor->threshold_max - (float)$sensor->threshold_min;
                            if ($range > 0) {
                                $percentage = min(100, max(0, (((float)$sensor->last_value - (float)$sensor->threshold_min) / $range) * 100));
                            }
                        }
                    @endphp
                    <div class="rounded-xl border {{ $isAnomaly ? 'border-red-300 bg-red-50/30' : 'border-gray-200' }} p-5 transition-colors">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-lg {{ $isAnomaly ? 'bg-red-100' : 'bg-emerald-100' }} flex items-center justify-center">
                                    <svg class="h-5 w-5 {{ $isAnomaly ? 'text-red-600' : 'text-emerald-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.348 14.651a3.75 3.75 0 010-5.303m5.304 0a3.75 3.75 0 010 5.303m-7.425 2.122a6.75 6.75 0 010-9.546m9.546 0a6.75 6.75 0 010 9.546"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $sensor->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $sensor->sensor_type }} &middot; {{ $sensor->unit }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold {{ $isAnomaly ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ $sensor->last_value !== null ? number_format((float)$sensor->last_value, 1) : '—' }}
                                    <span class="text-xs font-normal text-gray-500">{{ $sensor->unit }}</span>
                                </p>
                                <p class="text-[11px] text-gray-400">
                                    {{ $sensor->last_reading_at ? $sensor->last_reading_at->diffForHumans() : 'No readings' }}
                                </p>
                            </div>
                        </div>

                        @if($isAnomaly)
                        <div class="flex items-center gap-2 mb-3 px-3 py-2 rounded-lg bg-red-100 border border-red-200">
                            <svg class="h-4 w-4 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.168 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                            <span class="text-xs font-medium text-red-700">Anomaly detected: value is {{ $sensor->getAnomalyType((float)$sensor->last_value) === 'above_maximum' ? 'above maximum threshold' : 'below minimum threshold' }}</span>
                        </div>
                        @endif

                        {{-- Threshold Bar --}}
                        @if($sensor->threshold_min !== null && $sensor->threshold_max !== null)
                        <div>
                            <div class="flex items-center justify-between text-[11px] text-gray-500 mb-1">
                                <span>Min: {{ number_format((float)$sensor->threshold_min, 1) }}</span>
                                <span>Max: {{ number_format((float)$sensor->threshold_max, 1) }}</span>
                            </div>
                            <div class="h-2 w-full bg-gray-200 rounded-full overflow-hidden">
                                @if($percentage !== null)
                                <div class="h-full rounded-full transition-all {{ $isAnomaly ? 'bg-red-500' : 'bg-emerald-500' }}"
                                     style="width: {{ $percentage }}%"></div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <div class="mt-2 flex items-center gap-2">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium {{ $sensor->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $sensor->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            @if($sensor->alert_enabled)
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium bg-blue-50 text-blue-700">Alerts On</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="py-12 text-center">
                    <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.348 14.651a3.75 3.75 0 010-5.303m5.304 0a3.75 3.75 0 010 5.303m-7.425 2.122a6.75 6.75 0 010-9.546m9.546 0a6.75 6.75 0 010 9.546M12 12h.008v.008H12V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                    <p class="mt-2 text-sm font-medium text-gray-500">No sensors attached to this asset</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Maintenance Tab --}}
        <div x-show="tab === 'maintenance'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="p-6">
                @if($asset->maintenanceSchedules->count() > 0)
                <div class="space-y-4">
                    @foreach($asset->maintenanceSchedules as $schedule)
                    @php
                        $isOverdue = $schedule->isDue();
                    @endphp
                    <div class="rounded-xl border {{ $isOverdue ? 'border-red-300 bg-red-50/30' : 'border-gray-200' }} p-5">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold text-gray-900">{{ $schedule->name }}</p>
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium {{ $schedule->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                @if($schedule->description)
                                <p class="mt-1 text-xs text-gray-500">{{ $schedule->description }}</p>
                                @endif
                            </div>
                            <div class="text-right flex-shrink-0 ml-4">
                                <span class="inline-flex items-center rounded-lg px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-700">
                                    {{ ucfirst(str_replace('_', '-', $schedule->frequency)) }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-3 gap-4">
                            <div>
                                <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Next Due</p>
                                @if($schedule->next_due_date)
                                <p class="mt-0.5 text-sm font-medium {{ $isOverdue ? 'text-red-600' : 'text-gray-900' }}">
                                    @if($isOverdue)
                                        <svg class="inline h-3.5 w-3.5 text-red-500 mr-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.168 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                                        Overdue &mdash;
                                    @endif
                                    {{ $schedule->next_due_date->format('M d, Y') }}
                                </p>
                                @else
                                <p class="mt-0.5 text-sm text-gray-400">—</p>
                                @endif
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Last Completed</p>
                                <p class="mt-0.5 text-sm font-medium text-gray-900">{{ $schedule->last_completed_date?->format('M d, Y') ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Est. Duration</p>
                                <p class="mt-0.5 text-sm font-medium text-gray-900">{{ $schedule->estimated_duration_minutes ? $schedule->estimated_duration_minutes . ' min' : '—' }}</p>
                            </div>
                        </div>

                        {{-- Checklist Preview --}}
                        @if($schedule->checklist && count($schedule->checklist) > 0)
                        <div class="mt-4 pt-3 border-t border-gray-100">
                            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-2">Checklist Preview</p>
                            <div class="space-y-1">
                                @foreach(array_slice($schedule->checklist, 0, 4) as $item)
                                <div class="flex items-center gap-2 text-xs text-gray-600">
                                    <svg class="h-3.5 w-3.5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                                    <span>{{ is_string($item) ? $item : ($item['name'] ?? $item['task'] ?? $item['label'] ?? json_encode($item)) }}</span>
                                </div>
                                @endforeach
                                @if(count($schedule->checklist) > 4)
                                <p class="text-[11px] text-gray-400 ml-5">+{{ count($schedule->checklist) - 4 }} more items</p>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div class="py-12 text-center">
                    <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.42 15.17l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM13.5 10.5h-6"/></svg>
                    <p class="mt-2 text-sm font-medium text-gray-500">No maintenance schedules configured</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Documents Tab --}}
        <div x-show="tab === 'documents'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="p-6">
                <div class="py-16 text-center">
                    <div class="mx-auto h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                        <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-1">No documents yet</h3>
                    <p class="text-sm text-gray-500 max-w-sm mx-auto">Document management is coming soon. You will be able to upload and manage manuals, warranties, inspection reports, and other asset documentation here.</p>
                    <button disabled class="mt-4 inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-400 shadow-sm cursor-not-allowed">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                        Upload Document
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- QR Code Card --}}
    <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-start gap-6">
            <div class="flex-shrink-0 w-52 h-52 bg-white rounded-xl border border-gray-200 p-3 flex items-center justify-center">
                @try
                    {!! $this->generateQrCode() !!}
                @catch (\Throwable $e)
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/></svg>
                        <p class="text-xs text-gray-400 mt-2">QR code unavailable</p>
                    </div>
                @endtry
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-gray-900 mb-1">Asset QR Code</h3>
                <p class="text-sm text-gray-500 mb-3">Scan this code to quickly identify and look up this asset in the field.</p>
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Code:</span>
                    <code class="px-3 py-1 rounded-lg bg-gray-100 text-sm font-mono text-gray-700">{{ $asset->qr_code ?? $asset->generateQrCode() }}</code>
                </div>
                <a href="data:image/svg+xml;base64,{{ base64_encode($this->generateQrCode()) }}"
                   download="asset-{{ $asset->asset_tag ?? $asset->id }}-qr.svg"
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                    <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download QR Code
                </a>
            </div>
        </div>
    </div>

</div>
