<div wire:poll.30s class="w-full h-8 bg-zinc-900 overflow-hidden relative flex items-center rounded-lg mb-6">
    <style>
        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .ticker-track {
            display: flex;
            animation: marquee 30s linear infinite;
            white-space: nowrap;
        }
        .ticker-track:hover {
            animation-play-state: paused;
        }
        .ticker-glow {
            text-shadow: 0 0 6px rgba(16, 185, 129, 0.4);
        }
    </style>
    <div class="ticker-track">
        @for($loop_i = 0; $loop_i < 2; $loop_i++)
            @foreach($this->items as $item)
                <span class="inline-flex items-center gap-1.5 px-4 text-xs font-medium ticker-glow">
                    @if($item['trend'] === 'up')
                        <svg class="w-3 h-3 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" /></svg>
                    @elseif($item['trend'] === 'down')
                        <svg class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 4.5l15 15m0 0V8.25m0 11.25H8.25" /></svg>
                    @else
                        <svg class="w-3 h-3 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6" /></svg>
                    @endif
                    <span class="text-zinc-400">{{ $item['label'] }}:</span>
                    <span class="text-emerald-400 font-bold">{{ $item['value'] }}{{ $item['unit'] }}</span>
                </span>
                <span class="text-zinc-700 px-1">&bull;</span>
            @endforeach
        @endfor
    </div>
</div>
