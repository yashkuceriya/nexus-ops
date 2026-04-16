<div x-data="{ selectedPin: null }">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Interactive Floor Plan</h1>
                <p class="mt-1 text-sm text-gray-500">Data center mechanical room layout with live asset status</p>
            </div>
            <div class="flex items-center gap-3">
                <label class="text-sm font-medium text-gray-700">Floor / Building:</label>
                <select wire:model.live="selectedFloor"
                    class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @foreach($floors as $floor)
                        <option value="{{ $floor['id'] }}">{{ $floor['name'] }}</option>
                    @endforeach
                    @if(empty($floors))
                        <option value="">No locations available</option>
                    @endif
                </select>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
        {{-- Floor Plan SVG --}}
        <div class="xl:col-span-3">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 overflow-hidden">
                <svg viewBox="0 0 800 560" class="w-full h-auto" style="min-height: 480px;"
                     xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="grid" width="20" height="20" patternUnits="userSpaceOnUse">
                            <path d="M 20 0 L 0 0 0 20" fill="none" stroke="#f0f0f0" stroke-width="0.5"/>
                        </pattern>
                        <filter id="shadow" x="-2" y="-2" width="104%" height="104%">
                            <feDropShadow dx="0" dy="1" stdDeviation="2" flood-opacity="0.1"/>
                        </filter>
                    </defs>

                    {{-- Background Grid --}}
                    <rect width="800" height="560" fill="url(#grid)"/>

                    {{-- Outer Building Walls --}}
                    <rect x="30" y="30" width="740" height="500" fill="none" stroke="#374151" stroke-width="3" rx="4"/>

                    {{-- Main Corridor (horizontal) --}}
                    <rect x="30" y="290" width="740" height="40" fill="#f9fafb" stroke="#9ca3af" stroke-width="1.5" stroke-dasharray="6,3"/>
                    <text x="400" y="315" text-anchor="middle" class="text-[11px]" fill="#6b7280" font-weight="600" font-family="system-ui">MAIN CORRIDOR</text>

                    {{-- Server Room A (top-left) --}}
                    <rect x="40" y="40" width="300" height="240" fill="#f0fdf4" stroke="#6b7280" stroke-width="1.5" rx="2"/>
                    <text x="190" y="65" text-anchor="middle" fill="#047857" font-weight="700" font-size="12" font-family="system-ui">SERVER ROOM A</text>
                    {{-- Server Racks (visual) --}}
                    <rect x="60" y="80" width="60" height="20" fill="#d1fae5" stroke="#6ee7b7" stroke-width="1" rx="2"/>
                    <rect x="60" y="105" width="60" height="20" fill="#d1fae5" stroke="#6ee7b7" stroke-width="1" rx="2"/>
                    <rect x="160" y="80" width="60" height="20" fill="#d1fae5" stroke="#6ee7b7" stroke-width="1" rx="2"/>
                    <rect x="160" y="105" width="60" height="20" fill="#d1fae5" stroke="#6ee7b7" stroke-width="1" rx="2"/>
                    <rect x="260" y="80" width="60" height="20" fill="#d1fae5" stroke="#6ee7b7" stroke-width="1" rx="2"/>
                    <rect x="260" y="105" width="60" height="20" fill="#d1fae5" stroke="#6ee7b7" stroke-width="1" rx="2"/>
                    <text x="90" y="95" text-anchor="middle" fill="#065f46" font-size="8" font-family="system-ui">Rack 1</text>
                    <text x="190" y="95" text-anchor="middle" fill="#065f46" font-size="8" font-family="system-ui">Rack 2</text>
                    <text x="290" y="95" text-anchor="middle" fill="#065f46" font-size="8" font-family="system-ui">Rack 3</text>

                    {{-- Server Room B (top-right) --}}
                    <rect x="350" y="40" width="410" height="240" fill="#eff6ff" stroke="#6b7280" stroke-width="1.5" rx="2"/>
                    <text x="555" y="65" text-anchor="middle" fill="#1d4ed8" font-weight="700" font-size="12" font-family="system-ui">SERVER ROOM B</text>
                    {{-- Server Racks --}}
                    <rect x="370" y="80" width="60" height="20" fill="#dbeafe" stroke="#93c5fd" stroke-width="1" rx="2"/>
                    <rect x="370" y="105" width="60" height="20" fill="#dbeafe" stroke="#93c5fd" stroke-width="1" rx="2"/>
                    <rect x="470" y="80" width="60" height="20" fill="#dbeafe" stroke="#93c5fd" stroke-width="1" rx="2"/>
                    <rect x="470" y="105" width="60" height="20" fill="#dbeafe" stroke="#93c5fd" stroke-width="1" rx="2"/>
                    <rect x="570" y="80" width="60" height="20" fill="#dbeafe" stroke="#93c5fd" stroke-width="1" rx="2"/>
                    <rect x="570" y="105" width="60" height="20" fill="#dbeafe" stroke="#93c5fd" stroke-width="1" rx="2"/>
                    <rect x="670" y="80" width="60" height="20" fill="#dbeafe" stroke="#93c5fd" stroke-width="1" rx="2"/>
                    <rect x="670" y="105" width="60" height="20" fill="#dbeafe" stroke="#93c5fd" stroke-width="1" rx="2"/>
                    <text x="400" y="95" text-anchor="middle" fill="#1e3a8a" font-size="8" font-family="system-ui">Rack 4</text>
                    <text x="500" y="95" text-anchor="middle" fill="#1e3a8a" font-size="8" font-family="system-ui">Rack 5</text>
                    <text x="600" y="95" text-anchor="middle" fill="#1e3a8a" font-size="8" font-family="system-ui">Rack 6</text>
                    <text x="700" y="95" text-anchor="middle" fill="#1e3a8a" font-size="8" font-family="system-ui">Rack 7</text>

                    {{-- Mechanical Room (bottom-left) --}}
                    <rect x="40" y="340" width="340" height="180" fill="#fefce8" stroke="#6b7280" stroke-width="1.5" rx="2"/>
                    <text x="210" y="365" text-anchor="middle" fill="#a16207" font-weight="700" font-size="12" font-family="system-ui">MECHANICAL ROOM</text>
                    {{-- HVAC Units --}}
                    <rect x="60" y="385" width="80" height="50" fill="#fef9c3" stroke="#facc15" stroke-width="1" rx="3"/>
                    <text x="100" y="415" text-anchor="middle" fill="#854d0e" font-size="9" font-family="system-ui">CRAC-1</text>
                    <rect x="160" y="385" width="80" height="50" fill="#fef9c3" stroke="#facc15" stroke-width="1" rx="3"/>
                    <text x="200" y="415" text-anchor="middle" fill="#854d0e" font-size="9" font-family="system-ui">CRAC-2</text>
                    <rect x="260" y="385" width="80" height="50" fill="#fef9c3" stroke="#facc15" stroke-width="1" rx="3"/>
                    <text x="300" y="415" text-anchor="middle" fill="#854d0e" font-size="9" font-family="system-ui">Chiller</text>
                    {{-- Piping --}}
                    <line x1="140" y1="410" x2="160" y2="410" stroke="#ca8a04" stroke-width="2" stroke-dasharray="4,2"/>
                    <line x1="240" y1="410" x2="260" y2="410" stroke="#ca8a04" stroke-width="2" stroke-dasharray="4,2"/>

                    {{-- Electrical Room (bottom-right) --}}
                    <rect x="390" y="340" width="370" height="180" fill="#fdf2f8" stroke="#6b7280" stroke-width="1.5" rx="2"/>
                    <text x="575" y="365" text-anchor="middle" fill="#9d174d" font-weight="700" font-size="12" font-family="system-ui">ELECTRICAL ROOM</text>
                    {{-- Electrical Panels --}}
                    <rect x="410" y="385" width="70" height="50" fill="#fce7f3" stroke="#f9a8d4" stroke-width="1" rx="3"/>
                    <text x="445" y="415" text-anchor="middle" fill="#831843" font-size="9" font-family="system-ui">UPS-1</text>
                    <rect x="500" y="385" width="70" height="50" fill="#fce7f3" stroke="#f9a8d4" stroke-width="1" rx="3"/>
                    <text x="535" y="415" text-anchor="middle" fill="#831843" font-size="9" font-family="system-ui">UPS-2</text>
                    <rect x="590" y="385" width="70" height="50" fill="#fce7f3" stroke="#f9a8d4" stroke-width="1" rx="3"/>
                    <text x="625" y="415" text-anchor="middle" fill="#831843" font-size="9" font-family="system-ui">PDU-A</text>
                    <rect x="680" y="385" width="60" height="50" fill="#fce7f3" stroke="#f9a8d4" stroke-width="1" rx="3"/>
                    <text x="710" y="415" text-anchor="middle" fill="#831843" font-size="9" font-family="system-ui">ATS</text>
                    {{-- Bus Bars --}}
                    <line x1="480" y1="410" x2="500" y2="410" stroke="#ec4899" stroke-width="2"/>
                    <line x1="570" y1="410" x2="590" y2="410" stroke="#ec4899" stroke-width="2"/>
                    <line x1="660" y1="410" x2="680" y2="410" stroke="#ec4899" stroke-width="2"/>

                    {{-- Door symbols --}}
                    <rect x="180" y="288" width="30" height="6" fill="#d1d5db" rx="1"/>
                    <text x="195" y="286" text-anchor="middle" fill="#9ca3af" font-size="7" font-family="system-ui">Door</text>
                    <rect x="500" y="288" width="30" height="6" fill="#d1d5db" rx="1"/>
                    <text x="515" y="286" text-anchor="middle" fill="#9ca3af" font-size="7" font-family="system-ui">Door</text>

                    {{-- Asset Pins --}}
                    @foreach($this->assetPins as $pin)
                        @php
                            $fillColor = match($pin['color']) {
                                'green' => '#10b981',
                                'amber' => '#f59e0b',
                                'red' => '#ef4444',
                                'gray' => '#9ca3af',
                                default => '#9ca3af',
                            };
                            $pulseColor = match($pin['color']) {
                                'green' => '#6ee7b7',
                                'amber' => '#fcd34d',
                                'red' => '#fca5a5',
                                'gray' => '#d1d5db',
                                default => '#d1d5db',
                            };
                        @endphp
                        <g class="cursor-pointer" wire:click="selectAsset({{ $pin['id'] }})"
                           x-on:mouseenter="selectedPin = {{ $pin['id'] }}"
                           x-on:mouseleave="selectedPin = selectedPin === {{ $pin['id'] }} ? selectedPin : null">

                            {{-- Pulse ring for alerts --}}
                            @if($pin['color'] === 'red')
                                <circle cx="{{ $pin['x'] }}" cy="{{ $pin['y'] }}" r="14" fill="none" stroke="{{ $pulseColor }}" stroke-width="2" opacity="0.4">
                                    <animate attributeName="r" values="10;18" dur="1.5s" repeatCount="indefinite"/>
                                    <animate attributeName="opacity" values="0.6;0" dur="1.5s" repeatCount="indefinite"/>
                                </circle>
                            @endif

                            {{-- Pin circle --}}
                            <circle cx="{{ $pin['x'] }}" cy="{{ $pin['y'] }}" r="10" fill="{{ $fillColor }}" stroke="white" stroke-width="2.5" filter="url(#shadow)"
                                opacity="{{ $selectedAssetId === $pin['id'] ? '1' : '0.85' }}"
                                class="transition-all duration-150 hover:opacity-100"/>

                            {{-- Pin icon (dot) --}}
                            <circle cx="{{ $pin['x'] }}" cy="{{ $pin['y'] }}" r="3" fill="white"/>

                            {{-- Tooltip on hover --}}
                            <g x-show="selectedPin === {{ $pin['id'] }}" x-cloak>
                                <rect x="{{ $pin['x'] - 70 }}" y="{{ $pin['y'] - 42 }}" width="140" height="28" rx="6" fill="#1f2937" opacity="0.92"/>
                                <text x="{{ $pin['x'] }}" y="{{ $pin['y'] - 25 }}" text-anchor="middle" fill="white" font-size="10" font-weight="600" font-family="system-ui">{{ Str::limit($pin['name'], 18) }}</text>
                                <text x="{{ $pin['x'] }}" y="{{ $pin['y'] - 15 }}" text-anchor="middle" fill="{{ $pulseColor }}" font-size="8" font-family="system-ui">{{ $pin['status'] }}</text>
                            </g>
                        </g>
                    @endforeach

                    {{-- Scale indicator --}}
                    <line x1="650" y1="535" x2="750" y2="535" stroke="#9ca3af" stroke-width="1.5"/>
                    <line x1="650" y1="532" x2="650" y2="538" stroke="#9ca3af" stroke-width="1.5"/>
                    <line x1="750" y1="532" x2="750" y2="538" stroke="#9ca3af" stroke-width="1.5"/>
                    <text x="700" y="548" text-anchor="middle" fill="#9ca3af" font-size="9" font-family="system-ui">10 meters</text>
                </svg>
            </div>

            {{-- Legend --}}
            <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-3">
                <div class="flex items-center gap-6 flex-wrap">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Legend:</span>
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-3 h-3 rounded-full bg-emerald-500 ring-2 ring-white shadow"></span>
                        <span class="text-xs text-gray-600">Normal — all sensors OK, no active WOs</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-3 h-3 rounded-full bg-amber-500 ring-2 ring-white shadow"></span>
                        <span class="text-xs text-gray-600">Attention — open work orders</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-3 h-3 rounded-full bg-red-500 ring-2 ring-white shadow"></span>
                        <span class="text-xs text-gray-600">Alert — sensor anomaly detected</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-3 h-3 rounded-full bg-gray-400 ring-2 ring-white shadow"></span>
                        <span class="text-xs text-gray-600">Offline — no sensors attached</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Asset Detail Panel --}}
        <div class="xl:col-span-1">
            @if($this->selectedAssetDetail)
                @php $detail = $this->selectedAssetDetail; @endphp
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-6">
                    {{-- Header --}}
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between
                        {{ match($detail['color']) {
                            'green' => 'bg-emerald-50',
                            'amber' => 'bg-amber-50',
                            'red' => 'bg-red-50',
                            default => 'bg-gray-50',
                        } }}">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">{{ $detail['name'] }}</h3>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $detail['system_type'] }}</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider
                            {{ match($detail['color']) {
                                'green' => 'bg-emerald-100 text-emerald-700',
                                'amber' => 'bg-amber-100 text-amber-700',
                                'red' => 'bg-red-100 text-red-700',
                                default => 'bg-gray-100 text-gray-700',
                            } }}">
                            <span class="w-1.5 h-1.5 rounded-full
                                {{ match($detail['color']) {
                                    'green' => 'bg-emerald-500',
                                    'amber' => 'bg-amber-500',
                                    'red' => 'bg-red-500',
                                    default => 'bg-gray-500',
                                } }}"></span>
                            {{ $detail['status'] }}
                        </span>
                    </div>

                    {{-- Details --}}
                    <div class="p-5 space-y-4">
                        <div>
                            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Condition</p>
                            <p class="mt-1 text-sm font-medium text-gray-900">{{ $detail['condition'] }}</p>
                        </div>

                        <div>
                            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Last Sensor Reading</p>
                            <p class="mt-1 text-sm font-medium text-gray-900">{{ $detail['last_reading'] }}</p>
                        </div>

                        <div>
                            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Open Work Orders</p>
                            <p class="mt-1 text-sm font-medium {{ $detail['open_wo_count'] > 0 ? 'text-amber-600' : 'text-gray-900' }}">
                                {{ $detail['open_wo_count'] }}
                            </p>
                        </div>

                        <a href="{{ route('assets.show', $detail['id']) }}"
                            class="mt-4 w-full inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                            </svg>
                            View Asset Detail
                        </a>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                    </svg>
                    <p class="mt-3 text-sm font-medium text-gray-500">Select an asset pin</p>
                    <p class="mt-1 text-xs text-gray-400">Click on any pin on the floor plan to view asset details</p>
                </div>
            @endif
        </div>
    </div>
</div>
