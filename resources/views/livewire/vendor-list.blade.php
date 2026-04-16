<div class="min-h-screen bg-slate-50">
    {{-- Page Header --}}
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-2 h-8 bg-emerald-500 rounded-full"></div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Vendor Management</h1>
                </div>
                <p class="text-sm text-slate-500 ml-5 pl-0.5">Manage vendors, contracts, and performance &mdash; NexusOps Platform</p>
            </div>
            <button wire:click="openCreateForm"
                class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Add Vendor
            </button>
        </div>
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
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search vendors by name, contact, or email..."
                    class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors placeholder:text-slate-400">
            </div>

            {{-- Trade Filter --}}
            <div class="relative">
                <select wire:model.live="tradeFilter"
                    class="appearance-none pl-4 pr-10 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors text-slate-700 cursor-pointer">
                    <option value="">All Trades</option>
                    <option value="HVAC">HVAC</option>
                    <option value="Electrical">Electrical</option>
                    <option value="Plumbing">Plumbing</option>
                    <option value="Fire/Life Safety">Fire/Life Safety</option>
                    <option value="General Maintenance">General Maintenance</option>
                    <option value="Roofing">Roofing</option>
                    <option value="Elevator">Elevator</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                    </svg>
                </div>
            </div>

            {{-- Active Filter --}}
            <div class="relative">
                <select wire:model.live="activeFilter"
                    class="appearance-none pl-4 pr-10 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors text-slate-700 cursor-pointer">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Vendor Card Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @foreach($vendors as $vendor)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden hover:shadow-md transition-all duration-200">
            {{-- Card Header --}}
            <div class="px-5 pt-5 pb-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('vendors.show', $vendor->id) }}" class="text-sm font-bold text-slate-900 hover:text-emerald-600 transition-colors truncate block">{{ $vendor->name }}</a>
                        <p class="text-xs text-slate-400 mt-0.5 truncate">
                            @if($vendor->contact_name)
                                {{ $vendor->contact_name }}
                                @if($vendor->email) &middot; {{ $vendor->email }} @endif
                            @else
                                {{ $vendor->email ?? 'No contact info' }}
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        @if($vendor->is_active)
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide ring-1 ring-inset bg-emerald-100 text-emerald-700 ring-emerald-600/10">Active</span>
                        @else
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide ring-1 ring-inset bg-slate-100 text-slate-600 ring-slate-500/10">Inactive</span>
                        @endif
                    </div>
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
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $tradeColors }}">{{ $trade }}</span>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Stats Grid --}}
            <div class="px-5 pb-4">
                <div class="grid grid-cols-4 gap-2">
                    <div class="bg-slate-50 rounded-lg p-2.5 text-center">
                        <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">WOs</div>
                        <div class="text-sm font-bold text-slate-700">{{ number_format($vendor->total_work_orders) }}</div>
                    </div>
                    <div class="bg-slate-50 rounded-lg p-2.5 text-center">
                        <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Spend</div>
                        <div class="text-sm font-bold text-slate-700">${{ number_format($vendor->total_spend, 0) }}</div>
                    </div>
                    <div class="bg-slate-50 rounded-lg p-2.5 text-center">
                        <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Resp.</div>
                        <div class="text-sm font-bold text-slate-700">{{ $vendor->avg_response_hours ? number_format($vendor->avg_response_hours, 1) . 'h' : '---' }}</div>
                    </div>
                    <div class="bg-slate-50 rounded-lg p-2.5 text-center">
                        <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Rating</div>
                        <div class="flex items-center justify-center gap-0.5">
                            @if($vendor->rating)
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= floor($vendor->rating))
                                        <svg class="w-3 h-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    @elseif($i - $vendor->rating < 1)
                                        <svg class="w-3 h-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20" style="clip-path: inset(0 50% 0 0);"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    @else
                                        <svg class="w-3 h-3 text-slate-200" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    @endif
                                @endfor
                                <span class="text-[10px] font-semibold text-slate-500 ml-0.5">{{ number_format($vendor->rating, 1) }}</span>
                            @else
                                <span class="text-[10px] text-slate-400">N/A</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card Footer --}}
            <div class="px-5 py-3 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    {{-- Insurance Status --}}
                    @php $insuranceStatus = $vendor->getInsuranceStatus(); @endphp
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium {{ match($insuranceStatus) {
                        'active' => 'text-emerald-600',
                        'expiring' => 'text-amber-600',
                        'expired' => 'text-red-600',
                        default => 'text-slate-400',
                    } }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ match($insuranceStatus) {
                            'active' => 'bg-emerald-500',
                            'expiring' => 'bg-amber-500',
                            'expired' => 'bg-red-500',
                            default => 'bg-slate-300',
                        } }}"></span>
                        Ins: {{ ucfirst($insuranceStatus) }}
                    </span>

                    @if($vendor->active_contracts_count > 0)
                    <span class="inline-flex items-center gap-1 text-xs text-slate-400">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                        </svg>
                        {{ $vendor->active_contracts_count }} {{ Str::plural('contract', $vendor->active_contracts_count) }}
                    </span>
                    @endif
                </div>

                <a href="{{ route('vendors.show', $vendor->id) }}" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 transition-colors">
                    View Details &rarr;
                </a>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Empty State --}}
    @if($vendors->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-16 text-center">
        <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
            </svg>
        </div>
        <p class="text-slate-500 font-medium">No vendors found</p>
        <p class="text-sm text-slate-400 mt-1">Try adjusting your search or filter criteria, or add a new vendor</p>
    </div>
    @endif

    {{-- Vendor Form Modal --}}
    @if($showForm)
    <div class="fixed inset-0 z-50 overflow-y-auto" x-data x-init="document.body.classList.add('overflow-hidden')" x-on:remove="document.body.classList.remove('overflow-hidden')">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" wire:click="closeForm"></div>
            <div class="relative bg-white rounded-2xl shadow-xl max-w-2xl w-full mx-auto z-10">
                @livewire('vendor-form', ['vendorId' => $editingVendorId], key('vendor-form-' . ($editingVendorId ?? 'new')))
            </div>
        </div>
    </div>
    @endif
</div>
