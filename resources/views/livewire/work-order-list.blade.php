<div class="space-y-6" wire:poll.15s>
    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <p class="label-kicker">Maintenance</p>
            <h1 class="text-2xl font-bold tracking-tight text-ink mt-1">Work Orders</h1>
            <p class="text-[13px] text-ink-muted mt-0.5">Manage and track facility maintenance tasks.</p>
        </div>
        <span class="chip chip-pass">
            <span class="dot dot-pass animate-pulse"></span>
            Auto-refresh
        </span>
    </div>

    {{-- Filter Row --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex flex-wrap items-center gap-3 flex-1 min-w-0">
            <select wire:model.live="statusFilter"
                class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-[13px] text-ink focus:border-accent-500 focus:ring-1 focus:ring-accent-500 transition-colors">
                <option value="">All Statuses</option>
                @foreach(['pending','assigned','in_progress','on_hold','completed','verified','cancelled'] as $s)
                <option value="{{ $s }}">{{ str_replace('_',' ',ucfirst($s)) }}</option>
                @endforeach
            </select>

            <select wire:model.live="priorityFilter"
                class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-[13px] text-ink focus:border-accent-500 focus:ring-1 focus:ring-accent-500 transition-colors">
                <option value="">All Priorities</option>
                @foreach(['emergency','critical','high','medium','low'] as $p)
                <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                @endforeach
            </select>

            <select wire:model.live="typeFilter"
                class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-[13px] text-ink focus:border-accent-500 focus:ring-1 focus:ring-accent-500 transition-colors">
                <option value="">All Types</option>
                @foreach(['corrective','preventive','inspection','sensor_alert','request'] as $t)
                <option value="{{ $t }}">{{ str_replace('_',' ',ucfirst($t)) }}</option>
                @endforeach
            </select>

            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-ink-soft" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search..."
                    class="w-56 rounded-lg border border-slate-200 bg-white pl-9 pr-3 py-2 text-[13px] text-ink placeholder-slate-400 focus:border-accent-500 focus:ring-1 focus:ring-accent-500 transition-colors">
            </div>
        </div>

        <button wire:click="$set('statusFilter', 'pending')" class="btn-primary inline-flex items-center gap-2">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Pending Review
        </button>
    </div>

    {{-- Table --}}
    <div class="space-y-2">
        <div class="hidden md:grid md:grid-cols-[80px_1fr_110px_110px_130px_150px_60px] gap-4 px-5 py-2 label-kicker">
            <div>WO #</div>
            <div>Title</div>
            <div>Type</div>
            <div>Priority</div>
            <div>Status</div>
            <div>Assigned</div>
            <div class="text-right">Actions</div>
        </div>

        @forelse($workOrders as $wo)
        @php
            $statusChip = match($wo->status) {
                'completed','verified' => 'chip-pass',
                'cancelled' => 'chip-fail',
                'in_progress' => 'chip-run',
                'on_hold' => 'chip-warn',
                'pending','assigned' => 'chip-pending',
                default => 'chip-pending',
            };
            $priorityChip = match($wo->priority) {
                'emergency','critical' => 'chip-fail',
                'high' => 'chip-warn',
                'medium' => 'chip-accent',
                'low' => 'chip-pending',
                default => 'chip-pending',
            };
        @endphp
        <div class="card {{ $wo->isSlaBreached() ? 'ring-1 ring-red-200 border-red-200' : '' }}">
            <div class="grid grid-cols-1 md:grid-cols-[80px_1fr_110px_110px_130px_150px_60px] gap-4 items-center px-5 py-4">
                <div>
                    <span class="mono font-semibold text-ink">{{ $wo->wo_number }}</span>
                    @if($wo->isSlaBreached())
                    <span class="ml-1 chip chip-fail">SLA</span>
                    @endif
                </div>

                <div class="min-w-0">
                    <p class="text-[13px] font-semibold text-ink truncate">{{ $wo->title }}</p>
                    @if($wo->asset)
                    <p class="text-[11px] text-ink-soft mt-0.5 truncate">{{ $wo->asset->name }}</p>
                    @endif
                </div>

                <div>
                    <span class="chip chip-pending">{{ str_replace('_',' ',ucfirst($wo->type)) }}</span>
                </div>

                <div>
                    <span class="chip {{ $priorityChip }}">{{ ucfirst($wo->priority) }}</span>
                </div>

                <div>
                    <span class="chip {{ $statusChip }}">
                        <span class="dot {{ str_replace('chip-','dot-',$statusChip) }}"></span>
                        {{ str_replace('_',' ',ucfirst($wo->status)) }}
                    </span>
                </div>

                <div>
                    @if($wo->assignee)
                    <div class="flex items-center gap-2.5">
                        <div class="h-7 w-7 rounded-full bg-accent-100 flex items-center justify-center text-[11px] font-semibold text-accent-700 flex-shrink-0">
                            {{ strtoupper(substr($wo->assignee->name, 0, 1)) }}{{ strtoupper(substr(strstr($wo->assignee->name, ' ') ?: '', 1, 1)) }}
                        </div>
                        <span class="text-[13px] text-ink-muted truncate">{{ $wo->assignee->name }}</span>
                    </div>
                    @else
                    <span class="text-[13px] text-ink-soft">Unassigned</span>
                    @endif
                </div>

                <div class="text-right">
                    <div x-data="{ open: false }" class="relative inline-block text-left">
                        <button @click="open = !open" class="rounded-lg p-1.5 text-ink-soft hover:text-ink hover:bg-slate-100 transition-colors">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4z"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition
                            class="absolute right-0 z-10 mt-1 w-44 origin-top-right rounded-lg bg-white border border-slate-200 shadow-sm focus:outline-none">
                            <div class="py-1">
                                <a href="{{ route('work-orders.show', $wo) }}" class="block px-4 py-2 text-[13px] text-ink-muted hover:bg-slate-50">View Details</a>
                                <a href="{{ route('work-orders.show', $wo) }}?edit=1" class="block px-4 py-2 text-[13px] text-ink-muted hover:bg-slate-50">Edit</a>
                                <a href="{{ route('work-orders.show', $wo) }}?reassign=1" class="block px-4 py-2 text-[13px] text-ink-muted hover:bg-slate-50">Reassign</a>
                                <button wire:click="$dispatch('cancel-work-order', { id: {{ $wo->id }} })"
                                        wire:confirm="Cancel WO-{{ $wo->number ?? $wo->id }}? This will stop any assigned technician and close the work order."
                                        class="block w-full text-left px-4 py-2 text-[13px] text-red-600 hover:bg-red-50">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        @empty
        <div class="card px-5 py-16 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="mt-3 text-[13px] font-semibold text-ink-muted">No work orders found</p>
            <p class="mt-1 text-[11px] text-ink-soft">Try adjusting your filters or search terms.</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($workOrders->hasPages())
    <div class="flex items-center justify-between">
        <p class="text-[13px] text-ink-muted">
            Showing <span class="font-semibold text-ink mono">{{ $workOrders->firstItem() }}</span> to <span class="font-semibold text-ink mono">{{ $workOrders->lastItem() }}</span> of <span class="font-semibold text-ink mono">{{ $workOrders->total() }}</span> work orders
        </p>
        <div>
            {{ $workOrders->links() }}
        </div>
    </div>
    @elseif($workOrders->count() > 0)
    <p class="text-[13px] text-ink-soft">Showing {{ $workOrders->count() }} of {{ $workOrders->total() }} work orders.</p>
    @endif
</div>
