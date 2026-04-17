<div
    x-data="{
        open: false,
        selectedIndex: 0,
        totalItems: 0,
        init() {
            this.$watch('open', (val) => {
                if (val) {
                    this.$nextTick(() => {
                        this.$refs.searchInput?.focus();
                    });
                    this.selectedIndex = 0;
                } else {
                    $wire.set('search', '');
                }
            });
        }
    }"
    @keydown.window.prevent.cmd.k="open = true"
    @keydown.window.prevent.ctrl.k="open = true"
    @keydown.escape.window="open = false"
    x-cloak
>
    {{-- Overlay --}}
    <template x-teleport="body">
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[99] bg-zinc-900/90 backdrop-blur-xl flex items-start justify-center pt-[15vh]"
            @click.self="open = false"
        >
            {{-- Modal --}}
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                class="w-full max-w-2xl bg-zinc-900 border border-zinc-700/50 rounded-2xl shadow-2xl shadow-black/50 overflow-hidden"
                @keydown.arrow-down.prevent="selectedIndex = Math.min(selectedIndex + 1, totalItems - 1)"
                @keydown.arrow-up.prevent="selectedIndex = Math.max(selectedIndex - 1, 0)"
                @keydown.enter.prevent="
                    const selected = document.querySelector('[data-cmd-index=\'' + selectedIndex + '\']');
                    if (selected) window.location.href = selected.dataset.cmdUrl;
                "
            >
                {{-- Search Input --}}
                <div class="flex items-center gap-3 px-5 py-4 border-b border-zinc-800">
                    <svg class="w-5 h-5 text-zinc-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input
                        x-ref="searchInput"
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Search work orders, assets, projects, sensors..."
                        class="flex-1 bg-transparent text-lg text-white placeholder:text-zinc-600 focus:outline-none"
                    >
                    <kbd class="hidden sm:inline-flex items-center gap-1 px-2 py-1 text-[10px] font-medium text-zinc-600 bg-zinc-800 border border-zinc-700 rounded-md">
                        ESC
                    </kbd>
                </div>

                {{-- Results Area --}}
                <div class="max-h-[60vh] overflow-y-auto" wire:loading.class="opacity-50">
                    @php $itemIndex = 0; @endphp

                    @if(strlen($search) >= 2 && count($results) > 0)
                        {{-- Search Results --}}
                        @php
                            $grouped = collect($results)->groupBy('type');
                        @endphp

                        @foreach($grouped as $type => $items)
                            <div class="px-3 pt-3 pb-1">
                                <p class="px-2 text-[10px] font-semibold text-zinc-600 uppercase tracking-wider">{{ $type }}</p>
                            </div>
                            @foreach($items as $item)
                                <a
                                    href="{{ $item['url'] }}"
                                    data-cmd-index="{{ $itemIndex }}"
                                    data-cmd-url="{{ $item['url'] }}"
                                    class="flex items-center gap-3 mx-2 px-3 py-2.5 rounded-xl text-sm transition-colors"
                                    :class="selectedIndex === {{ $itemIndex }} ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-zinc-200'"
                                    @mouseenter="selectedIndex = {{ $itemIndex }}"
                                >
                                    {{-- Icon --}}
                                    <div class="w-8 h-8 rounded-lg bg-zinc-800 border border-zinc-700/50 flex items-center justify-center flex-shrink-0">
                                        @if($item['icon'] === 'clipboard')
                                            <svg class="w-4 h-4 text-accent-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75" /></svg>
                                        @elseif($item['icon'] === 'cube')
                                            <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                                        @elseif($item['icon'] === 'folder')
                                            <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" /></svg>
                                        @elseif($item['icon'] === 'signal')
                                            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.348 14.651a3.75 3.75 0 010-5.303m5.304 0a3.75 3.75 0 010 5.303m-7.425 2.122a6.75 6.75 0 010-9.546m9.546 0a6.75 6.75 0 010 9.546M5.106 18.894c-3.808-3.808-3.808-9.98 0-13.788m13.788 0c3.808 3.808 3.808 9.98 0 13.788M12 12h.008v.008H12V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                                        @endif
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium truncate" :class="selectedIndex === {{ $itemIndex }} ? 'text-white' : ''">{{ $item['title'] }}</p>
                                        <p class="text-xs text-zinc-600 truncate">{{ $item['subtitle'] }}</p>
                                    </div>

                                    <span class="text-[10px] font-mono text-zinc-700 bg-zinc-800 px-1.5 py-0.5 rounded border border-zinc-700/50">{{ $item['hint'] }}</span>
                                </a>
                                @php $itemIndex++; @endphp
                            @endforeach
                        @endforeach

                    @elseif(strlen($search) >= 2 && count($results) === 0)
                        {{-- No Results --}}
                        <div class="px-5 py-12 text-center">
                            <svg class="w-10 h-10 text-zinc-700 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                            <p class="text-sm text-zinc-500">No results for "<span class="text-zinc-400">{{ $search }}</span>"</p>
                            <p class="text-xs text-zinc-600 mt-1">Try a different search term</p>
                        </div>

                    @else
                        {{-- Quick Actions --}}
                        <div class="px-3 pt-3 pb-1">
                            <p class="px-2 text-[10px] font-semibold text-zinc-600 uppercase tracking-wider">Quick Actions</p>
                        </div>
                        @foreach($quickActions as $action)
                            <a
                                href="{{ $action['url'] }}"
                                data-cmd-index="{{ $itemIndex }}"
                                data-cmd-url="{{ $action['url'] }}"
                                class="flex items-center gap-3 mx-2 px-3 py-2.5 rounded-xl text-sm transition-colors"
                                :class="selectedIndex === {{ $itemIndex }} ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-zinc-200'"
                                @mouseenter="selectedIndex = {{ $itemIndex }}"
                            >
                                <div class="w-8 h-8 rounded-lg bg-zinc-800 border border-zinc-700/50 flex items-center justify-center flex-shrink-0">
                                    @if($action['icon'] === 'plus')
                                        <svg class="w-4 h-4 text-accent-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                    @elseif($action['icon'] === 'dashboard')
                                        <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                                    @elseif($action['icon'] === 'signal')
                                        <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.348 14.651a3.75 3.75 0 010-5.303m5.304 0a3.75 3.75 0 010 5.303m-7.425 2.122a6.75 6.75 0 010-9.546m9.546 0a6.75 6.75 0 010 9.546M5.106 18.894c-3.808-3.808-3.808-9.98 0-13.788m13.788 0c3.808 3.808 3.808 9.98 0 13.788M12 12h.008v.008H12V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium truncate">{{ $action['title'] }}</p>
                                    <p class="text-xs text-zinc-600">{{ $action['subtitle'] }}</p>
                                </div>
                                <kbd class="text-[10px] font-mono text-zinc-700 bg-zinc-800 px-1.5 py-0.5 rounded border border-zinc-700/50">{{ $action['hint'] }}</kbd>
                            </a>
                            @php $itemIndex++; @endphp
                        @endforeach

                        {{-- Recent Pages --}}
                        <div class="px-3 pt-4 pb-1">
                            <p class="px-2 text-[10px] font-semibold text-zinc-600 uppercase tracking-wider">Recent</p>
                        </div>
                        @foreach($recentPages as $page)
                            <a
                                href="{{ $page['url'] }}"
                                data-cmd-index="{{ $itemIndex }}"
                                data-cmd-url="{{ $page['url'] }}"
                                class="flex items-center gap-3 mx-2 px-3 py-2.5 rounded-xl text-sm transition-colors"
                                :class="selectedIndex === {{ $itemIndex }} ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-zinc-200'"
                                @mouseenter="selectedIndex = {{ $itemIndex }}"
                            >
                                <div class="w-8 h-8 rounded-lg bg-zinc-800 border border-zinc-700/50 flex items-center justify-center flex-shrink-0">
                                    @if($page['icon'] === 'dashboard')
                                        <svg class="w-4 h-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6z" /></svg>
                                    @elseif($page['icon'] === 'clipboard')
                                        <svg class="w-4 h-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75" /></svg>
                                    @elseif($page['icon'] === 'cube')
                                        <svg class="w-4 h-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                                    @elseif($page['icon'] === 'folder')
                                        <svg class="w-4 h-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" /></svg>
                                    @elseif($page['icon'] === 'signal')
                                        <svg class="w-4 h-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.348 14.651a3.75 3.75 0 010-5.303m5.304 0a3.75 3.75 0 010 5.303m-7.425 2.122a6.75 6.75 0 010-9.546m9.546 0a6.75 6.75 0 010 9.546M5.106 18.894c-3.808-3.808-3.808-9.98 0-13.788m13.788 0c3.808 3.808 3.808 9.98 0 13.788M12 12h.008v.008H12V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                                    @endif
                                </div>
                                <p class="flex-1 font-medium truncate">{{ $page['title'] }}</p>
                                <svg class="w-4 h-4 text-zinc-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </a>
                            @php $itemIndex++; @endphp
                        @endforeach

                        <script>
                            document.addEventListener('alpine:init', () => {
                                // Set total items count for keyboard nav
                            });
                        </script>
                    @endif

                    {{-- Set total item count for Alpine keyboard nav --}}
                    <div x-init="totalItems = {{ $itemIndex }}" class="hidden"></div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-between px-5 py-3 border-t border-zinc-800 bg-zinc-900/50">
                    <div class="flex items-center gap-3 text-[11px] text-zinc-600">
                        <span class="flex items-center gap-1">
                            <kbd class="px-1.5 py-0.5 bg-zinc-800 border border-zinc-700 rounded text-[10px]">↑</kbd>
                            <kbd class="px-1.5 py-0.5 bg-zinc-800 border border-zinc-700 rounded text-[10px]">↓</kbd>
                            navigate
                        </span>
                        <span class="flex items-center gap-1">
                            <kbd class="px-1.5 py-0.5 bg-zinc-800 border border-zinc-700 rounded text-[10px]">↵</kbd>
                            select
                        </span>
                        <span class="flex items-center gap-1">
                            <kbd class="px-1.5 py-0.5 bg-zinc-800 border border-zinc-700 rounded text-[10px]">esc</kbd>
                            close
                        </span>
                    </div>
                    <span class="text-[10px] text-zinc-700">NexusOps Command Palette</span>
                </div>
            </div>
        </div>
    </template>
</div>
