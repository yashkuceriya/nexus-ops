<div
    class="fixed bottom-6 right-6 z-50 flex flex-col gap-3 max-w-sm"
    style="pointer-events: none;"
>
    @foreach($toasts as $toast)
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => { show = false; $wire.removeToast('{{ $toast['id'] }}') }, 5000)"
        x-show="show"
        x-transition:enter="transform ease-out duration-300 transition"
        x-transition:enter-start="translate-y-2 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="pointer-events: auto;"
        class="rounded-xl shadow-lg border px-4 py-3 flex items-start gap-3
            @switch($toast['type'])
                @case('success') bg-emerald-50 border-emerald-200 @break
                @case('error') bg-red-50 border-red-200 @break
                @case('warning') bg-amber-50 border-amber-200 @break
                @default bg-blue-50 border-blue-200
            @endswitch
        "
    >
        {{-- Icon --}}
        <div class="flex-shrink-0 mt-0.5">
            @switch($toast['type'])
                @case('success')
                    <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    @break
                @case('error')
                    <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    @break
                @case('warning')
                    <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    @break
                @default
                    <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
            @endswitch
        </div>

        {{-- Message --}}
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium
                @switch($toast['type'])
                    @case('success') text-emerald-800 @break
                    @case('error') text-red-800 @break
                    @case('warning') text-amber-800 @break
                    @default text-blue-800
                @endswitch
            ">{{ $toast['message'] }}</p>
        </div>

        {{-- Close button --}}
        <button
            @click="show = false; $wire.removeToast('{{ $toast['id'] }}')"
            class="flex-shrink-0 rounded-md p-1 transition-colors
                @switch($toast['type'])
                    @case('success') text-emerald-400 hover:text-emerald-600 hover:bg-emerald-100 @break
                    @case('error') text-red-400 hover:text-red-600 hover:bg-red-100 @break
                    @case('warning') text-amber-400 hover:text-amber-600 hover:bg-amber-100 @break
                    @default text-blue-400 hover:text-blue-600 hover:bg-blue-100
                @endswitch
            "
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    @endforeach
</div>
