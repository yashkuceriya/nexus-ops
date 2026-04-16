<div class="min-h-screen -m-6 bg-zinc-950 text-gray-200">
    {{-- Header --}}
    <div class="border-b border-zinc-800 bg-zinc-900/80 backdrop-blur-sm sticky top-0 z-10">
        <div class="max-w-[1600px] mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <h1 class="text-xl font-bold text-white tracking-tight">API Documentation</h1>
                    <span class="inline-flex items-center rounded-md bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-400 ring-1 ring-inset ring-emerald-500/20">v1.0</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-zinc-500 font-mono">Base URL:</span>
                    <code class="text-xs font-mono text-emerald-400 bg-zinc-800 px-3 py-1.5 rounded-md border border-zinc-700">{{ url('/api') }}</code>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-[1600px] mx-auto flex">
        {{-- LEFT: Endpoint Navigation --}}
        <aside class="w-[220px] flex-shrink-0 border-r border-zinc-800 bg-zinc-900/50 min-h-[calc(100vh-73px)] overflow-y-auto sticky top-[73px] self-start" style="max-height: calc(100vh - 73px);">
            <nav class="py-4 px-3 space-y-4">
                @foreach($this->endpoints as $group => $endpoints)
                    <div>
                        <h3 class="px-2 mb-1.5 text-[10px] font-bold text-zinc-500 uppercase tracking-wider">{{ $group }}</h3>
                        <ul class="space-y-0.5">
                            @foreach($endpoints as $endpoint)
                                @php
                                    $key = $endpoint['method'] . ' ' . $endpoint['path'];
                                    $isActive = $selectedEndpoint === $key;
                                    $methodColors = [
                                        'GET' => 'bg-emerald-500/15 text-emerald-400',
                                        'POST' => 'bg-blue-500/15 text-blue-400',
                                        'PUT' => 'bg-amber-500/15 text-amber-400',
                                        'PATCH' => 'bg-amber-500/15 text-amber-400',
                                        'DELETE' => 'bg-red-500/15 text-red-400',
                                    ];
                                    $methodColor = $methodColors[$endpoint['method']] ?? 'bg-gray-500/15 text-gray-400';
                                @endphp
                                <li>
                                    <button
                                        wire:click="selectEndpoint('{{ $key }}')"
                                        class="w-full text-left px-2 py-2 rounded-md text-xs transition-all group flex items-start gap-2
                                            {{ $isActive ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-800/60 hover:text-zinc-200' }}"
                                    >
                                        <span class="inline-flex items-center justify-center rounded px-1.5 py-0.5 text-[10px] font-bold leading-none flex-shrink-0 mt-0.5 {{ $methodColor }}">
                                            {{ $endpoint['method'] }}
                                        </span>
                                        <span class="font-mono text-[11px] leading-tight break-all">{{ str_replace('/api', '', $endpoint['path']) }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </nav>
        </aside>

        @if($this->selected)
        @php
            $ep = $this->selected;
            $methodBadgeColors = [
                'GET' => 'bg-emerald-500/15 text-emerald-400 ring-emerald-500/25',
                'POST' => 'bg-blue-500/15 text-blue-400 ring-blue-500/25',
                'PUT' => 'bg-amber-500/15 text-amber-400 ring-amber-500/25',
                'PATCH' => 'bg-amber-500/15 text-amber-400 ring-amber-500/25',
                'DELETE' => 'bg-red-500/15 text-red-400 ring-red-500/25',
            ];
            $badgeColor = $methodBadgeColors[$ep['method']] ?? 'bg-gray-500/15 text-gray-400 ring-gray-500/25';
        @endphp

        {{-- CENTER: Endpoint Details --}}
        <div class="flex-1 min-w-0 border-r border-zinc-800">
            <div class="p-8">
                {{-- Method + Path --}}
                <div class="flex items-center gap-3 mb-6">
                    <span class="inline-flex items-center rounded-md px-3 py-1.5 text-sm font-bold ring-1 ring-inset {{ $badgeColor }}">
                        {{ $ep['method'] }}
                    </span>
                    <code class="text-lg font-mono text-white font-medium">{{ $ep['path'] }}</code>
                </div>

                {{-- Description --}}
                <p class="text-sm text-zinc-400 leading-relaxed mb-8 max-w-2xl">{{ $ep['description'] }}</p>

                {{-- Authentication Notice --}}
                @if($ep['path'] !== '/api/auth/login')
                <div class="flex items-center gap-2 mb-8 px-4 py-3 rounded-lg bg-amber-500/5 border border-amber-500/15">
                    <svg class="w-4 h-4 text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                    <span class="text-xs text-amber-400/80">Requires authentication. Include <code class="font-mono text-amber-400">Authorization: Bearer &lt;token&gt;</code> header.</span>
                </div>
                @endif

                {{-- Parameters Table --}}
                @if(count($ep['parameters']) > 0)
                <div class="mb-8">
                    <h3 class="text-sm font-semibold text-zinc-300 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Parameters
                    </h3>
                    <div class="overflow-hidden rounded-lg border border-zinc-800">
                        <table class="min-w-full divide-y divide-zinc-800">
                            <thead>
                                <tr class="bg-zinc-900/80">
                                    <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-zinc-500 uppercase tracking-wider">Name</th>
                                    <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-zinc-500 uppercase tracking-wider">Type</th>
                                    <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-zinc-500 uppercase tracking-wider">Required</th>
                                    <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-zinc-500 uppercase tracking-wider">Description</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-800/50">
                                @foreach($ep['parameters'] as $param)
                                <tr class="hover:bg-zinc-800/30 transition-colors">
                                    <td class="px-4 py-2.5">
                                        <code class="text-xs font-mono text-cyan-400">{{ $param['name'] }}</code>
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <span class="text-xs text-purple-400 font-mono">{{ $param['type'] }}</span>
                                    </td>
                                    <td class="px-4 py-2.5">
                                        @if($param['required'])
                                            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-bold bg-red-500/10 text-red-400 ring-1 ring-inset ring-red-500/20">Required</span>
                                        @else
                                            <span class="text-xs text-zinc-600">Optional</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-xs text-zinc-400">{{ $param['description'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                {{-- Request Body --}}
                @if($ep['request'])
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-zinc-300 flex items-center gap-2">
                            <svg class="w-4 h-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                            </svg>
                            Request Body
                        </h3>
                    </div>
                    <div class="relative group rounded-lg overflow-hidden border border-zinc-800" x-data="{ copied: false }">
                        <div class="absolute top-2 right-2 z-10">
                            <button
                                x-on:click="
                                    navigator.clipboard.writeText(JSON.stringify({{ json_encode($ep['request']) }}, null, 2));
                                    copied = true;
                                    setTimeout(() => copied = false, 2000);
                                "
                                class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-[10px] font-medium bg-zinc-800 text-zinc-400 hover:text-zinc-200 border border-zinc-700 hover:border-zinc-600 transition-all opacity-0 group-hover:opacity-100"
                            >
                                <template x-if="!copied">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9.75a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                                    </svg>
                                </template>
                                <template x-if="copied">
                                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                </template>
                                <span x-text="copied ? 'Copied!' : 'Copy'" :class="copied && 'text-emerald-400'"></span>
                            </button>
                        </div>
                        <pre class="bg-zinc-900 p-4 overflow-x-auto text-[13px] leading-relaxed"><code>{!! $this->syntaxHighlight(json_encode($ep['request'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !!}</code></pre>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- RIGHT: Response Example --}}
        <div class="w-[340px] flex-shrink-0 min-h-[calc(100vh-73px)]">
            <div class="p-6 sticky top-[73px]">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-zinc-300 flex items-center gap-2">
                        <svg class="w-4 h-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859" />
                        </svg>
                        Response
                        <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-bold bg-emerald-500/10 text-emerald-400 ring-1 ring-inset ring-emerald-500/20">200 OK</span>
                    </h3>
                </div>
                <div class="relative group rounded-lg overflow-hidden border border-zinc-800" x-data="{ copied: false }">
                    <div class="absolute top-2 right-2 z-10">
                        <button
                            x-on:click="
                                navigator.clipboard.writeText(JSON.stringify({{ json_encode($ep['response']) }}, null, 2));
                                copied = true;
                                setTimeout(() => copied = false, 2000);
                            "
                            class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-[10px] font-medium bg-zinc-800 text-zinc-400 hover:text-zinc-200 border border-zinc-700 hover:border-zinc-600 transition-all opacity-0 group-hover:opacity-100"
                        >
                            <template x-if="!copied">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9.75a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                                </svg>
                            </template>
                            <template x-if="copied">
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </template>
                            <span x-text="copied ? 'Copied!' : 'Copy'" :class="copied && 'text-emerald-400'"></span>
                        </button>
                    </div>
                    @php
                        $jsonStr = json_encode($ep['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                        $lines = explode("\n", $jsonStr);
                    @endphp
                    <pre class="bg-zinc-900 p-4 overflow-x-auto text-[13px] leading-relaxed"><code>@foreach($lines as $i => $line)<span class="inline-block w-8 text-right mr-3 text-zinc-600 select-none text-[11px]">{{ $i + 1 }}</span>{!! $this->syntaxHighlight(e($line), true) !!}
@endforeach</code></pre>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
