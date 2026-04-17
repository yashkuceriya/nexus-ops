<div class="">
    <div class="px-6 py-6 max-w-[1400px] mx-auto">

        {{-- Back Link --}}
        <div class="mb-5">
            <a href="{{ route('vendors.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Vendors
            </a>
        </div>

        {{-- Header --}}
        <div class="mb-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <h1 class="text-2xl font-bold text-ink tracking-tight">{{ $vendor->name }}</h1>
                        @if($vendor->is_active)
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide ring-1 ring-inset bg-emerald-100 text-emerald-700 ring-emerald-600/10">Active</span>
                        @else
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide ring-1 ring-inset bg-slate-100 text-slate-600 ring-slate-500/10">Inactive</span>
                        @endif
                    </div>

                    {{-- Contact Info --}}
                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                        @if($vendor->contact_name)
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            {{ $vendor->contact_name }}
                        </span>
                        @endif
                        @if($vendor->email)
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                            {{ $vendor->email }}
                        </span>
                        @endif
                        @if($vendor->phone)
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                            {{ $vendor->phone }}
                        </span>
                        @endif
                    </div>

                    {{-- Trade Specialties --}}
                    @if($vendor->trade_specialties && count($vendor->trade_specialties) > 0)
                    <div class="flex flex-wrap gap-1.5 mt-3">
                        @foreach($vendor->trade_specialties as $trade)
                        @php
                            $tradeColors = match($trade) {
                                'HVAC' => 'bg-blue-100 text-blue-700',
                                'Electrical' => 'bg-amber-100 text-amber-700',
                                'Plumbing' => 'bg-cyan-100 text-cyan-700',
                                'Fire/Life Safety' => 'bg-red-100 text-red-700',
                                'General Maintenance' => 'bg-slate-100 text-slate-700',
                                'Roofing' => 'bg-orange-100 text-orange-700',
                                'Elevator' => 'bg-purple-100 text-purple-700',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $tradeColors }}">{{ $trade }}</span>
                        @endforeach
                    </div>
                    @endif

                    {{-- Rating --}}
                    @if($vendor->rating)
                    <div class="flex items-center gap-1.5 mt-3">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= floor($vendor->rating))
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @elseif($i - $vendor->rating < 1)
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20" style="clip-path: inset(0 50% 0 0);"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @else
                                <svg class="w-5 h-5 text-slate-200" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @endif
                        @endfor
                        <span class="text-sm font-semibold text-gray-600 ml-1">{{ number_format($vendor->rating, 1) }} / 5.0</span>
                    </div>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <button wire:click="openEditVendor"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                        Edit Vendor
                    </button>
                    <button wire:click="openAddContract"
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Add Contract
                    </button>
                </div>
            </div>
        </div>

        {{-- Two Column Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- LEFT COLUMN (2/3) --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Performance Scorecard --}}
                <div class="card overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Performance Scorecard</h3>
                    </div>
                    <div class="px-5 py-5">
                        <div class="grid grid-cols-5 gap-4 mb-6">
                            <div class="text-center">
                                <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Avg Response</div>
                                <div class="text-lg font-bold text-gray-900">{{ $vendor->avg_response_hours ? number_format($vendor->avg_response_hours, 1) . 'h' : '---' }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Avg Completion</div>
                                <div class="text-lg font-bold text-gray-900">{{ $vendor->avg_completion_hours ? number_format($vendor->avg_completion_hours, 1) . 'h' : '---' }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Total WOs</div>
                                <div class="text-lg font-bold text-gray-900">{{ number_format($vendor->total_work_orders) }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Total Spend</div>
                                <div class="text-lg font-bold text-gray-900">${{ number_format($vendor->total_spend, 0) }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">First-Visit Fix</div>
                                <div class="text-lg font-bold text-gray-900">---</div>
                            </div>
                        </div>

                        {{-- Radar Chart --}}
                        @php $metrics = $this->performanceMetrics; @endphp
                        <div class="flex justify-center">
                            <div class="w-72 h-72">
                                <canvas id="vendorRadarChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tabs --}}
                <div class="card overflow-hidden">
                    <div class="border-b border-gray-100">
                        <nav class="flex -mb-px">
                            <button wire:click="switchTab('contracts')"
                                class="px-5 py-3.5 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'contracts' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                Contracts ({{ $this->contracts->count() }})
                            </button>
                            <button wire:click="switchTab('work-orders')"
                                class="px-5 py-3.5 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'work-orders' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                Work Orders ({{ $this->workOrders->count() }})
                            </button>
                        </nav>
                    </div>

                    {{-- Contracts Tab --}}
                    @if($activeTab === 'contracts')
                    <div class="divide-y divide-gray-50">
                        @forelse($this->contracts as $contract)
                        <div class="px-5 py-4 hover:bg-gray-50/50 transition-colors cursor-pointer" wire:click="openEditContract({{ $contract->id }})">
                            <div class="flex items-start justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h4 class="text-sm font-semibold text-gray-900">{{ $contract->title }}</h4>
                                        @php
                                            $statusColors = match($contract->status) {
                                                'draft' => 'bg-gray-100 text-gray-600',
                                                'active' => 'bg-emerald-100 text-emerald-700',
                                                'expired' => 'bg-red-100 text-red-700',
                                                'terminated' => 'bg-orange-100 text-orange-700',
                                                default => 'bg-gray-100 text-gray-600',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $statusColors }}">{{ ucfirst($contract->status) }}</span>
                                        @if($contract->auto_renew)
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold bg-blue-50 text-blue-600">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/></svg>
                                            Auto-Renew
                                        </span>
                                        @endif
                                    </div>
                                    <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500">
                                        @if($contract->contract_number)
                                        <span>#{{ $contract->contract_number }}</span>
                                        @endif
                                        <span>{{ $contract->start_date->format('M d, Y') }} &mdash; {{ $contract->end_date->format('M d, Y') }}</span>
                                        @if($contract->nte_limit)
                                        <span class="font-medium text-gray-700">NTE: ${{ number_format($contract->nte_limit, 2) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0 ml-4">
                                    @if($contract->annual_value)
                                    <div class="text-sm font-bold text-gray-900">${{ number_format($contract->annual_value, 0) }}/yr</div>
                                    @endif
                                    @if($contract->monthly_cost)
                                    <div class="text-xs text-gray-500">${{ number_format($contract->monthly_cost, 0) }}/mo</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="px-5 py-10 text-center">
                            <svg class="mx-auto h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            <p class="mt-2 text-sm text-gray-500">No contracts yet</p>
                        </div>
                        @endforelse
                    </div>
                    @endif

                    {{-- Work Orders Tab --}}
                    @if($activeTab === 'work-orders')
                    <div class="divide-y divide-gray-50">
                        @forelse($this->workOrders as $wo)
                        <a href="{{ route('work-orders.show', $wo->id) }}" class="block px-5 py-4 hover:bg-gray-50/50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs font-mono font-semibold text-gray-500 bg-gray-100 rounded px-1.5 py-0.5">{{ $wo->wo_number }}</span>
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-[10px] font-medium {{ match($wo->status) {
                                            'pending' => 'bg-gray-100 text-gray-600',
                                            'assigned' => 'bg-blue-50 text-blue-700',
                                            'in_progress' => 'bg-yellow-50 text-yellow-700',
                                            'on_hold' => 'bg-purple-50 text-purple-700',
                                            'completed' => 'bg-emerald-50 text-emerald-700',
                                            'verified' => 'bg-green-50 text-green-700',
                                            'cancelled' => 'bg-gray-100 text-gray-600',
                                            default => 'bg-gray-50 text-gray-700',
                                        } }}">{{ str_replace('_', ' ', ucfirst($wo->status)) }}</span>
                                    </div>
                                    <h4 class="text-sm font-medium text-gray-900">{{ $wo->title }}</h4>
                                    <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                                        @if($wo->project) <span>{{ $wo->project->name }}</span> @endif
                                        @if($wo->assignee) <span>{{ $wo->assignee->name }}</span> @endif
                                        <span>{{ $wo->created_at->format('M d, Y') }}</span>
                                    </div>
                                </div>
                                @if($wo->actual_cost)
                                <span class="text-sm font-semibold text-gray-900 flex-shrink-0 ml-4">${{ number_format($wo->actual_cost, 2) }}</span>
                                @endif
                            </div>
                        </a>
                        @empty
                        <div class="px-5 py-10 text-center">
                            <svg class="mx-auto h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75"/></svg>
                            <p class="mt-2 text-sm text-gray-500">No work orders assigned to this vendor</p>
                        </div>
                        @endforelse
                    </div>
                    @endif
                </div>
            </div>

            {{-- RIGHT COLUMN (1/3) --}}
            <div class="space-y-6">

                {{-- Vendor Info Card --}}
                <div class="card overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Vendor Details</h3>
                    </div>
                    <div class="px-5 py-4 space-y-3">
                        @if($vendor->address)
                        <div>
                            <span class="label-kicker block mb-0.5">Address</span>
                            <span class="text-sm text-gray-900">{{ $vendor->address }}</span>
                            @if($vendor->city || $vendor->state || $vendor->zip)
                            <br><span class="text-sm text-gray-900">{{ implode(', ', array_filter([$vendor->city, $vendor->state])) }} {{ $vendor->zip }}</span>
                            @endif
                        </div>
                        @endif

                        @if($vendor->license_number)
                        <div>
                            <span class="label-kicker block mb-0.5">License #</span>
                            <span class="text-sm font-mono text-gray-900">{{ $vendor->license_number }}</span>
                        </div>
                        @endif

                        <div>
                            <span class="label-kicker block mb-0.5">Insurance</span>
                            @php $insuranceStatus = $vendor->getInsuranceStatus(); @endphp
                            <span class="inline-flex items-center gap-1.5 text-sm font-medium {{ match($insuranceStatus) {
                                'active' => 'text-emerald-600',
                                'expiring' => 'text-amber-600',
                                'expired' => 'text-red-600',
                                default => 'text-gray-400',
                            } }}">
                                <span class="w-2 h-2 rounded-full {{ match($insuranceStatus) {
                                    'active' => 'bg-emerald-500',
                                    'expiring' => 'bg-amber-500',
                                    'expired' => 'bg-red-500',
                                    default => 'bg-gray-300',
                                } }}"></span>
                                {{ ucfirst($insuranceStatus) }}
                                @if($vendor->insurance_expiry)
                                    ({{ $vendor->insurance_expiry->format('M d, Y') }})
                                @endif
                            </span>
                        </div>

                        @if($vendor->notes)
                        <div>
                            <span class="label-kicker block mb-0.5">Notes</span>
                            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $vendor->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Active Contract Summary --}}
                @php $activeContract = $vendor->getActiveContract(); @endphp
                @if($activeContract)
                <div class="card overflow-hidden ring-1 ring-emerald-200">
                    <div class="px-5 py-3.5 border-b border-gray-100 bg-emerald-50/50">
                        <h3 class="text-sm font-semibold text-emerald-900">Active Contract</h3>
                    </div>
                    <div class="px-5 py-4 space-y-3">
                        <div>
                            <span class="text-sm font-semibold text-gray-900">{{ $activeContract->title }}</span>
                            @if($activeContract->contract_number)
                            <span class="text-xs text-gray-500 ml-1">#{{ $activeContract->contract_number }}</span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="label-kicker">Expires</span>
                            <span class="text-sm text-gray-900">{{ $activeContract->end_date->format('M d, Y') }}</span>
                        </div>
                        @if($activeContract->nte_limit)
                        <div class="flex items-center justify-between">
                            <span class="label-kicker">NTE Limit</span>
                            <span class="text-sm font-bold text-gray-900">${{ number_format($activeContract->nte_limit, 2) }}</span>
                        </div>
                        @endif
                        @if($activeContract->auto_renew)
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/></svg>
                            <span class="text-xs font-medium text-blue-600">Auto-renewal enabled</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Vendor Form Modal --}}
    @if($showVendorForm)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" wire:click="closeAllForms"></div>
            <div class="relative bg-white rounded-2xl shadow-xl max-w-2xl w-full mx-auto z-10">
                @livewire('vendor-form', ['vendorId' => $vendor->id], key('vendor-form-edit-' . $vendor->id))
            </div>
        </div>
    </div>
    @endif

    {{-- Contract Form Modal --}}
    @if($showContractForm)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" wire:click="closeAllForms"></div>
            <div class="relative bg-white rounded-2xl shadow-xl max-w-2xl w-full mx-auto z-10">
                @livewire('vendor-contract-form', ['vendorId' => $vendor->id, 'contractId' => $editingContractId], key('contract-form-' . ($editingContractId ?? 'new')))
            </div>
        </div>
    </div>
    @endif

    {{-- Radar Chart Script --}}
    <script>
        document.addEventListener('livewire:navigated', initRadarChart);
        document.addEventListener('DOMContentLoaded', initRadarChart);

        function initRadarChart() {
            const canvas = document.getElementById('vendorRadarChart');
            if (!canvas) return;

            // Destroy existing chart if any
            const existingChart = Chart.getChart(canvas);
            if (existingChart) existingChart.destroy();

            new Chart(canvas, {
                type: 'radar',
                data: {
                    labels: ['Response Time', 'Completion Speed', 'Volume', 'Cost Efficiency', 'Quality'],
                    datasets: [{
                        label: 'Performance',
                        data: [
                            {{ $metrics['response_time'] }},
                            {{ $metrics['completion_time'] }},
                            {{ $metrics['volume'] }},
                            {{ $metrics['cost_efficiency'] }},
                            {{ $metrics['quality'] }}
                        ],
                        backgroundColor: 'rgba(16, 185, 129, 0.15)',
                        borderColor: 'rgb(16, 185, 129)',
                        borderWidth: 2,
                        pointBackgroundColor: 'rgb(16, 185, 129)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        r: {
                            beginAtZero: true,
                            max: 100,
                            ticks: { stepSize: 20, font: { size: 10 }, backdropColor: 'transparent' },
                            pointLabels: { font: { size: 11, weight: '600' }, color: '#64748b' },
                            grid: { color: '#e2e8f0' },
                            angleLines: { color: '#e2e8f0' },
                        }
                    },
                    plugins: {
                        legend: { display: false },
                    }
                }
            });
        }
    </script>
</div>
