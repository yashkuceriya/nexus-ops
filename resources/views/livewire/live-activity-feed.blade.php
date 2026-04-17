<div wire:poll.8s>
    <div class="card overflow-hidden">
        {{-- Header --}}
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Live Activity</h3>
            </div>
            <span class="text-[10px] text-gray-400 font-mono">Last {{ $this->limit }} events</span>
        </div>

        {{-- Activity List --}}
        <div class="divide-y divide-gray-50 max-h-[480px] overflow-y-auto">
            @forelse($this->activities as $activity)
            <div class="px-4 py-3 hover:bg-gray-50/50 transition-colors">
                <div class="flex items-start gap-3">
                    {{-- Icon --}}
                    <div class="flex-shrink-0 mt-0.5">
                        @switch($activity['type'])
                            @case('work_order_created')
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                </div>
                                @break
                            @case('work_order_completed')
                                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                @break
                            @case('sensor_alert')
                                <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                </div>
                                @break
                            @case('request_submitted')
                                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                                    </svg>
                                </div>
                                @break
                            @default
                                <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-5.385 3.17m0 0l-.916-5.646 4.132-4.127m-3.216 9.773L2.72 9.906c-.41-2.525 1.45-4.885 3.985-5.054l5.553-.37a1.125 1.125 0 011.142.662l2.263 5.084a1.125 1.125 0 01-.233 1.22L11.42 15.17z" />
                                    </svg>
                                </div>
                        @endswitch
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-700 leading-snug">{{ $activity['description'] }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            @if($activity['user_name'])
                            <div class="flex items-center gap-1.5">
                                <div class="w-4 h-4 rounded-full bg-gray-200 flex items-center justify-center text-[8px] font-bold text-gray-600">
                                    {{ $activity['user_initial'] }}
                                </div>
                                <span class="text-xs text-gray-500">{{ $activity['user_name'] }}</span>
                            </div>
                            <span class="text-gray-300">&middot;</span>
                            @endif
                            <span class="text-xs text-gray-400">{{ $activity['time_ago'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="px-5 py-12 text-center">
                <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-gray-400 font-medium">No recent activity</p>
                <p class="text-xs text-gray-300 mt-0.5">Activity will appear here as events occur</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
