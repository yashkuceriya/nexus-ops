<div>
    <style>
        @keyframes gradient-border {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .ai-card-border {
            background: linear-gradient(135deg, #10b981, #06b6d4, #10b981);
            background-size: 200% 200%;
            animation: gradient-border 4s ease infinite;
            padding: 1px;
            border-radius: 0.75rem;
        }
        .ai-card-inner {
            background: white;
            border-radius: calc(0.75rem - 1px);
        }
        .confidence-bar-fill {
            transition: width 1.5s ease-out;
        }
    </style>

    <div class="mb-8">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-gradient-to-br from-emerald-500 to-cyan-500 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">AI Analysis</h2>
                    <p class="text-xs text-gray-500">Pattern analysis across operational data</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-emerald-50 to-cyan-50 px-3 py-1 text-[10px] font-semibold text-emerald-700 border border-emerald-200">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                </svg>
                Powered by NexusOps AI
            </span>
        </div>

        {{-- Insights Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($insights as $index => $insight)
                <div class="ai-card-border">
                    <div class="ai-card-inner p-5">
                        {{-- Icon --}}
                        <div class="flex items-start gap-3 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-100 to-cyan-100 flex items-center justify-center flex-shrink-0">
                                @if($insight['icon'] === 'wrench')
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-5.1 5.1a2.121 2.121 0 11-3-3l5.1-5.1m0 0L3.357 8.108a1.125 1.125 0 01.265-1.542l2.872-2.155a1.125 1.125 0 011.542.266L11.42 15.17zm0 0l3.272-3.272M18.938 13.5A7.5 7.5 0 0021 7.5c0-.414-.168-.828-.5-1.134L18 3.857a.75.75 0 00-1.134.003l-2.512 2.636a.75.75 0 00.003 1.06l2.512 2.512a.75.75 0 001.06-.003l.944-.987" /></svg>
                                @elseif($insight['icon'] === 'signal')
                                    <svg class="w-4 h-4 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.348 14.651a3.75 3.75 0 010-5.303m5.304 0a3.75 3.75 0 010 5.303m-7.425 2.122a6.75 6.75 0 010-9.546m9.546 0a6.75 6.75 0 010 9.546" /></svg>
                                @elseif($insight['icon'] === 'calendar')
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                                @elseif($insight['icon'] === 'clock')
                                    <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                @endif
                            </div>
                            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider bg-gray-100 text-gray-500">
                                {{ str_replace('_', ' ', $insight['category']) }}
                            </span>
                        </div>

                        {{-- Typewriter Text --}}
                        <div class="mb-4 min-h-[3.5rem]"
                             x-data="{ shown: '', full: @js($insight['insight_text']), index: 0 }"
                             x-init="
                                let delay = {{ $index * 400 }};
                                setTimeout(() => {
                                    let interval = setInterval(() => {
                                        if (index < full.length) {
                                            shown += full[index];
                                            index++;
                                        } else {
                                            clearInterval(interval);
                                        }
                                    }, 12);
                                }, delay);
                             ">
                            <p class="text-sm text-gray-700 leading-relaxed" x-text="shown"></p>
                        </div>

                        {{-- Confidence Bar --}}
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">Confidence</span>
                                <span class="text-[10px] font-bold text-gray-600">{{ $insight['confidence'] }}%</span>
                            </div>
                            <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="confidence-bar-fill h-full rounded-full bg-gradient-to-r from-emerald-500 to-cyan-500"
                                     x-data="{ width: 0 }"
                                     x-init="setTimeout(() => width = {{ $insight['confidence'] }}, {{ 300 + $index * 400 }})"
                                     x-bind:style="'width: ' + width + '%'">
                                </div>
                            </div>
                        </div>

                        {{-- Action Button --}}
                        <button class="w-full text-center text-xs font-semibold text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg py-2 transition-colors">
                            {{ $insight['action_label'] }}
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
