<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-bind:class="{ 'dark': darkMode }" x-cloak>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — Nexus Ops</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        sidebar: { DEFAULT: '#1a1f2e', hover: '#252b3b', active: '#2d3548', border: '#2d3548' },
                        brand: { 50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0', 300: '#6ee7b7', 400: '#34d399', 500: '#10b981', 600: '#059669', 700: '#047857', 800: '#065f46', 900: '#064e3b' }
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    @livewireStyles
    <style>
        /* Micro-interactions */
        .btn-press { transform: scale(1); transition: transform 75ms; }
        .btn-press:active { transform: scale(0.95); }
        .card-hover { transition: all 200ms; }
        .card-hover:hover { transform: scale(1.01) translateY(-2px); }
        .link-glow { transition: color 200ms; }
        .link-glow:hover { color: #34d399; }
        .fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

        /* Tabular numbers for KPIs */
        .tabular-nums { font-variant-numeric: tabular-nums; }

        /* Smooth scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #374151; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #4b5563; }

        /* Copy animation */
        .copied-flash { animation: copiedFlash 0.6s ease-out; }
        @keyframes copiedFlash { 0% { background-color: rgba(16, 185, 129, 0.2); } 100% { background-color: transparent; } }
    </style>
</head>
<body class="h-full overflow-hidden">
<div class="flex h-full">
    {{-- Sidebar --}}
    <aside class="w-64 bg-sidebar flex flex-col flex-shrink-0">
        {{-- Brand --}}
        <div class="h-16 flex items-center px-5 border-b border-sidebar-border">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 bg-brand-600 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 0h.008v.008h-.008V7.5z" />
                    </svg>
                </div>
                <div>
                    <span class="text-base font-bold text-white">Nexus<span class="text-brand-400">Ops</span></span>
                    <p class="text-[10px] text-gray-500 uppercase tracking-wider leading-none mt-0.5">Facility Operations</p>
                </div>
            </div>
        </div>

        {{-- Search --}}
        <div class="px-4 py-3">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input type="text" placeholder="Search sensors, assets, or logs..."
                    class="w-full bg-sidebar-hover border border-sidebar-border rounded-lg py-2 pl-9 pr-3 text-xs text-gray-400 placeholder:text-gray-600 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20">
            </div>
        </div>

        {{-- Dark Mode Toggle --}}
        <div class="px-4 pb-2">
            <button
                @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)"
                class="flex items-center justify-between w-full px-3 py-2 rounded-lg bg-sidebar-hover border border-sidebar-border text-xs text-gray-400 hover:text-gray-200 transition-colors"
            >
                <span class="flex items-center gap-2">
                    {{-- Sun icon (shown in dark mode) --}}
                    <svg x-show="darkMode" class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                    </svg>
                    {{-- Moon icon (shown in light mode) --}}
                    <svg x-show="!darkMode" class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                    </svg>
                    <span x-text="darkMode ? 'Light Mode' : 'Dark Mode'"></span>
                </span>
                <div class="relative w-8 h-4 rounded-full transition-colors" :class="darkMode ? 'bg-brand-600' : 'bg-zinc-600'">
                    <div class="absolute top-0.5 left-0.5 w-3 h-3 rounded-full bg-white transition-transform" :class="darkMode ? 'translate-x-4' : 'translate-x-0'"></div>
                </div>
            </button>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 space-y-1 overflow-y-auto">
            <p class="px-3 pt-2 pb-1 text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Overview</p>

            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                {{ request()->routeIs('dashboard') ? 'bg-sidebar-active text-white' : 'text-gray-400 hover:bg-sidebar-hover hover:text-gray-200' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'text-brand-400' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
                Dashboard
            </a>

            <p class="px-3 pt-4 pb-1 text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Management</p>

            <a href="{{ route('projects.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                {{ request()->routeIs('projects.*') ? 'bg-sidebar-active text-white' : 'text-gray-400 hover:bg-sidebar-hover hover:text-gray-200' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('projects.*') ? 'text-brand-400' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                </svg>
                Projects
            </a>

            <a href="{{ route('work-orders.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                {{ request()->routeIs('work-orders.*') ? 'bg-sidebar-active text-white' : 'text-gray-400 hover:bg-sidebar-hover hover:text-gray-200' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('work-orders.*') ? 'text-brand-400' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75" />
                </svg>
                Work Orders
            </a>

            <a href="{{ route('assets.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                {{ request()->routeIs('assets.*') ? 'bg-sidebar-active text-white' : 'text-gray-400 hover:bg-sidebar-hover hover:text-gray-200' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('assets.*') ? 'text-brand-400' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                </svg>
                Assets
            </a>

            <a href="{{ route('health-matrix.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                {{ request()->routeIs('health-matrix.*') ? 'bg-sidebar-active text-white' : 'text-gray-400 hover:bg-sidebar-hover hover:text-gray-200' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('health-matrix.*') ? 'text-brand-400' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
                </svg>
                Health Matrix
            </a>

            <a href="{{ route('vendors.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                {{ request()->routeIs('vendors.*') ? 'bg-sidebar-active text-white' : 'text-gray-400 hover:bg-sidebar-hover hover:text-gray-200' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('vendors.*') ? 'text-brand-400' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                </svg>
                Vendors
            </a>

            <p class="px-3 pt-4 pb-1 text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Monitoring</p>

            <a href="{{ route('map.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                {{ request()->routeIs('map.*') ? 'bg-sidebar-active text-white' : 'text-gray-400 hover:bg-sidebar-hover hover:text-gray-200' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('map.*') ? 'text-brand-400' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                </svg>
                Facility Map
            </a>

            <a href="{{ route('sensors.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                {{ request()->routeIs('sensors.*') ? 'bg-sidebar-active text-white' : 'text-gray-400 hover:bg-sidebar-hover hover:text-gray-200' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('sensors.*') ? 'text-brand-400' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.348 14.651a3.75 3.75 0 010-5.303m5.304 0a3.75 3.75 0 010 5.303m-7.425 2.122a6.75 6.75 0 010-9.546m9.546 0a6.75 6.75 0 010 9.546M5.106 18.894c-3.808-3.808-3.808-9.98 0-13.788m13.788 0c3.808 3.808 3.808 9.98 0 13.788M12 12h.008v.008H12V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
                IoT Sensors
            </a>

            <a href="{{ route('floor-plan.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                {{ request()->routeIs('floor-plan.*') ? 'bg-sidebar-active text-white' : 'text-gray-400 hover:bg-sidebar-hover hover:text-gray-200' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('floor-plan.*') ? 'text-brand-400' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" />
                </svg>
                Floor Plan
            </a>

            <p class="px-3 pt-4 pb-1 text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Administration</p>

            <a href="{{ route('automation.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                {{ request()->routeIs('automation.*') ? 'bg-sidebar-active text-white' : 'text-gray-400 hover:bg-sidebar-hover hover:text-gray-200' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('automation.*') ? 'text-brand-400' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                </svg>
                Automation
            </a>

            <a href="{{ route('audit-log.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                {{ request()->routeIs('audit-log.*') ? 'bg-sidebar-active text-white' : 'text-gray-400 hover:bg-sidebar-hover hover:text-gray-200' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('audit-log.*') ? 'text-brand-400' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                Audit Trail
            </a>

            <a href="{{ route('reports.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                {{ request()->routeIs('reports.*') ? 'bg-sidebar-active text-white' : 'text-gray-400 hover:bg-sidebar-hover hover:text-gray-200' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('reports.*') ? 'text-brand-400' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                </svg>
                Reports
            </a>

            <a href="{{ route('docs.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                {{ request()->routeIs('docs.*') ? 'bg-sidebar-active text-white' : 'text-gray-400 hover:bg-sidebar-hover hover:text-gray-200' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('docs.*') ? 'text-brand-400' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
                </svg>
                API Docs
            </a>
        </nav>

        {{-- User --}}
        <div class="border-t border-sidebar-border p-3">
            <div class="flex items-center gap-3 px-2">
                <div class="w-9 h-9 rounded-full bg-brand-600 flex items-center justify-center text-white text-sm font-semibold flex-shrink-0">
                    {{ substr(auth()->user()->name ?? 'U', 0, 1) }}{{ substr(explode(' ', auth()->user()->name ?? 'U')[1] ?? '', 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-200 truncate">{{ auth()->user()->name ?? 'Guest' }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ ucfirst(auth()->user()->role ?? 'user') }}</p>
                </div>
                <div class="flex items-center gap-1">
                    <a href="{{ route('dashboard') }}" class="p-1.5 rounded-md text-gray-500 hover:text-gray-300 hover:bg-sidebar-hover transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="p-1.5 rounded-md text-gray-500 hover:text-red-400 hover:bg-sidebar-hover transition" title="Logout">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    {{-- Main Content --}}
    <div class="flex-1 flex flex-col overflow-hidden">
        {{-- Top Bar --}}
        <header class="h-16 bg-white dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700 flex items-center justify-between px-6 flex-shrink-0">
            <div>
                <h1 class="text-lg font-semibold text-gray-900 dark:text-zinc-100">{{ $title ?? 'Dashboard' }}</h1>
                @if(isset($subtitle))
                <p class="text-xs text-gray-500 dark:text-zinc-400">{{ $subtitle }}</p>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-400 dark:text-zinc-500">{{ now()->format('M d, Y') }}</span>
                @auth
                    @livewire('notification-bell')
                @endauth
            </div>
        </header>

        {{-- Page Content --}}
        <main class="flex-1 overflow-y-auto bg-gray-50 dark:bg-zinc-900 p-6 fade-in">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <footer class="bg-white dark:bg-zinc-800 border-t border-gray-200 dark:border-zinc-700 px-6 py-2 flex items-center justify-between flex-shrink-0">
            <span class="text-xs text-gray-400 dark:text-zinc-400">&copy; 2026 NexusOps. All rights reserved.</span>
            <span class="text-xs text-gray-400 dark:text-zinc-400">v1.0.0</span>
        </footer>
    </div>
</div>
@auth
    @livewire('command-palette')
    @livewire('toast-notifications')
@endauth
@livewireScripts
</body>
</html>
