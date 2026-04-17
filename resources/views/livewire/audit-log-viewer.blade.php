<div class="min-h-screen bg-gray-50">
    <div class="px-6 py-6 max-w-[1400px] mx-auto">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Audit Trail</h1>
            <p class="mt-1 text-sm text-gray-500">Activity log for compliance and security</p>
        </div>

        {{-- Filter Bar --}}
        <div class="flex flex-wrap items-center gap-3 mb-5">
            <select wire:model.live="actionFilter"
                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                <option value="">All Actions</option>
                @foreach($this->actions as $action)
                <option value="{{ $action }}">{{ str_replace('_', ' ', ucfirst($action)) }}</option>
                @endforeach
            </select>

            <select wire:model.live="entityFilter"
                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                <option value="">All Entity Types</option>
                @foreach($this->entityTypes as $type)
                <option value="{{ $type }}">{{ class_basename($type) }}</option>
                @endforeach
            </select>

            <select wire:model.live="userFilter"
                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                <option value="">All Users</option>
                @foreach($this->users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>

            <input type="date" wire:model.live="dateFrom"
                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
            <span class="text-sm text-gray-400">to</span>
            <input type="date" wire:model.live="dateTo"
                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">

            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search..."
                    class="w-56 rounded-lg border border-gray-300 bg-white pl-9 pr-3 py-2 text-sm text-gray-700 shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
            </div>
        </div>

        {{-- Table --}}
        <div class="card/80 overflow-hidden">
            {{-- Table Header --}}
            <div class="hidden md:grid md:grid-cols-[170px_180px_140px_1fr_130px_50px] gap-4 px-5 py-3 bg-gray-50 border-b border-gray-200 label-kicker">
                <div>Timestamp</div>
                <div>User</div>
                <div>Action</div>
                <div>Entity</div>
                <div>IP Address</div>
                <div></div>
            </div>

            {{-- Table Rows --}}
            @forelse($logs as $log)
            <div x-data="{ expanded: false }" class="border-b border-gray-100 last:border-b-0">
                <div class="grid grid-cols-1 md:grid-cols-[170px_180px_140px_1fr_130px_50px] gap-4 items-center px-5 py-3.5 hover:bg-gray-50/50 transition-colors cursor-pointer" @click="expanded = !expanded">

                    {{-- Timestamp --}}
                    <div>
                        <p class="text-sm text-gray-900">{{ $log->created_at->format('M d, Y') }}</p>
                        <p class="text-xs text-gray-400">{{ $log->created_at->format('g:i:s A') }}</p>
                    </div>

                    {{-- User --}}
                    <div class="flex items-center gap-2.5">
                        @if($log->user)
                        <div class="h-7 w-7 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-semibold text-indigo-700 flex-shrink-0">
                            {{ strtoupper(substr($log->user->name, 0, 1)) }}{{ strtoupper(substr(strstr($log->user->name, ' ') ?: '', 1, 1)) }}
                        </div>
                        <span class="text-sm text-gray-700 truncate">{{ $log->user->name }}</span>
                        @else
                        <div class="h-7 w-7 rounded-full bg-gray-100 flex items-center justify-center text-xs font-semibold text-gray-400 flex-shrink-0">?</div>
                        <span class="text-sm text-gray-400">System</span>
                        @endif
                    </div>

                    {{-- Action Badge --}}
                    <div>
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold
                            {{ match($log->action) {
                                'created' => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20',
                                'updated' => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20',
                                'deleted' => 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20',
                                'status_changed' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20',
                                default => 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/20',
                            } }}">
                            {{ str_replace('_', ' ', ucfirst($log->action)) }}
                        </span>
                    </div>

                    {{-- Entity --}}
                    <div class="min-w-0">
                        <p class="text-sm text-gray-900 truncate">
                            {{ class_basename($log->auditable_type) }}
                            @if($log->auditable_type === 'App\\Models\\WorkOrder' && $log->auditable)
                                #{{ $log->auditable->wo_number ?? $log->auditable_id }}
                            @else
                                #{{ $log->auditable_id }}
                            @endif
                        </p>
                    </div>

                    {{-- IP Address --}}
                    <div>
                        <span class="text-sm text-gray-500 font-mono">{{ $log->ip_address ?? '-' }}</span>
                    </div>

                    {{-- Expand Toggle --}}
                    <div class="text-right">
                        <svg class="h-4 w-4 text-gray-400 transition-transform duration-200 inline-block" :class="{ 'rotate-180': expanded }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>

                {{-- Expanded Detail --}}
                <div x-show="expanded" x-collapse x-cloak class="px-5 pb-4">
                    @php
                        $oldValues = $log->old_values ?? [];
                        $newValues = $log->new_values ?? [];
                        $allKeys = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));
                        $changedKeys = array_filter($allKeys, function($key) use ($oldValues, $newValues) {
                            return ($oldValues[$key] ?? null) !== ($newValues[$key] ?? null);
                        });
                    @endphp

                    @if(count($changedKeys) > 0)
                    <div class="grid grid-cols-2 gap-4 mt-1">
                        {{-- Old Values --}}
                        <div>
                            <p class="label-kicker mb-2">Previous Values</p>
                            <div class="rounded-lg bg-red-50 border border-red-200/60 p-3 space-y-1.5">
                                @foreach($changedKeys as $key)
                                <div class="flex items-start gap-2">
                                    <span class="text-xs font-medium text-red-700 min-w-[100px]">{{ str_replace('_', ' ', ucfirst($key)) }}:</span>
                                    <span class="text-xs text-red-600 break-all">{{ is_array($oldValues[$key] ?? null) ? json_encode($oldValues[$key]) : ($oldValues[$key] ?? '(empty)') }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- New Values --}}
                        <div>
                            <p class="label-kicker mb-2">New Values</p>
                            <div class="rounded-lg bg-emerald-50 border border-emerald-200/60 p-3 space-y-1.5">
                                @foreach($changedKeys as $key)
                                <div class="flex items-start gap-2">
                                    <span class="text-xs font-medium text-emerald-700 min-w-[100px]">{{ str_replace('_', ' ', ucfirst($key)) }}:</span>
                                    <span class="text-xs text-emerald-600 break-all">{{ is_array($newValues[$key] ?? null) ? json_encode($newValues[$key]) : ($newValues[$key] ?? '(empty)') }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @else
                    <p class="text-xs text-gray-400 italic">No detailed changes recorded for this entry.</p>
                    @endif
                </div>
            </div>
            @empty
            <div class="px-5 py-16 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                <p class="mt-3 text-sm font-medium text-gray-500">No audit logs found</p>
                <p class="mt-1 text-xs text-gray-400">Try adjusting your filters or search terms</p>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
        <div class="mt-6 flex items-center justify-between">
            <p class="text-sm text-gray-600">
                Showing <span class="font-semibold text-gray-900">{{ $logs->firstItem() }}</span> to <span class="font-semibold text-gray-900">{{ $logs->lastItem() }}</span> of <span class="font-semibold text-gray-900">{{ $logs->total() }}</span> entries
            </p>
            <div>
                {{ $logs->links() }}
            </div>
        </div>
        @elseif($logs->count() > 0)
        <div class="mt-6">
            <p class="text-sm text-gray-500">Showing {{ $logs->count() }} of {{ $logs->total() }} entries</p>
        </div>
        @endif

    </div>
</div>
