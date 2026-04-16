<div class="relative" x-data="{ open: @entangle('showDropdown') }">
    {{-- Bell Button --}}
    <button wire:click="toggleDropdown" class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition relative">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>
        @if($this->unreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-red-500 rounded-full">
                {{ $this->unreadCount > 9 ? '9+' : $this->unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown --}}
    @if($showDropdown)
    <div class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50" @click.outside="$wire.toggleDropdown()">
        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
            @if($this->unreadCount > 0)
                <button wire:click="markAllAsRead" class="text-xs text-brand-600 hover:text-brand-700 font-medium">
                    Mark all as read
                </button>
            @endif
        </div>

        {{-- Notification List --}}
        <div class="max-h-80 overflow-y-auto divide-y divide-gray-50">
            @forelse($this->notifications as $notification)
                <div
                    wire:click="markAsRead('{{ $notification->id }}')"
                    class="flex items-start gap-3 px-4 py-3 cursor-pointer transition {{ is_null($notification->read_at) ? 'bg-brand-50/50 hover:bg-brand-50' : 'hover:bg-gray-50' }}"
                >
                    {{-- Icon by type --}}
                    <div class="flex-shrink-0 mt-0.5">
                        @php $nType = $notification->data['type'] ?? 'default'; @endphp
                        @if($nType === 'work_order_assigned')
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </div>
                        @elseif($nType === 'sla_breach_warning')
                            <div class="w-8 h-8 rounded-full {{ ($notification->data['breached'] ?? false) ? 'bg-red-100' : 'bg-amber-100' }} flex items-center justify-center">
                                <svg class="w-4 h-4 {{ ($notification->data['breached'] ?? false) ? 'text-red-600' : 'text-amber-600' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        @elseif($nType === 'sensor_alert')
                            <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-orange-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                            </div>
                        @else
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                </svg>
                            </div>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-900 leading-snug">
                            @if($nType === 'work_order_assigned')
                                <span class="font-medium">{{ $notification->data['wo_number'] ?? '' }}</span> assigned to you by {{ $notification->data['assigned_by'] ?? 'someone' }}
                            @elseif($nType === 'sla_breach_warning')
                                @if($notification->data['breached'] ?? false)
                                    <span class="font-medium text-red-600">SLA Breached:</span>
                                @else
                                    <span class="font-medium text-amber-600">SLA Warning:</span>
                                @endif
                                {{ $notification->data['wo_number'] ?? '' }} - {{ Str::limit($notification->data['title'] ?? '', 40) }}
                            @elseif($nType === 'sensor_alert')
                                <span class="font-medium">Sensor Alert:</span> {{ $notification->data['sensor_name'] ?? '' }} on {{ $notification->data['asset_name'] ?? '' }}
                            @else
                                Notification
                            @endif
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>

                    {{-- Unread dot --}}
                    @if(is_null($notification->read_at))
                        <div class="flex-shrink-0 mt-1.5">
                            <div class="w-2 h-2 bg-brand-500 rounded-full"></div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="px-4 py-8 text-center">
                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                    <p class="text-sm text-gray-400">No notifications yet</p>
                </div>
            @endforelse
        </div>
    </div>
    @endif
</div>
