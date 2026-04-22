<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>{{ $title ?? 'Request Portal' }} — NexusOps</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50: '#EEF2FF', 100: '#E0E7FF', 200: '#C7D2FE', 300: '#A5B4FC', 400: '#818CF8', 500: '#6366F1', 600: '#4F46E5', 700: '#4338CA', 800: '#3730A3', 900: '#312E81' }
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @livewireStyles
</head>
<body class="h-full">
    <div class="min-h-full">
        {{-- Header --}}
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 bg-brand-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 0h.008v.008h-.008V7.5z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-base font-bold text-gray-900">Nexus<span class="text-brand-600">Ops</span></span>
                            <p class="text-[10px] text-gray-500 uppercase tracking-wider leading-none mt-0.5">Request Portal</p>
                        </div>
                    </div>
                    <nav class="flex items-center gap-4">
                        <a href="/request" class="text-sm font-medium text-gray-600 hover:text-brand-600 transition-colors">Submit Request</a>
                        <a href="/request/track" class="text-sm font-medium text-gray-600 hover:text-brand-600 transition-colors">Track Request</a>
                    </nav>
                </div>
            </div>
        </header>

        {{-- Content --}}
        <main class="max-w-3xl mx-auto px-4 sm:px-6 py-8">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <footer class="bg-white border-t border-gray-200 mt-auto">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
                <span class="text-xs text-gray-400">&copy; 2026 NexusOps. All rights reserved.</span>
                <span class="text-xs text-gray-400">Occupant Request Portal</span>
            </div>
        </footer>
    </div>
    @livewireScripts
</body>
</html>
