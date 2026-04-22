<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>{{ $title ?? 'Dashboard' }} — NexusOps</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        mono: ['JetBrains Mono', 'ui-monospace', 'monospace'],
                    },
                    colors: {
                        canvas: '#F5F4F9',
                        ink: { DEFAULT: '#0F172A', muted: '#475569', soft: '#94A3B8' },
                        hairline: '#E5E7EB',
                        accent: { 50:'#EEF2FF', 100:'#E0E7FF', 200:'#C7D2FE', 300:'#A5B4FC', 400:'#818CF8', 500:'#6366F1', 600:'#4F46E5', 700:'#4338CA', 800:'#3730A3', 900:'#312E81' },
                        brand: { 50:'#EEF2FF', 100:'#E0E7FF', 200:'#C7D2FE', 300:'#A5B4FC', 400:'#818CF8', 500:'#6366F1', 600:'#4F46E5', 700:'#4338CA', 800:'#3730A3', 900:'#312E81' },
                    },
                    fontSize: {
                        'label': ['10px', { lineHeight: '14px', letterSpacing: '0.08em', fontWeight: '600' }],
                    },
                }
            }
        }
    </script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    @livewireStyles
    <style>
        html, body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; -webkit-font-smoothing: antialiased; }
        body { background: #F5F4F9; color: #0F172A; }

        /* Uppercase tracked label */
        .label-kicker { font-size: 10px; font-weight: 600; letter-spacing: 0.09em; text-transform: uppercase; color: #64748B; }

        /* Hairline cards */
        .card { background:#fff; border:1px solid #E5E7EB; border-radius: 12px; }

        /* KPI card */
        .kpi { padding: 18px 20px; }
        .kpi-value { font-size: 30px; font-weight: 700; letter-spacing: -0.02em; font-variant-numeric: tabular-nums; }

        /* Status chips (pill) */
        .chip { display:inline-flex; align-items:center; gap:6px; padding:3px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; letter-spacing: .02em; border:1px solid transparent; }
        .chip-pass    { background:#ECFDF5; color:#047857; border-color:#A7F3D0; }
        .chip-fail    { background:#FEF2F2; color:#B91C1C; border-color:#FECACA; }
        .chip-run     { background:#EFF6FF; color:#1D4ED8; border-color:#BFDBFE; }
        .chip-pending { background:#F8FAFC; color:#475569; border-color:#E2E8F0; }
        .chip-warn    { background:#FFFBEB; color:#B45309; border-color:#FDE68A; }
        .chip-accent  { background:#EEF2FF; color:#4338CA; border-color:#C7D2FE; }

        /* Status dot */
        .dot { width:8px; height:8px; border-radius:999px; display:inline-block; }
        .dot-pass { background:#10B981; } .dot-fail{background:#EF4444;} .dot-run{background:#3B82F6;} .dot-pending{background:#94A3B8;} .dot-warn{background:#F59E0B;}

        /* Matrix cells */
        .cell { width:40px; height:32px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; border:1px solid transparent; }
        .cell-pass { background:#ECFDF5; border-color:#A7F3D0; color:#047857; }
        .cell-fail { background:#FEF2F2; border-color:#FECACA; color:#B91C1C; }
        .cell-run  { background:#EEF2FF; border-color:#C7D2FE; color:#4338CA; }
        .cell-none { background:#F8FAFC; border-color:#E2E8F0; color:#94A3B8; }

        /* Primary button */
        .btn-primary { background:#4F46E5; color:#fff; padding:8px 14px; border-radius: 8px; font-weight:600; font-size:13px; transition: background 120ms ease; }
        .btn-primary:hover { background:#4338CA; }
        .btn-primary:active { transform: scale(.985); }

        .btn-ghost { background:#fff; color:#0F172A; padding:8px 14px; border-radius: 8px; font-weight:600; font-size:13px; border:1px solid #E5E7EB; }
        .btn-ghost:hover { background:#F8FAFC; }

        /* Sidebar nav item */
        .nav-item { display:flex; align-items:center; gap:10px; padding: 8px 12px; border-radius:8px; font-size:13px; font-weight:500; color:#475569; transition: background 120ms; }
        .nav-item:hover { background:#EEF2FF; color:#312E81; }
        .nav-item.active { background:#EEF2FF; color:#4338CA; font-weight:600; }
        .nav-item.active .nav-icon { color:#4F46E5; }
        .nav-icon { width:18px; height:18px; color:#94A3B8; flex-shrink:0; }
        .nav-section { font-size:10px; letter-spacing:0.1em; text-transform:uppercase; font-weight:700; color:#94A3B8; padding: 14px 14px 6px; }

        /* Mono text */
        .mono { font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 11px; letter-spacing: 0.02em; }

        /* Tabular nums */
        .tabular-nums { font-variant-numeric: tabular-nums; }

        /* Fade-in */
        .fade-in { animation: fadeIn .25s ease-out; }
        @keyframes fadeIn { from { opacity:0; transform: translateY(4px);} to {opacity:1; transform:translateY(0);} }

        /* Scrollbar */
        ::-webkit-scrollbar { width:6px; height:6px; }
        ::-webkit-scrollbar-thumb { background:#CBD5E1; border-radius:3px; }
        ::-webkit-scrollbar-thumb:hover { background:#94A3B8; }

        /* Hairline utility */
        .hairline { border:1px solid #E5E7EB; }
        .hairline-b { border-bottom:1px solid #E5E7EB; }
        .hairline-t { border-top:1px solid #E5E7EB; }
        .hairline-r { border-right:1px solid #E5E7EB; }
    </style>
</head>
<body class="h-full overflow-hidden">
<div class="flex h-full">
    {{-- Sidebar --}}
    <aside class="w-60 bg-white hairline-r flex flex-col flex-shrink-0">
        {{-- Brand --}}
        <a href="{{ route('dashboard') }}" class="h-16 flex items-center px-5 hairline-b">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-accent-600 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25M3 6.695c0-.836.88-1.38 1.628-1.006L9.503 8.311c.317.158.69.158 1.006 0l4.994-2.497a1.125 1.125 0 011.006 0l4.875 2.437c.381.19.622.58.622 1.006V19.18c0 .836-.88 1.38-1.628 1.006l-3.869-1.934a1.125 1.125 0 00-1.006 0l-4.994 2.497c-.317.158-.69.158-1.006 0l-4.875-2.437A1.125 1.125 0 013 17.305V6.695z" />
                    </svg>
                </div>
                <div>
                    <div class="text-[15px] font-bold tracking-tight text-ink leading-none">NexusOps</div>
                    <div class="text-[9px] font-bold tracking-[0.15em] text-ink-soft mt-1">FACILITY OS</div>
                </div>
            </div>
        </a>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-3 pt-2 pb-3">
            <p class="nav-section">Overview</p>
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25a2.25 2.25 0 01-2.25-2.25v-2.25z"/></svg>
                Dashboard
            </a>

            <p class="nav-section">Commissioning</p>
            <a href="{{ route('fpt.scripts.index') }}" class="nav-item {{ request()->routeIs('fpt.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                FPT / Cx Tests
            </a>
            <a href="{{ route('deficiencies.index') }}" class="nav-item {{ request()->routeIs('deficiencies.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                Deficiency Board
            </a>
            <a href="{{ route('reports.commissioning') }}" class="nav-item {{ request()->routeIs('reports.commissioning') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.306a11.95 11.95 0 015.814-5.518l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg>
                Cx Analytics
            </a>
            <a href="{{ route('lessons-learned.index') }}" class="nav-item {{ request()->routeIs('lessons-learned.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                Lessons Learned
            </a>

            <p class="nav-section">Operations</p>
            <a href="{{ route('projects.index') }}" class="nav-item {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"/></svg>
                Projects
            </a>
            <a href="{{ route('work-orders.index') }}" class="nav-item {{ request()->routeIs('work-orders.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
                Work Orders
            </a>
            <a href="{{ route('assets.index') }}" class="nav-item {{ request()->routeIs('assets.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                Assets
            </a>
            <a href="{{ route('vendors.index') }}" class="nav-item {{ request()->routeIs('vendors.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                Vendors
            </a>

            <p class="nav-section">Monitoring</p>
            <a href="{{ route('sensors.index') }}" class="nav-item {{ request()->routeIs('sensors.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.348 14.651a3.75 3.75 0 010-5.303m5.304 0a3.75 3.75 0 010 5.303m-7.425 2.122a6.75 6.75 0 010-9.546m9.546 0a6.75 6.75 0 010 9.546M5.106 18.894c-3.808-3.808-3.808-9.98 0-13.788m13.788 0c3.808 3.808 3.808 9.98 0 13.788"/></svg>
                IoT Sensors
            </a>
            <a href="{{ route('floor-plan.index') }}" class="nav-item {{ request()->routeIs('floor-plan.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/></svg>
                Floor Plan
            </a>
            <a href="{{ route('health-matrix.index') }}" class="nav-item {{ request()->routeIs('health-matrix.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5"/></svg>
                Health Matrix
            </a>
            <a href="{{ route('map.index') }}" class="nav-item {{ request()->routeIs('map.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                Facility Map
            </a>

            <p class="nav-section">Administration</p>
            <a href="{{ route('automation.index') }}" class="nav-item {{ request()->routeIs('automation.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                Automation
            </a>
            <a href="{{ route('audit-log.index') }}" class="nav-item {{ request()->routeIs('audit-log.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                Audit Trail
            </a>
            <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                Reports
            </a>
            <a href="{{ route('docs.index') }}" class="nav-item {{ request()->routeIs('docs.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5"/></svg>
                API Docs
            </a>
        </nav>

        {{-- Pinned CTA + user --}}
        <div class="px-3 pb-3 relative" x-data="{ quickOpen: false }" @click.outside="quickOpen = false">
            <button type="button" @click="quickOpen = !quickOpen" class="btn-primary w-full inline-flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                New Commission
            </button>
            <div x-show="quickOpen" x-transition.origin.bottom.left x-cloak
                 class="absolute bottom-full left-3 right-3 mb-2 card p-1.5 shadow-lg z-50">
                <p class="label-kicker px-2 pt-1 pb-1.5">Quick Create</p>
                <a href="{{ route('work-orders.index') }}?action=new" class="flex items-center gap-2.5 px-2.5 py-2 rounded-md hover:bg-accent-50 text-[13px] text-ink">
                    <svg class="w-4 h-4 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08M15.75 18.75h.008v.008h-.008V18.75zM12 5.625c0-.621.504-1.125 1.125-1.125h1.5c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125h-1.5A1.125 1.125 0 0112 7.125v-1.5z"/></svg>
                    Work Order
                </a>
                <a href="{{ route('projects.index') }}" class="flex items-center gap-2.5 px-2.5 py-2 rounded-md hover:bg-accent-50 text-[13px] text-ink">
                    <svg class="w-4 h-4 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75"/></svg>
                    Project
                </a>
                <a href="{{ route('fpt.scripts.index') }}" class="flex items-center gap-2.5 px-2.5 py-2 rounded-md hover:bg-accent-50 text-[13px] text-ink">
                    <svg class="w-4 h-4 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75"/></svg>
                    FPT Script
                </a>
                <a href="{{ route('automation.create') }}" class="flex items-center gap-2.5 px-2.5 py-2 rounded-md hover:bg-accent-50 text-[13px] text-ink">
                    <svg class="w-4 h-4 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                    Automation Rule
                </a>
            </div>
        </div>
        <div class="hairline-t p-3">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-accent-600 flex items-center justify-center text-white text-[11px] font-bold flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}{{ strtoupper(substr(explode(' ', auth()->user()->name ?? 'U ')[1] ?? '', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[13px] font-semibold text-ink truncate leading-tight">{{ auth()->user()->name ?? 'Guest' }}</p>
                    <p class="text-[11px] text-ink-soft truncate">{{ ucfirst(auth()->user()->role ?? 'user') }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="p-1.5 rounded-md text-ink-soft hover:text-red-600 hover:bg-red-50 transition" title="Logout">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main Content --}}
    <div class="flex-1 flex flex-col overflow-hidden">
        {{-- Top Bar --}}
        <header class="h-14 bg-white hairline-b flex items-center justify-between px-6 flex-shrink-0">
            <div class="flex items-center gap-6">
                <nav class="flex items-center gap-1">
                    <a href="{{ route('projects.index') }}" class="px-3 py-1.5 rounded-md text-[13px] font-semibold {{ request()->routeIs('projects.*') ? 'text-accent-700 bg-accent-50' : 'text-ink-muted hover:text-ink hover:bg-slate-50' }}">Projects</a>
                    <a href="{{ route('assets.index') }}" class="px-3 py-1.5 rounded-md text-[13px] font-semibold {{ request()->routeIs('assets.*') ? 'text-accent-700 bg-accent-50' : 'text-ink-muted hover:text-ink hover:bg-slate-50' }}">Assets</a>
                    <a href="{{ route('reports.commissioning') }}" class="px-3 py-1.5 rounded-md text-[13px] font-semibold {{ request()->routeIs('reports.*') ? 'text-accent-700 bg-accent-50' : 'text-ink-muted hover:text-ink hover:bg-slate-50' }}">Analytics</a>
                </nav>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="window.dispatchEvent(new KeyboardEvent('keydown', { key: 'k', metaKey: true, ctrlKey: true }))"
                    class="hidden md:inline-flex items-center gap-2 rounded-md border border-slate-200 bg-slate-50/60 hover:bg-white px-2.5 py-1 text-[12px] text-ink-muted transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    <span>Search or jump to…</span>
                    <kbd class="mono text-[10px] px-1.5 py-0.5 rounded border border-slate-200 bg-white text-ink-soft">⌘K</kbd>
                </button>
                {{-- Tenant switcher --}}
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" @click="open = !open" class="btn-ghost inline-flex items-center gap-2 text-[12px]">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                        <span class="truncate max-w-[140px]">{{ auth()->user()?->tenant?->name ?? 'Tenant' }}</span>
                        <svg class="w-3 h-3 text-ink-soft transition-transform" :class="open && 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 011.08 1.04l-4.25 4.39a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"/></svg>
                    </button>
                    <div x-show="open" x-transition.origin.top.right x-cloak class="absolute right-0 top-full mt-1 w-72 card p-2 shadow-lg z-50">
                        <p class="label-kicker px-2 pt-1 pb-1.5">Active Tenant</p>
                        <div class="flex items-center gap-2.5 px-2.5 py-2 rounded-md bg-accent-50">
                            <div class="w-7 h-7 rounded-md bg-accent-600 text-white flex items-center justify-center text-[11px] font-bold">
                                {{ strtoupper(substr(auth()->user()?->tenant?->name ?? 'N', 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[13px] font-semibold text-ink truncate">{{ auth()->user()?->tenant?->name ?? 'No tenant' }}</p>
                                <p class="text-[11px] text-ink-soft mono truncate">{{ auth()->user()?->tenant?->slug ?? '—' }}</p>
                            </div>
                            <svg class="w-4 h-4 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </div>
                        <div class="hairline-t my-2"></div>
                        <button type="button" disabled class="w-full text-left px-2.5 py-1.5 rounded-md text-[12px] text-ink-soft flex items-center gap-2 cursor-not-allowed opacity-60" title="Multi-tenant switching available on Enterprise plan">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            Add tenant <span class="ml-auto chip chip-accent text-[9px]">ENTERPRISE</span>
                        </button>
                    </div>
                </div>

                @auth
                    @livewire('notification-bell')
                @endauth

                {{-- Help popover --}}
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" @click="open = !open" class="w-8 h-8 rounded-full hover:bg-slate-100 inline-flex items-center justify-center text-ink-soft" title="Help and shortcuts">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/></svg>
                    </button>
                    <div x-show="open" x-transition.origin.top.right x-cloak class="absolute right-0 top-full mt-1 w-72 card p-3 shadow-lg z-50">
                        <p class="label-kicker mb-2">Keyboard Shortcuts</p>
                        <ul class="text-[12px] text-ink-muted space-y-1.5">
                            <li class="flex items-center justify-between"><span>Open command palette</span><kbd class="mono text-[10px] px-1.5 py-0.5 rounded border border-slate-200 bg-slate-50">⌘K</kbd></li>
                            <li class="flex items-center justify-between"><span>Close modal / palette</span><kbd class="mono text-[10px] px-1.5 py-0.5 rounded border border-slate-200 bg-slate-50">ESC</kbd></li>
                            <li class="flex items-center justify-between"><span>Navigate palette results</span><kbd class="mono text-[10px] px-1.5 py-0.5 rounded border border-slate-200 bg-slate-50">↑ ↓</kbd></li>
                            <li class="flex items-center justify-between"><span>Jump to selection</span><kbd class="mono text-[10px] px-1.5 py-0.5 rounded border border-slate-200 bg-slate-50">ENTER</kbd></li>
                        </ul>
                        <div class="hairline-t my-3"></div>
                        <a href="{{ route('docs.index') }}" class="flex items-center justify-between px-1 text-[12px] text-accent-700 font-semibold hover:text-accent-800">API & integrations reference →</a>
                    </div>
                </div>

                {{-- Avatar menu --}}
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" @click="open = !open" class="w-8 h-8 rounded-full bg-accent-600 flex items-center justify-center text-white text-[11px] font-bold ring-2 ring-white hover:ring-accent-200 transition-all" title="{{ auth()->user()->name ?? 'Guest' }}">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}{{ strtoupper(substr(explode(' ', auth()->user()->name ?? 'U ')[1] ?? '', 0, 1)) }}
                    </button>
                    <div x-show="open" x-transition.origin.top.right x-cloak class="absolute right-0 top-full mt-1 w-60 card p-2 shadow-lg z-50">
                        <div class="px-2.5 py-2">
                            <p class="text-[13px] font-semibold text-ink truncate">{{ auth()->user()->name ?? 'Guest' }}</p>
                            <p class="text-[11px] text-ink-soft truncate">{{ auth()->user()->email ?? '' }}</p>
                            <span class="chip chip-accent mt-1.5 text-[10px]">{{ strtoupper(auth()->user()->role ?? 'USER') }}</span>
                        </div>
                        <div class="hairline-t my-1"></div>
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md hover:bg-slate-50 text-[13px] text-ink">
                            <svg class="w-4 h-4 text-ink-soft" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            Profile
                        </a>
                        <a href="{{ route('audit-log.index') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md hover:bg-slate-50 text-[13px] text-ink">
                            <svg class="w-4 h-4 text-ink-soft" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25"/></svg>
                            Activity log
                        </a>
                        <a href="{{ route('docs.index') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md hover:bg-slate-50 text-[13px] text-ink">
                            <svg class="w-4 h-4 text-ink-soft" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25"/></svg>
                            API Docs
                        </a>
                        <div class="hairline-t my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md hover:bg-red-50 hover:text-red-600 text-[13px] text-ink transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="flex-1 overflow-y-auto fade-in" style="background:#F5F4F9;">
            <div class="px-6 py-6 max-w-[1600px] mx-auto">
                @isset($title)
                    <div class="mb-5 flex items-start justify-between gap-4 flex-wrap">
                        <div>
                            @isset($breadcrumbs)<p class="label-kicker mb-1">{!! $breadcrumbs !!}</p>@endisset
                            <h1 class="text-2xl font-bold tracking-tight text-ink">{{ $title }}</h1>
                            @isset($subtitle)<p class="text-[13px] text-ink-muted mt-1">{{ $subtitle }}</p>@endisset
                        </div>
                        @isset($actions){{ $actions }}@endisset
                    </div>
                @endisset
                {{ $slot }}
            </div>
        </main>
        {{-- Status strip --}}
        <footer class="h-7 bg-white hairline-t flex items-center justify-between px-4 text-[10px] mono text-ink-soft flex-shrink-0">
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1.5"><span class="dot dot-pass animate-pulse"></span>SYSTEM NOMINAL</span>
                <span>TENANT: {{ strtoupper(auth()->user()?->tenant?->slug ?? 'NONE') }}</span>
                <span class="hidden md:inline">NODE: NEXUS_EDGE_01</span>
                <span class="hidden lg:inline">ENV: {{ strtoupper(app()->environment()) }}</span>
            </div>
            <div class="flex items-center gap-4">
                <span>LAT {{ now()->format('H:i:s') }} UTC</span>
                <span>v1.0.0 · build {{ substr(hash('xxh32', app_path()), 0, 7) }}</span>
            </div>
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
