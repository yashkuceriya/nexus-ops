<div class="min-h-screen bg-gray-50" wire:poll.15s>
    <div class="px-6 py-6 max-w-[1400px] mx-auto">

        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Work Orders</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage and track facility maintenance tasks</p>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                    Auto-refresh
                </span>
            </div>
        </div>

        {{-- Filter Row --}}
        <div class="flex flex-wrap items-center gap-3 mb-5">
            <div class="flex flex-wrap items-center gap-3 flex-1 min-w-0">
                {{-- Status Filter --}}
                <select wire:model.live="statusFilter"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                    <option value="">All Statuses</option>
                    @foreach(['pending','assigned','in_progress','on_hold','completed','verified','cancelled'] as $s)
                    <option value="{{ $s }}">{{ str_replace('_',' ',ucfirst($s)) }}</option>
                    @endforeach
                </select>

                {{-- Priority Filter --}}
                <select wire:model.live="priorityFilter"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                    <option value="">All Priorities</option>
                    @foreach(['emergency','critical','high','medium','low'] as $p)
                    <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                    @endforeach
                </select>

                {{-- Type Filter --}}
                <select wire:model.live="typeFilter"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                    <option value="">All Types</option>
                    @foreach(['corrective','preventive','inspection','sensor_alert','request'] as $t)
                    <option value="{{ $t }}">{{ str_replace('_',' ',ucfirst($t)) }}</option>
                    @endforeach
                </select>

                {{-- Search --}}
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search..."
                        class="w-56 rounded-lg border border-gray-300 bg-white pl-9 pr-3 py-2 text-sm text-gray-700 shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                </div>
            </div>

            {{-- Pending Review Button --}}
            <button wire:click="$set('statusFilter', 'pending')"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Pending Review
            </button>
        </div>

        {{-- Table --}}
        <div class="space-y-2">
            {{-- Table Header --}}
            <div class="hidden md:grid md:grid-cols-[80px_1fr_110px_110px_130px_150px_60px] gap-4 px-5 py-2.5 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <div>WO #</div>
                <div>Title</div>
                <div>Type</div>
                <div>Priority</div>
                <div>Status</div>
                <div>Assigned</div>
                <div class="text-right">Actions</div>
            </div>

            {{-- Table Rows --}}
            @forelse($workOrders as $wo)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 hover:shadow-md hover:border-gray-300/80 transition-all duration-150 {{ $wo->isSlaBreached() ? 'ring-1 ring-red-200 border-red-200' : '' }}">
                <div class="grid grid-cols-1 md:grid-cols-[80px_1fr_110px_110px_130px_150px_60px] gap-4 items-center px-5 py-4">

                    {{-- WO # --}}
                    <div>
                        <span class="text-sm font-mono font-semibold text-gray-900">{{ $wo->wo_number }}</span>
                        @if($wo->isSlaBreached())
                        <span class="ml-1 inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-bold bg-red-100 text-red-700">SLA</span>
                        @endif
                    </div>

                    {{-- Title + Asset --}}
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $wo->title }}</p>
                        @if($wo->asset)
                        <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $wo->asset->name }}</p>
                        @endif
                    </div>

                    {{-- Type Badge --}}
                    <div>
                        <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold bg-gray-100 text-gray-700 ring-1 ring-inset ring-gray-500/10">
                            {{ str_replace('_',' ',ucfirst($wo->type)) }}
                        </span>
                    </div>

                    {{-- Priority --}}
                    <div>
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold
                            {{ match($wo->priority) {
                                'emergency' => 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20',
                                'critical' => 'bg-orange-50 text-orange-700 ring-1 ring-inset ring-orange-600/20',
                                'high' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20',
                                'medium' => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20',
                                'low' => 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/20',
                                default => 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/20',
                            } }}">{{ ucfirst($wo->priority) }}</span>
                    </div>

                    {{-- Status --}}
                    <div>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium
                            {{ match($wo->status) {
                                'pending' => 'bg-red-50 text-red-700',
                                'assigned' => 'bg-blue-50 text-blue-700',
                                'in_progress' => 'bg-yellow-50 text-yellow-700',
                                'on_hold' => 'bg-purple-50 text-purple-700',
                                'completed' => 'bg-emerald-50 text-emerald-700',
                                'verified' => 'bg-green-50 text-green-700',
                                'cancelled' => 'bg-gray-100 text-gray-600',
                                default => 'bg-gray-50 text-gray-700',
                            } }}">
                            <span class="h-1.5 w-1.5 rounded-full
                                {{ match($wo->status) {
                                    'pending' => 'bg-red-500',
                                    'assigned' => 'bg-blue-500',
                                    'in_progress' => 'bg-yellow-500',
                                    'on_hold' => 'bg-purple-500',
                                    'completed' => 'bg-emerald-500',
                                    'verified' => 'bg-green-500',
                                    'cancelled' => 'bg-gray-400',
                                    default => 'bg-gray-500',
                                } }}"></span>
                            {{ str_replace('_',' ',ucfirst($wo->status)) }}
                        </span>
                    </div>

                    {{-- Assigned --}}
                    <div>
                        @if($wo->assignee)
                        <div class="flex items-center gap-2.5">
                            <div class="h-7 w-7 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-semibold text-indigo-700 flex-shrink-0">
                                {{ strtoupper(substr($wo->assignee->name, 0, 1)) }}{{ strtoupper(substr(strstr($wo->assignee->name, ' ') ?: '', 1, 1)) }}
                            </div>
                            <span class="text-sm text-gray-700 truncate">{{ $wo->assignee->name }}</span>
                        </div>
                        @else
                        <span class="text-sm text-gray-400">Unassigned</span>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="text-right">
                        <div x-data="{ open: false }" class="relative inline-block text-left">
                            <button @click="open = !open" class="rounded-lg p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4z"/></svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition
                                class="absolute right-0 z-10 mt-1 w-44 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black/5 focus:outline-none">
                                <div class="py-1">
                                    <a href="{{ route('work-orders.show', $wo) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">View Details</a>
                                    <a href="{{ route('work-orders.show', $wo) }}?edit=1" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Edit</a>
                                    <a href="{{ route('work-orders.show', $wo) }}?reassign=1" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Reassign</a>
                                    <button wire:click="$dispatch('cancel-work-order', { id: {{ $wo->id }} })" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-16 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <p class="mt-3 text-sm font-medium text-gray-500">No work orders found</p>
                <p class="mt-1 text-xs text-gray-400">Try adjusting your filters or search terms</p>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($workOrders->hasPages())
        <div class="mt-6 flex items-center justify-between">
            <p class="text-sm text-gray-600">
                Showing <span class="font-semibold text-gray-900">{{ $workOrders->firstItem() }}</span> to <span class="font-semibold text-gray-900">{{ $workOrders->lastItem() }}</span> of <span class="font-semibold text-gray-900">{{ $workOrders->total() }}</span> work orders
            </p>
            <div>
                {{ $workOrders->links() }}
            </div>
        </div>
        @elseif($workOrders->count() > 0)
        <div class="mt-6">
            <p class="text-sm text-gray-500">Showing {{ $workOrders->count() }} of {{ $workOrders->total() }} work orders</p>
        </div>
        @endif

    </div>
</div>
