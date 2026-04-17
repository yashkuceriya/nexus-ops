<div class="space-y-6">
    @if(session('success'))
    <div class="card p-4 bg-emerald-50 border-emerald-200 text-[13px] text-emerald-700 font-semibold">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="card p-4 bg-red-50 border-red-200 text-[13px] text-red-700 font-semibold">
        {{ session('error') }}
    </div>
    @endif

    {{-- Back Link --}}
    <div>
        <a href="{{ route('work-orders.index') }}" class="inline-flex items-center gap-1.5 text-[13px] text-ink-muted hover:text-ink transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Work Orders
        </a>
    </div>

    {{-- Header --}}
    <div>
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <span class="mono font-semibold text-ink bg-slate-100 rounded-md px-2.5 py-1">{{ $workOrder->wo_number }}</span>

                        {{-- Priority Badge --}}
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold
                            {{ match($workOrder->priority) {
                                'emergency' => 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20',
                                'critical' => 'bg-orange-50 text-orange-700 ring-1 ring-inset ring-orange-600/20',
                                'high' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20',
                                'medium' => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20',
                                'low' => 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/20',
                                default => 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/20',
                            } }}">{{ ucfirst($workOrder->priority) }}</span>

                        {{-- Status Badge --}}
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium
                            {{ match($workOrder->status) {
                                'open' => 'bg-red-50 text-red-700',
                                'assigned' => 'bg-blue-50 text-blue-700',
                                'in_progress' => 'bg-yellow-50 text-yellow-700',
                                'on_hold' => 'bg-purple-50 text-purple-700',
                                'completed' => 'bg-emerald-50 text-emerald-700',
                                'verified' => 'bg-green-50 text-green-700',
                                'cancelled' => 'bg-gray-100 text-gray-600',
                                default => 'bg-gray-50 text-gray-700',
                            } }}">
                            <span class="h-1.5 w-1.5 rounded-full
                                {{ match($workOrder->status) {
                                    'open' => 'bg-red-500',
                                    'assigned' => 'bg-blue-500',
                                    'in_progress' => 'bg-yellow-500',
                                    'on_hold' => 'bg-purple-500',
                                    'completed' => 'bg-emerald-500',
                                    'verified' => 'bg-green-500',
                                    'cancelled' => 'bg-gray-400',
                                    default => 'bg-gray-500',
                                } }}"></span>
                            {{ str_replace('_', ' ', ucfirst($workOrder->status)) }}
                        </span>
                    </div>
                    <h1 class="text-2xl font-bold text-ink tracking-tight mt-2">{{ $workOrder->title }}</h1>
                    <p class="mt-1 text-sm text-gray-500">Created {{ $workOrder->created_at->format('M d, Y \a\t g:i A') }}</p>
                </div>
            </div>
        </div>

        {{-- Status Timeline --}}
        @php
            $stages = [
                'open'        => ['label' => 'Open',        'field' => 'created_at'],
                'assigned'    => ['label' => 'Assigned',    'field' => null],
                'in_progress' => ['label' => 'In Progress', 'field' => 'started_at'],
                'completed'   => ['label' => 'Completed',   'field' => 'completed_at'],
                'verified'    => ['label' => 'Verified',    'field' => 'verified_at'],
            ];
            $statusOrder = array_keys($stages);
            $currentIndex = array_search($workOrder->status, $statusOrder);
            if ($currentIndex === false) $currentIndex = -1;
            $isCancelled = $workOrder->status === 'cancelled';
            $isOnHold = $workOrder->status === 'on_hold';
        @endphp
        <div class="card px-6 py-5 mb-6">
            @if($isCancelled)
            <div class="flex items-center gap-2 mb-3">
                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/></svg>
                <span class="text-sm font-semibold text-gray-600">This work order has been cancelled</span>
            </div>
            @endif
            @if($isOnHold)
            <div class="flex items-center gap-2 mb-3">
                <svg class="h-5 w-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                <span class="text-sm font-semibold text-amber-700">This work order is on hold</span>
            </div>
            @endif
            <div class="flex items-center justify-between">
                @foreach($stages as $stageKey => $stageMeta)
                    @php
                        $stageIndex = array_search($stageKey, $statusOrder);
                        $isPast = $stageIndex < $currentIndex;
                        $isCurrent = $stageIndex === $currentIndex && !$isCancelled && !$isOnHold;
                        $isCurrentOnHold = $isOnHold && $stageKey === 'in_progress';
                        $timestamp = $stageMeta['field'] && $workOrder->{$stageMeta['field']} ? $workOrder->{$stageMeta['field']}->format('M d, g:i A') : null;
                    @endphp
                    <div class="flex flex-col items-center flex-1 relative">
                        {{-- Connector Line --}}
                        @if(!$loop->first)
                        <div class="absolute top-4 right-1/2 w-full h-0.5 -translate-y-1/2
                            {{ $isPast || $isCurrent ? 'bg-emerald-400' : 'bg-gray-200' }}"></div>
                        @endif

                        {{-- Circle --}}
                        <div class="relative z-10 flex items-center justify-center h-8 w-8 rounded-full border-2 transition-all
                            {{ $isPast ? 'bg-emerald-500 border-emerald-500 text-white' : '' }}
                            {{ $isCurrent || $isCurrentOnHold ? 'bg-emerald-500 border-emerald-500 text-white ring-4 ring-emerald-100' : '' }}
                            {{ !$isPast && !$isCurrent && !$isCurrentOnHold ? 'bg-white border-gray-300 text-gray-400' : '' }}">
                            @if($isPast)
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            @elseif($isCurrent || $isCurrentOnHold)
                                <span class="h-2.5 w-2.5 rounded-full bg-white"></span>
                            @else
                                <span class="text-xs font-semibold">{{ $stageIndex + 1 }}</span>
                            @endif
                        </div>

                        {{-- Label --}}
                        <span class="mt-2 text-xs font-semibold {{ $isPast || $isCurrent || $isCurrentOnHold ? 'text-gray-900' : 'text-gray-400' }}">{{ $stageMeta['label'] }}</span>
                        @if($timestamp)
                        <span class="text-[10px] text-gray-400 mt-0.5">{{ $timestamp }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Two Column Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- LEFT COLUMN (2/3) --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Description Card --}}
                @if($workOrder->description)
                <div class="card overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Description</h3>
                    </div>
                    <div class="px-5 py-4">
                        <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $workOrder->description }}</p>
                    </div>
                </div>
                @endif

                {{-- Details Card --}}
                <div class="card overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Details</h3>
                    </div>
                    <div class="px-5 py-4">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                            <div>
                                <dt class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Project</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $workOrder->project?->name ?? '---' }}</dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Asset</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($workOrder->asset)
                                        {{ $workOrder->asset->name }}
                                        <span class="text-xs text-gray-400 font-mono ml-1">{{ $workOrder->asset->asset_tag }}</span>
                                    @else
                                        ---
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Location</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $workOrder->location?->name ?? '---' }}</dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Type</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold bg-gray-100 text-gray-700 ring-1 ring-inset ring-gray-500/10">
                                        {{ str_replace('_', ' ', ucfirst($workOrder->type)) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Source</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($workOrder->source ?? 'manual') }}</dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">SLA Deadline</dt>
                                <dd class="mt-1 text-sm">
                                    @if($workOrder->sla_deadline)
                                        <span class="{{ $workOrder->isSlaBreached() ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                                            {{ $workOrder->sla_deadline->format('M d, Y g:i A') }}
                                        </span>
                                        @if($workOrder->isSlaBreached())
                                            <span class="ml-1.5 inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-bold bg-red-100 text-red-700">BREACHED</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">---</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Assigned To</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($workOrder->assignee)
                                    <div class="flex items-center gap-2">
                                        <div class="h-6 w-6 rounded-full bg-indigo-100 flex items-center justify-center text-[10px] font-semibold text-indigo-700 flex-shrink-0">
                                            {{ strtoupper(substr($workOrder->assignee->name, 0, 1)) }}{{ strtoupper(substr(strstr($workOrder->assignee->name, ' ') ?: '', 1, 1)) }}
                                        </div>
                                        {{ $workOrder->assignee->name }}
                                    </div>
                                    @else
                                        <span class="text-gray-400">Unassigned</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Created By</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($workOrder->creator)
                                    <div class="flex items-center gap-2">
                                        <div class="h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center text-[10px] font-semibold text-gray-600 flex-shrink-0">
                                            {{ strtoupper(substr($workOrder->creator->name, 0, 1)) }}{{ strtoupper(substr(strstr($workOrder->creator->name, ' ') ?: '', 1, 1)) }}
                                        </div>
                                        {{ $workOrder->creator->name }}
                                    </div>
                                    @else
                                        <span class="text-gray-400">System</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- Linked Issue Card --}}
                @if($workOrder->issue)
                <div class="card overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Linked Issue</h3>
                    </div>
                    <div class="px-5 py-4">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $workOrder->issue->title }}</p>
                                <div class="flex items-center gap-3 mt-2">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium
                                        {{ match($workOrder->issue->status) {
                                            'open' => 'bg-red-50 text-red-700',
                                            'in_progress' => 'bg-yellow-50 text-yellow-700',
                                            'work_completed' => 'bg-blue-50 text-blue-700',
                                            'closed' => 'bg-emerald-50 text-emerald-700',
                                            'deferred' => 'bg-purple-50 text-purple-700',
                                            default => 'bg-gray-50 text-gray-700',
                                        } }}">
                                        <span class="h-1.5 w-1.5 rounded-full
                                            {{ match($workOrder->issue->status) {
                                                'open' => 'bg-red-500',
                                                'in_progress' => 'bg-yellow-500',
                                                'work_completed' => 'bg-blue-500',
                                                'closed' => 'bg-emerald-500',
                                                'deferred' => 'bg-purple-500',
                                                default => 'bg-gray-500',
                                            } }}"></span>
                                        {{ str_replace('_', ' ', ucfirst($workOrder->issue->status)) }}
                                    </span>
                                    @if($workOrder->issue->priority)
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold
                                        {{ match($workOrder->issue->priority) {
                                            'critical' => 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20',
                                            'high' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20',
                                            'medium' => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20',
                                            'low' => 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/20',
                                            default => 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/20',
                                        } }}">{{ ucfirst($workOrder->issue->priority) }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-shrink-0 ml-4">
                                <span class="inline-flex items-center rounded-md bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-600/20">Issue #{{ $workOrder->issue->id }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Activity Log Card --}}
                <div class="card overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Activity Log</h3>
                    </div>
                    <div class="divide-y divide-gray-50">
                        @forelse($this->auditLogs as $log)
                        <div class="px-5 py-3.5 flex items-start gap-3">
                            <div class="flex-shrink-0 mt-0.5">
                                @if($log->user)
                                <div class="h-7 w-7 rounded-full bg-indigo-100 flex items-center justify-center text-[10px] font-semibold text-indigo-700">
                                    {{ strtoupper(substr($log->user->name, 0, 1)) }}{{ strtoupper(substr(strstr($log->user->name, ' ') ?: '', 1, 1)) }}
                                </div>
                                @else
                                <div class="h-7 w-7 rounded-full bg-gray-100 flex items-center justify-center">
                                    <svg class="h-3.5 w-3.5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/></svg>
                                </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm text-gray-700">
                                        <span class="font-medium">{{ $log->user?->name ?? 'System' }}</span>
                                        @php
                                            $actionLabel = match($log->action) {
                                                'work_order_created' => 'created this work order',
                                                'work_order_status_changed' => 'changed status to ' . ($log->new_values['status'] ?? 'unknown'),
                                                'work_order_assigned' => 'assigned to ' . ($log->new_values['assignee_name'] ?? 'someone'),
                                                'work_order_updated' => 'updated work order details',
                                                default => str_replace('_', ' ', $log->action),
                                            };
                                        @endphp
                                        {{ $actionLabel }}
                                    </p>
                                    <span class="text-[11px] text-gray-400 flex-shrink-0 ml-3">{{ $log->created_at->diffForHumans() }}</span>
                                </div>
                                @if($log->action === 'work_order_status_changed' && isset($log->old_values['status']))
                                <div class="mt-1 flex items-center gap-1.5 text-xs text-gray-500">
                                    <span>{{ str_replace('_', ' ', ucfirst($log->old_values['status'])) }}</span>
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                    <span class="font-medium text-gray-700">{{ str_replace('_', ' ', ucfirst($log->new_values['status'] ?? '')) }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="px-5 py-10 text-center">
                            <svg class="mx-auto h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="mt-2 text-sm text-gray-500">No activity recorded yet</p>
                        </div>
                        @endforelse
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN (1/3) --}}
            <div class="space-y-6">

                {{-- Actions Card --}}
                @if(count($this->allowedTransitions) > 0)
                <div class="card overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Actions</h3>
                    </div>
                    <div class="px-5 py-4 space-y-2.5">
                        @foreach($this->allowedTransitions as $transition)
                        <button
                            wire:click="transitionStatus('{{ $transition['status'] }}')"
                            wire:loading.attr="disabled"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2
                            {{ match($transition['color']) {
                                'blue'    => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
                                'emerald' => 'bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500',
                                'green'   => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
                                'amber'   => 'bg-amber-500 text-white hover:bg-amber-600 focus:ring-amber-400',
                                'red'     => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
                                default   => 'bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500',
                            } }}">
                            @if($transition['status'] === 'in_progress')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                            @elseif($transition['status'] === 'completed')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            @elseif($transition['status'] === 'verified')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @elseif($transition['status'] === 'on_hold')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @elseif($transition['status'] === 'cancelled')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            @endif
                            {{ $transition['label'] }}
                        </button>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Assignment Card --}}
                <div class="card overflow-hidden" x-data="{ showReassign: false }">
                    <div class="px-5 py-3.5 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Assignment</h3>
                    </div>
                    <div class="px-5 py-4">
                        @if($workOrder->assignee)
                        <div class="flex items-center gap-3 mb-3">
                            <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-sm font-semibold text-indigo-700 flex-shrink-0">
                                {{ strtoupper(substr($workOrder->assignee->name, 0, 1)) }}{{ strtoupper(substr(strstr($workOrder->assignee->name, ' ') ?: '', 1, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $workOrder->assignee->name }}</p>
                                <p class="text-xs text-gray-500">{{ $workOrder->assignee->email }}</p>
                            </div>
                        </div>
                        @else
                        <div class="flex items-center gap-3 mb-3">
                            <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Unassigned</p>
                                <p class="text-xs text-gray-400">No one is assigned yet</p>
                            </div>
                        </div>
                        @endif

                        <button @click="showReassign = !showReassign"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                            <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            {{ $workOrder->assignee ? 'Reassign' : 'Assign' }}
                        </button>

                        <div x-show="showReassign" x-transition x-cloak class="mt-3">
                            <select wire:change="assignTo($event.target.value)" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                <option value="">Select a user...</option>
                                @foreach($this->availableUsers as $user)
                                <option value="{{ $user->id }}" {{ $workOrder->assigned_to === $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Vendor Assignment Card --}}
                <div class="card overflow-hidden" x-data="{ showVendorSelect: false }">
                    <div class="px-5 py-3.5 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Vendor Assignment</h3>
                    </div>
                    <div class="px-5 py-4">
                        @if($workOrder->vendor)
                        <div class="flex items-center gap-3 mb-3">
                            <div class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center text-sm font-semibold text-emerald-700 flex-shrink-0">
                                {{ strtoupper(substr($workOrder->vendor->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('vendors.show', $workOrder->vendor->id) }}" class="text-sm font-medium text-gray-900 hover:text-emerald-600 transition-colors">{{ $workOrder->vendor->name }}</a>
                                <p class="text-xs text-gray-500">{{ $workOrder->vendor->contact_name ?? $workOrder->vendor->email ?? '' }}</p>
                                @if($workOrder->vendor->trade_specialties && count($workOrder->vendor->trade_specialties) > 0)
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach($workOrder->vendor->trade_specialties as $trade)
                                    <span class="inline-flex items-center rounded-full px-1.5 py-0.5 text-[9px] font-semibold bg-gray-100 text-gray-600">{{ $trade }}</span>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- NTE Check --}}
                        @php
                            $activeContract = $workOrder->vendor->getActiveContract();
                            $nteLimit = $activeContract?->nte_limit;
                            $actualCost = $workOrder->actual_cost ?? $workOrder->estimated_cost;
                            $nteExceeded = $nteLimit && $actualCost && $actualCost > $nteLimit;
                        @endphp
                        @if($nteLimit)
                        <div class="rounded-lg border {{ $nteExceeded ? 'border-red-200 bg-red-50' : 'border-gray-100 bg-gray-50' }} p-3 mt-2">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">NTE Limit</span>
                                <span class="text-sm font-bold {{ $nteExceeded ? 'text-red-700' : 'text-gray-900' }}">${{ number_format($nteLimit, 2) }}</span>
                            </div>
                            @if($actualCost)
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Current Cost</span>
                                <span class="text-sm font-semibold {{ $nteExceeded ? 'text-red-600' : 'text-gray-700' }}">${{ number_format($actualCost, 2) }}</span>
                            </div>
                            @if($nteExceeded)
                            <div class="flex items-center gap-1.5 mt-2 text-xs font-semibold text-red-700">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                                NTE limit exceeded - approval required
                            </div>
                            @endif
                            @endif
                        </div>
                        @endif

                        @if($workOrder->vendor->rating)
                        <div class="flex items-center gap-1 mt-3">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= floor($workOrder->vendor->rating))
                                    <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @else
                                    <svg class="w-3.5 h-3.5 text-slate-200" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endif
                            @endfor
                            <span class="text-xs font-medium text-gray-500 ml-0.5">{{ number_format($workOrder->vendor->rating, 1) }}</span>
                        </div>
                        @endif
                        @else
                        <div class="flex items-center gap-3 mb-3">
                            <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">No vendor assigned</p>
                                <p class="text-xs text-gray-400">Assign a vendor to this work order</p>
                            </div>
                        </div>
                        @endif

                        <button @click="showVendorSelect = !showVendorSelect"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors mt-2">
                            <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                            {{ $workOrder->vendor ? 'Change Vendor' : 'Assign Vendor' }}
                        </button>

                        <div x-show="showVendorSelect" x-transition x-cloak class="mt-3">
                            <select wire:change="assignVendor($event.target.value)" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                                <option value="">Select a vendor...</option>
                                <option value="0">-- Remove vendor --</option>
                                @foreach($this->availableVendors as $v)
                                <option value="{{ $v->id }}" {{ $workOrder->vendor_id === $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Cost Tracking Card --}}
                <div class="card overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Cost Tracking</h3>
                    </div>
                    <div class="px-5 py-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Estimated Cost</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $workOrder->estimated_cost ? '$' . number_format($workOrder->estimated_cost, 2) : '---' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Actual Cost</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $workOrder->actual_cost ? '$' . number_format($workOrder->actual_cost, 2) : '---' }}</span>
                        </div>
                        @if($workOrder->estimated_cost && $workOrder->actual_cost)
                        @php
                            $variance = $workOrder->actual_cost - $workOrder->estimated_cost;
                            $variancePercent = $workOrder->estimated_cost > 0 ? ($variance / $workOrder->estimated_cost) * 100 : 0;
                        @endphp
                        <div class="border-t border-gray-100 pt-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Variance</span>
                                <span class="text-sm font-semibold {{ $variance > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                    {{ $variance > 0 ? '+' : '' }}${{ number_format(abs($variance), 2) }}
                                    ({{ $variance > 0 ? '+' : '' }}{{ number_format($variancePercent, 1) }}%)
                                </span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- SLA Card --}}
                @if($workOrder->sla_deadline)
                <div class="card overflow-hidden {{ $workOrder->isSlaBreached() ? 'ring-1 ring-red-200' : '' }}">
                    <div class="px-5 py-3.5 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">SLA</h3>
                    </div>
                    <div class="px-5 py-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Deadline</span>
                            <span class="text-sm text-gray-900">{{ $workOrder->sla_deadline->format('M d, Y g:i A') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">SLA Hours</span>
                            <span class="text-sm text-gray-900">{{ $workOrder->sla_hours }}h</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Time Remaining</span>
                            @if($workOrder->isSlaBreached())
                                <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-bold bg-red-100 text-red-700">BREACHED</span>
                            @elseif($workOrder->completed_at)
                                <span class="text-sm font-medium text-emerald-600">Completed on time</span>
                            @else
                                @php
                                    $remaining = now()->diff($workOrder->sla_deadline);
                                    $hoursLeft = $remaining->h + ($remaining->days * 24);
                                    $minutesLeft = $remaining->i;
                                @endphp
                                <span class="text-sm font-medium {{ $hoursLeft < 2 ? 'text-amber-600' : 'text-gray-900' }}">
                                    {{ $hoursLeft }}h {{ $minutesLeft }}m
                                </span>
                            @endif
                        </div>

                        {{-- Progress Bar --}}
                        @php
                            $totalSeconds = $workOrder->sla_hours * 3600;
                            $elapsedSeconds = $workOrder->created_at->diffInSeconds(
                                $workOrder->completed_at ?? now()
                            );
                            $progress = $totalSeconds > 0 ? min(100, ($elapsedSeconds / $totalSeconds) * 100) : 0;
                        @endphp
                        <div class="pt-1">
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="h-2 rounded-full transition-all duration-300
                                    {{ $progress >= 100 ? 'bg-red-500' : ($progress >= 75 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                    style="width: {{ $progress }}%"></div>
                            </div>
                            <p class="mt-1 text-[11px] text-gray-400 text-right">{{ number_format($progress, 0) }}% of SLA consumed</p>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Resolution Notes --}}
                @if($workOrder->resolution_notes)
                <div class="card overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Resolution Notes</h3>
                    </div>
                    <div class="px-5 py-4">
                        <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $workOrder->resolution_notes }}</p>
                    </div>
                </div>
                @endif

            </div>
        </div>

</div>
