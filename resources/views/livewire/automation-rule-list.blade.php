<div class="min-h-screen bg-gray-50">
    <div class="px-6 py-6 max-w-[1400px] mx-auto">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Automation Rules</h1>
                <p class="mt-1 text-sm text-gray-500">Workflow automation engine for triggers, conditions, and actions</p>
            </div>
            <a href="{{ route('automation.create') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-accent-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Create Rule
            </a>
        </div>

        {{-- Flash Message --}}
        @if(session()->has('success'))
        <div class="mb-5 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
        @endif

        {{-- Filter Bar --}}
        <div class="flex flex-wrap items-center gap-3 mb-5">
            <select wire:model.live="triggerFilter"
                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                <option value="">All Triggers</option>
                <option value="work_order_created">Work Order Created</option>
                <option value="work_order_status_changed">Status Changed</option>
                <option value="sla_approaching">SLA Approaching</option>
                <option value="sla_breached">SLA Breached</option>
                <option value="sensor_alert">Sensor Alert</option>
                <option value="issue_imported">Issue Imported</option>
                <option value="pm_due">PM Due</option>
            </select>

            <select wire:model.live="statusFilter"
                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search rules..."
                    class="w-56 rounded-lg border border-gray-300 bg-white pl-9 pr-3 py-2 text-sm text-gray-700 shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
            </div>
        </div>

        {{-- Table --}}
        <div class="card/80 overflow-hidden">
            {{-- Table Header --}}
            <div class="hidden md:grid md:grid-cols-[1fr_160px_100px_100px_90px_100px_140px_80px] gap-4 px-5 py-3 bg-gray-50 border-b border-gray-200 label-kicker">
                <div>Name</div>
                <div>Trigger</div>
                <div class="text-center">Conditions</div>
                <div class="text-center">Actions</div>
                <div class="text-center">Status</div>
                <div class="text-center">Executions</div>
                <div>Last Executed</div>
                <div class="text-center">Actions</div>
            </div>

            {{-- Table Rows --}}
            @forelse($rules as $rule)
            <div class="grid grid-cols-1 md:grid-cols-[1fr_160px_100px_100px_90px_100px_140px_80px] gap-4 items-center px-5 py-3.5 border-b border-gray-100 last:border-b-0 hover:bg-gray-50/50 transition-colors">

                {{-- Name --}}
                <div class="min-w-0">
                    <a href="{{ route('automation.edit', $rule->id) }}" class="text-sm font-medium text-gray-900 hover:text-accent-700 truncate block">
                        {{ $rule->name }}
                    </a>
                    @if($rule->description)
                    <p class="text-xs text-gray-400 truncate mt-0.5">{{ $rule->description }}</p>
                    @endif
                </div>

                {{-- Trigger --}}
                <div>
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold
                        {{ match($rule->trigger_type) {
                            'work_order_created' => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20',
                            'work_order_status_changed' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20',
                            'sla_approaching' => 'bg-orange-50 text-orange-700 ring-1 ring-inset ring-orange-600/20',
                            'sla_breached' => 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20',
                            'sensor_alert' => 'bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-600/20',
                            'issue_imported' => 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-600/20',
                            'pm_due' => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20',
                            default => 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/20',
                        } }}">
                        {{ str_replace('_', ' ', ucwords($rule->trigger_type, '_')) }}
                    </span>
                </div>

                {{-- Conditions Count --}}
                <div class="text-center">
                    <span class="text-sm text-gray-600">{{ count($rule->conditions ?? []) }}</span>
                </div>

                {{-- Actions Count --}}
                <div class="text-center">
                    <span class="text-sm text-gray-600">{{ count($rule->actions ?? []) }}</span>
                </div>

                {{-- Status Toggle --}}
                <div class="text-center">
                    <button wire:click="toggleActive({{ $rule->id }})"
                        class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none
                        {{ $rule->is_active ? 'bg-accent-600' : 'bg-gray-200' }}"
                        role="switch" aria-checked="{{ $rule->is_active ? 'true' : 'false' }}">
                        <span class="pointer-events-none inline-block h-4 w-4 rounded-full bg-white shadow ring-0 transition-transform duration-200 ease-in-out
                            {{ $rule->is_active ? 'translate-x-4' : 'translate-x-0' }}"></span>
                    </button>
                </div>

                {{-- Execution Count --}}
                <div class="text-center">
                    <span class="text-sm font-medium text-gray-900">{{ number_format($rule->execution_count) }}</span>
                </div>

                {{-- Last Executed --}}
                <div>
                    @if($rule->last_executed_at)
                    <p class="text-sm text-gray-700">{{ $rule->last_executed_at->format('M d, Y') }}</p>
                    <p class="text-xs text-gray-400">{{ $rule->last_executed_at->format('g:i A') }}</p>
                    @else
                    <span class="text-xs text-gray-400">Never</span>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-center gap-1">
                    <a href="{{ route('automation.edit', $rule->id) }}"
                        class="p-1.5 rounded-md text-gray-400 hover:text-accent-700 hover:bg-accent-50 transition" title="Edit">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                    </a>
                    <button wire:click="deleteRule({{ $rule->id }})" wire:confirm="Are you sure you want to delete this rule?"
                        class="p-1.5 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 transition" title="Delete">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                    </button>
                </div>
            </div>
            @empty
            <div class="px-5 py-16 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <p class="mt-3 text-sm font-medium text-gray-500">No automation rules found</p>
                <p class="mt-1 text-xs text-gray-400">Create your first rule to automate workflows</p>
                <a href="{{ route('automation.create') }}"
                    class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-accent-600 px-3 py-2 text-xs font-semibold text-white hover:bg-brand-700 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Create Rule
                </a>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($rules->hasPages())
        <div class="mt-6 flex items-center justify-between">
            <p class="text-sm text-gray-600">
                Showing <span class="font-semibold text-gray-900">{{ $rules->firstItem() }}</span> to <span class="font-semibold text-gray-900">{{ $rules->lastItem() }}</span> of <span class="font-semibold text-gray-900">{{ $rules->total() }}</span> rules
            </p>
            <div>
                {{ $rules->links() }}
            </div>
        </div>
        @elseif($rules->count() > 0)
        <div class="mt-6">
            <p class="text-sm text-gray-500">Showing {{ $rules->count() }} of {{ $rules->total() }} rules</p>
        </div>
        @endif

    </div>
</div>
