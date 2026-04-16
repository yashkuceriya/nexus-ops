<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NexusOps — Intelligent Facility Operations</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0', 300: '#6ee7b7', 400: '#34d399', 500: '#10b981', 600: '#059669', 700: '#047857', 800: '#065f46', 900: '#064e3b' }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { font-family: 'Inter', system-ui, -apple-system, sans-serif; }

        /* Animated gradient mesh background */
        .gradient-mesh {
            background: #0a0a0f;
            position: relative;
            overflow: hidden;
        }
        .gradient-mesh::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background:
                radial-gradient(ellipse 600px 600px at 20% 20%, rgba(16, 185, 129, 0.08) 0%, transparent 70%),
                radial-gradient(ellipse 800px 800px at 80% 10%, rgba(6, 182, 212, 0.06) 0%, transparent 70%),
                radial-gradient(ellipse 600px 600px at 60% 60%, rgba(59, 130, 246, 0.05) 0%, transparent 70%),
                radial-gradient(ellipse 500px 500px at 10% 80%, rgba(16, 185, 129, 0.04) 0%, transparent 70%);
            animation: meshMove 20s ease-in-out infinite alternate;
            z-index: 0;
            pointer-events: none;
        }
        @keyframes meshMove {
            0% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(2%, -3%) rotate(1deg); }
            50% { transform: translate(-1%, 2%) rotate(-0.5deg); }
            75% { transform: translate(3%, 1%) rotate(0.5deg); }
            100% { transform: translate(-2%, -1%) rotate(-1deg); }
        }

        /* Noise overlay for texture */
        .noise-overlay::after {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");
            z-index: 0;
            pointer-events: none;
        }

        /* Gradient text utility */
        .text-gradient {
            background: linear-gradient(135deg, #34d399 0%, #22d3ee 40%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Card glow on hover */
        .card-glow {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        .card-glow::before {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: inherit;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.0), rgba(6, 182, 212, 0.0));
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: -1;
        }
        .card-glow:hover::before {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(6, 182, 212, 0.15));
        }
        .card-glow:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 40px -10px rgba(16, 185, 129, 0.15);
            border-color: rgba(16, 185, 129, 0.3) !important;
        }

        /* Browser frame for mock dashboard */
        .browser-frame {
            transform: perspective(1200px) rotateY(-5deg);
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .browser-frame:hover {
            transform: perspective(1200px) rotateY(-2deg);
        }

        /* Tabular nums for stats */
        .tabular-nums { font-variant-numeric: tabular-nums; }

        /* Smooth scroll bar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0a0a0f; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #334155; }

        /* Glow pulse for CTA */
        .glow-btn {
            position: relative;
        }
        .glow-btn::after {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: inherit;
            background: linear-gradient(135deg, #10b981, #06b6d4, #3b82f6);
            opacity: 0;
            z-index: -1;
            filter: blur(12px);
            transition: opacity 0.3s ease;
        }
        .glow-btn:hover::after {
            opacity: 0.5;
        }

        /* Grid lines background */
        .grid-bg {
            background-image:
                linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 60px 60px;
        }
    </style>
</head>
<body class="gradient-mesh noise-overlay text-white antialiased">

    {{-- ===== NAVBAR ===== --}}
    <nav class="fixed top-0 left-0 right-0 z-50 border-b border-white/5" x-data="{ scrolled: false, mobileOpen: false }"
         @scroll.window="scrolled = (window.scrollY > 20)"
         :class="scrolled ? 'bg-zinc-900/80 backdrop-blur-xl' : 'bg-transparent'">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-cyan-500 rounded-lg flex items-center justify-center shadow-lg shadow-emerald-500/20">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 0h.008v.008h-.008V7.5z" />
                        </svg>
                    </div>
                    <span class="text-lg font-bold text-white">Nexus<span class="text-emerald-400">Ops</span></span>
                </div>

                {{-- Desktop Nav Links --}}
                <div class="hidden md:flex items-center gap-8">
                    <a href="#features" class="text-sm text-zinc-400 hover:text-white transition-colors duration-200">Features</a>
                    <a href="#pricing" class="text-sm text-zinc-400 hover:text-white transition-colors duration-200">Pricing</a>
                    <a href="#" class="text-sm text-zinc-400 hover:text-white transition-colors duration-200">Docs</a>
                </div>

                {{-- Auth Buttons --}}
                <div class="hidden md:flex items-center gap-3">
                    <a href="{{ route('login') }}" class="text-sm font-medium text-zinc-300 hover:text-white transition-colors px-4 py-2 rounded-lg hover:bg-white/5">
                        Sign In
                    </a>
                    <a href="{{ route('login') }}" class="text-sm font-medium text-white bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 px-4 py-2 rounded-lg transition-all shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/30">
                        Get Started
                    </a>
                </div>

                {{-- Mobile menu button --}}
                <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 text-zinc-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        <path x-show="mobileOpen" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Mobile menu --}}
            <div x-show="mobileOpen" x-transition class="md:hidden pb-4 space-y-2">
                <a href="#features" class="block text-sm text-zinc-400 hover:text-white py-2">Features</a>
                <a href="#pricing" class="block text-sm text-zinc-400 hover:text-white py-2">Pricing</a>
                <a href="#" class="block text-sm text-zinc-400 hover:text-white py-2">Docs</a>
                <div class="pt-2 flex flex-col gap-2">
                    <a href="{{ route('login') }}" class="text-sm text-center font-medium text-zinc-300 px-4 py-2 rounded-lg border border-zinc-700">Sign In</a>
                    <a href="{{ route('login') }}" class="text-sm text-center font-medium text-white bg-emerald-600 px-4 py-2 rounded-lg">Get Started</a>
                </div>
            </div>
        </div>
    </nav>

    {{-- ===== HERO SECTION ===== --}}
    <section class="relative min-h-screen flex items-center pt-16 grid-bg">
        <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-8 py-20 lg:py-32">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                {{-- Hero text --}}
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-emerald-500/20 bg-emerald-500/5 mb-8">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-xs font-medium text-emerald-400">Platform v3.0 now available</span>
                    </div>

                    <h1 class="text-5xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight leading-[1.05] mb-6">
                        <span class="text-gradient">Intelligent<br>Facility<br>Operations</span>
                    </h1>

                    <p class="text-lg sm:text-xl text-zinc-400 leading-relaxed max-w-lg mb-10">
                        Real-time monitoring, predictive maintenance, and smart automation for modern facilities. One platform to manage everything.
                    </p>

                    <div class="flex flex-wrap items-center gap-4 mb-12">
                        <a href="{{ route('login') }}" class="glow-btn relative inline-flex items-center gap-2 px-7 py-3.5 text-sm font-semibold text-white bg-gradient-to-r from-emerald-600 via-emerald-500 to-teal-500 rounded-xl transition-all shadow-xl shadow-emerald-500/20 hover:shadow-emerald-500/30 hover:scale-[1.02]">
                            Start Free Trial
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                        <a href="#" class="inline-flex items-center gap-2 px-7 py-3.5 text-sm font-semibold text-zinc-300 border border-zinc-700 hover:border-zinc-500 rounded-xl transition-all hover:bg-white/5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
                            </svg>
                            Watch Demo
                        </a>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-500 to-purple-600 border-2 border-zinc-900 flex items-center justify-center text-[10px] font-bold">JK</div>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 border-2 border-zinc-900 flex items-center justify-center text-[10px] font-bold">AM</div>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 border-2 border-zinc-900 flex items-center justify-center text-[10px] font-bold">RL</div>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 border-2 border-zinc-900 flex items-center justify-center text-[10px] font-bold">SP</div>
                        </div>
                        <div class="text-sm text-zinc-500">
                            <span class="text-zinc-300 font-medium">2,400+</span> facility managers trust NexusOps
                        </div>
                    </div>
                </div>

                {{-- Mock dashboard browser frame --}}
                <div class="hidden lg:block">
                    <div class="browser-frame relative">
                        {{-- Glow behind the frame --}}
                        <div class="absolute -inset-4 bg-gradient-to-r from-emerald-500/10 via-cyan-500/10 to-blue-500/10 rounded-2xl blur-2xl"></div>

                        <div class="relative bg-zinc-900 rounded-xl border border-zinc-800 shadow-2xl shadow-black/50 overflow-hidden">
                            {{-- Browser chrome --}}
                            <div class="flex items-center gap-2 px-4 py-3 bg-zinc-800/80 border-b border-zinc-700/50">
                                <div class="flex gap-1.5">
                                    <div class="w-3 h-3 rounded-full bg-red-500/80"></div>
                                    <div class="w-3 h-3 rounded-full bg-yellow-500/80"></div>
                                    <div class="w-3 h-3 rounded-full bg-green-500/80"></div>
                                </div>
                                <div class="flex-1 mx-4">
                                    <div class="bg-zinc-700/50 rounded-md px-3 py-1 text-xs text-zinc-500 text-center">app.nexusops.com/dashboard</div>
                                </div>
                            </div>

                            {{-- Mock dashboard content --}}
                            <div class="p-4 space-y-3">
                                {{-- Mock top bar --}}
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 bg-emerald-500/20 rounded flex items-center justify-center">
                                            <div class="w-3 h-3 bg-emerald-500 rounded-sm"></div>
                                        </div>
                                        <div class="h-2.5 w-20 bg-zinc-700 rounded"></div>
                                    </div>
                                    <div class="flex gap-2">
                                        <div class="h-7 w-20 bg-zinc-800 rounded border border-zinc-700"></div>
                                        <div class="h-7 w-7 bg-emerald-500/20 rounded"></div>
                                    </div>
                                </div>

                                {{-- Mock stat cards --}}
                                <div class="grid grid-cols-4 gap-2">
                                    <div class="bg-zinc-800/50 rounded-lg p-3 border border-zinc-700/30">
                                        <div class="h-2 w-12 bg-zinc-600 rounded mb-2"></div>
                                        <div class="h-4 w-8 bg-emerald-500/30 rounded"></div>
                                    </div>
                                    <div class="bg-zinc-800/50 rounded-lg p-3 border border-zinc-700/30">
                                        <div class="h-2 w-10 bg-zinc-600 rounded mb-2"></div>
                                        <div class="h-4 w-10 bg-cyan-500/30 rounded"></div>
                                    </div>
                                    <div class="bg-zinc-800/50 rounded-lg p-3 border border-zinc-700/30">
                                        <div class="h-2 w-14 bg-zinc-600 rounded mb-2"></div>
                                        <div class="h-4 w-6 bg-blue-500/30 rounded"></div>
                                    </div>
                                    <div class="bg-zinc-800/50 rounded-lg p-3 border border-zinc-700/30">
                                        <div class="h-2 w-10 bg-zinc-600 rounded mb-2"></div>
                                        <div class="h-4 w-12 bg-amber-500/30 rounded"></div>
                                    </div>
                                </div>

                                {{-- Mock chart --}}
                                <div class="bg-zinc-800/50 rounded-lg p-4 border border-zinc-700/30">
                                    <div class="h-2 w-24 bg-zinc-600 rounded mb-4"></div>
                                    <div class="flex items-end gap-1 h-24">
                                        <div class="flex-1 bg-emerald-500/20 rounded-t" style="height:40%"></div>
                                        <div class="flex-1 bg-emerald-500/30 rounded-t" style="height:65%"></div>
                                        <div class="flex-1 bg-emerald-500/20 rounded-t" style="height:45%"></div>
                                        <div class="flex-1 bg-emerald-500/40 rounded-t" style="height:80%"></div>
                                        <div class="flex-1 bg-emerald-500/30 rounded-t" style="height:60%"></div>
                                        <div class="flex-1 bg-cyan-500/40 rounded-t" style="height:90%"></div>
                                        <div class="flex-1 bg-cyan-500/30 rounded-t" style="height:70%"></div>
                                        <div class="flex-1 bg-cyan-500/20 rounded-t" style="height:55%"></div>
                                        <div class="flex-1 bg-blue-500/30 rounded-t" style="height:75%"></div>
                                        <div class="flex-1 bg-blue-500/20 rounded-t" style="height:50%"></div>
                                        <div class="flex-1 bg-blue-500/30 rounded-t" style="height:85%"></div>
                                        <div class="flex-1 bg-blue-500/40 rounded-t" style="height:95%"></div>
                                    </div>
                                </div>

                                {{-- Mock table --}}
                                <div class="bg-zinc-800/50 rounded-lg border border-zinc-700/30 overflow-hidden">
                                    <div class="grid grid-cols-4 gap-4 px-3 py-2 border-b border-zinc-700/30">
                                        <div class="h-2 w-12 bg-zinc-600 rounded"></div>
                                        <div class="h-2 w-16 bg-zinc-600 rounded"></div>
                                        <div class="h-2 w-10 bg-zinc-600 rounded"></div>
                                        <div class="h-2 w-14 bg-zinc-600 rounded"></div>
                                    </div>
                                    <div class="grid grid-cols-4 gap-4 px-3 py-2 border-b border-zinc-700/20">
                                        <div class="h-2 w-14 bg-zinc-700 rounded"></div>
                                        <div class="h-2 w-20 bg-zinc-700 rounded"></div>
                                        <div class="h-4 w-12 bg-emerald-500/20 rounded-full"></div>
                                        <div class="h-2 w-8 bg-zinc-700 rounded"></div>
                                    </div>
                                    <div class="grid grid-cols-4 gap-4 px-3 py-2 border-b border-zinc-700/20">
                                        <div class="h-2 w-10 bg-zinc-700 rounded"></div>
                                        <div class="h-2 w-16 bg-zinc-700 rounded"></div>
                                        <div class="h-4 w-12 bg-amber-500/20 rounded-full"></div>
                                        <div class="h-2 w-12 bg-zinc-700 rounded"></div>
                                    </div>
                                    <div class="grid grid-cols-4 gap-4 px-3 py-2">
                                        <div class="h-2 w-16 bg-zinc-700 rounded"></div>
                                        <div class="h-2 w-12 bg-zinc-700 rounded"></div>
                                        <div class="h-4 w-12 bg-cyan-500/20 rounded-full"></div>
                                        <div class="h-2 w-10 bg-zinc-700 rounded"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== STATS BAR ===== --}}
    <section class="relative z-10 border-y border-white/5 bg-zinc-900/50 backdrop-blur-sm"
             x-data="{
                visible: false,
                stats: [
                    { target: 500, suffix: '+', label: 'Facilities', current: 0 },
                    { target: 10000, suffix: '+', label: 'Work Orders', current: 0 },
                    { target: 99.9, suffix: '%', label: 'Uptime', current: 0, decimal: true },
                    { target: 150, suffix: '+', label: 'Enterprise Clients', current: 0 }
                ],
                animateCounters() {
                    if (this.visible) return;
                    this.visible = true;
                    this.stats.forEach((stat) => {
                        const duration = 2000;
                        const startTime = performance.now();
                        const animate = (currentTime) => {
                            const elapsed = currentTime - startTime;
                            const progress = Math.min(elapsed / duration, 1);
                            const eased = 1 - Math.pow(1 - progress, 3);
                            stat.current = stat.decimal
                                ? parseFloat((eased * stat.target).toFixed(1))
                                : Math.floor(eased * stat.target);
                            if (progress < 1) requestAnimationFrame(animate);
                        };
                        requestAnimationFrame(animate);
                    });
                }
             }"
             x-init="
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => { if (entry.isIntersecting) animateCounters(); });
                }, { threshold: 0.3 });
                observer.observe($el);
             ">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-12">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <template x-for="(stat, index) in stats" :key="index">
                    <div class="text-center">
                        <div class="text-3xl sm:text-4xl font-bold text-white tabular-nums">
                            <span x-text="stat.decimal ? stat.current.toFixed(1) : stat.current.toLocaleString()"></span><span class="text-emerald-400" x-text="stat.suffix"></span>
                        </div>
                        <div class="text-sm text-zinc-500 mt-1" x-text="stat.label"></div>
                    </div>
                </template>
            </div>
        </div>
    </section>

    {{-- ===== FEATURES GRID ===== --}}
    <section id="features" class="relative z-10 py-24 lg:py-32">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            {{-- Section Header --}}
            <div class="text-center max-w-2xl mx-auto mb-16"
                 x-data="{ shown: false }"
                 x-init="const o = new IntersectionObserver(e => { e.forEach(en => { if(en.isIntersecting) { shown = true; } }); }, { threshold: 0.2 }); o.observe($el);"
                 :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
                 style="transition: all 0.7s ease-out;">
                <p class="text-sm font-semibold text-emerald-400 uppercase tracking-wider mb-3">Everything you need</p>
                <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">Built for modern facility teams</h2>
                <p class="text-zinc-400 text-lg">A unified command center for every aspect of facility operations, from IoT sensors to vendor compliance.</p>
            </div>

            {{-- Feature Cards --}}
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
                {{-- Card 1: IoT Sensor Monitoring --}}
                <div class="card-glow bg-zinc-900/60 border border-zinc-800 rounded-2xl p-6"
                     x-data="{ shown: false }"
                     x-init="const o = new IntersectionObserver(e => { e.forEach(en => { if(en.isIntersecting) shown = true; }); }, { threshold: 0.15 }); o.observe($el);"
                     :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
                     style="transition: all 0.6s ease-out; transition-delay: 0ms;">
                    <div class="w-11 h-11 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.348 14.651a3.75 3.75 0 010-5.303m5.304 0a3.75 3.75 0 010 5.303m-7.425 2.122a6.75 6.75 0 010-9.546m9.546 0a6.75 6.75 0 010 9.546M5.106 18.894c-3.808-3.808-3.808-9.98 0-13.788m13.788 0c3.808 3.808 3.808 9.98 0 13.788M12 12h.008v.008H12V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">IoT Sensor Monitoring</h3>
                    <p class="text-sm text-zinc-400 leading-relaxed">Connect thousands of IoT sensors across your portfolio. Monitor temperature, humidity, air quality, and energy consumption in real time with instant threshold alerts.</p>
                </div>

                {{-- Card 2: Predictive Health Scoring --}}
                <div class="card-glow bg-zinc-900/60 border border-zinc-800 rounded-2xl p-6"
                     x-data="{ shown: false }"
                     x-init="const o = new IntersectionObserver(e => { e.forEach(en => { if(en.isIntersecting) shown = true; }); }, { threshold: 0.15 }); o.observe($el);"
                     :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
                     style="transition: all 0.6s ease-out; transition-delay: 100ms;">
                    <div class="w-11 h-11 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Predictive Health Scoring</h3>
                    <p class="text-sm text-zinc-400 leading-relaxed">AI-powered health scores predict equipment failures before they happen. Reduce unplanned downtime by up to 60% with data-driven maintenance scheduling.</p>
                </div>

                {{-- Card 3: Smart Automation --}}
                <div class="card-glow bg-zinc-900/60 border border-zinc-800 rounded-2xl p-6"
                     x-data="{ shown: false }"
                     x-init="const o = new IntersectionObserver(e => { e.forEach(en => { if(en.isIntersecting) shown = true; }); }, { threshold: 0.15 }); o.observe($el);"
                     :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
                     style="transition: all 0.6s ease-out; transition-delay: 200ms;">
                    <div class="w-11 h-11 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Smart Automation</h3>
                    <p class="text-sm text-zinc-400 leading-relaxed">Build powerful if-this-then-that automation rules with a visual builder. Auto-create work orders, send alerts, and trigger workflows based on sensor data or schedules.</p>
                </div>

                {{-- Card 4: Interactive Floor Plans --}}
                <div class="card-glow bg-zinc-900/60 border border-zinc-800 rounded-2xl p-6"
                     x-data="{ shown: false }"
                     x-init="const o = new IntersectionObserver(e => { e.forEach(en => { if(en.isIntersecting) shown = true; }); }, { threshold: 0.15 }); o.observe($el);"
                     :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
                     style="transition: all 0.6s ease-out; transition-delay: 0ms;">
                    <div class="w-11 h-11 rounded-xl bg-violet-500/10 border border-violet-500/20 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-violet-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Interactive Floor Plans</h3>
                    <p class="text-sm text-zinc-400 leading-relaxed">Navigate your facilities with interactive 2D floor plans. Drop pins for assets, sensors, and work orders. See real-time occupancy and environmental data at a glance.</p>
                </div>

                {{-- Card 5: Vendor Management --}}
                <div class="card-glow bg-zinc-900/60 border border-zinc-800 rounded-2xl p-6"
                     x-data="{ shown: false }"
                     x-init="const o = new IntersectionObserver(e => { e.forEach(en => { if(en.isIntersecting) shown = true; }); }, { threshold: 0.15 }); o.observe($el);"
                     :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
                     style="transition: all 0.6s ease-out; transition-delay: 100ms;">
                    <div class="w-11 h-11 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Vendor Management</h3>
                    <p class="text-sm text-zinc-400 leading-relaxed">Track vendor contracts, insurance compliance, and performance ratings. Streamline procurement with a centralized vendor portal and automated renewal reminders.</p>
                </div>

                {{-- Card 6: Real-time Analytics --}}
                <div class="card-glow bg-zinc-900/60 border border-zinc-800 rounded-2xl p-6"
                     x-data="{ shown: false }"
                     x-init="const o = new IntersectionObserver(e => { e.forEach(en => { if(en.isIntersecting) shown = true; }); }, { threshold: 0.15 }); o.observe($el);"
                     :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
                     style="transition: all 0.6s ease-out; transition-delay: 200ms;">
                    <div class="w-11 h-11 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-rose-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Real-time Analytics</h3>
                    <p class="text-sm text-zinc-400 leading-relaxed">Powerful dashboards with drill-down analytics. Track KPIs across your portfolio, generate compliance reports, and export data with one click for stakeholder presentations.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== TRUSTED BY ===== --}}
    <section class="relative z-10 py-16 border-y border-white/5 bg-zinc-900/30">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <p class="text-center text-xs font-semibold text-zinc-600 uppercase tracking-widest mb-10">Trusted by industry leaders worldwide</p>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-8 items-center">
                <div class="text-center">
                    <span class="text-lg font-bold text-zinc-700">Meridian Corp</span>
                </div>
                <div class="text-center">
                    <span class="text-lg font-bold text-zinc-700">Atlas Energy</span>
                </div>
                <div class="text-center">
                    <span class="text-lg font-bold text-zinc-700">Vertex Labs</span>
                </div>
                <div class="text-center">
                    <span class="text-lg font-bold text-zinc-700">Cascade Systems</span>
                </div>
                <div class="text-center">
                    <span class="text-lg font-bold text-zinc-700">Pinnacle Health</span>
                </div>
                <div class="text-center">
                    <span class="text-lg font-bold text-zinc-700">Orion Dynamics</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== CTA SECTION ===== --}}
    <section id="pricing" class="relative z-10 py-24 lg:py-32">
        <div class="max-w-3xl mx-auto px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">Ready to transform your operations?</h2>
            <p class="text-lg text-zinc-400 mb-10">Start your 14-day free trial. No credit card required. Full access to all features.</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('login') }}" class="glow-btn relative inline-flex items-center gap-2 px-8 py-4 text-sm font-semibold text-white bg-gradient-to-r from-emerald-600 via-emerald-500 to-teal-500 rounded-xl transition-all shadow-xl shadow-emerald-500/20 hover:shadow-emerald-500/30 hover:scale-[1.02]">
                    Start Free Trial
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </a>
                <a href="#" class="inline-flex items-center gap-2 px-8 py-4 text-sm font-semibold text-zinc-300 border border-zinc-700 hover:border-zinc-500 rounded-xl transition-all hover:bg-white/5">
                    Contact Sales
                </a>
            </div>
        </div>
    </section>

    {{-- ===== FOOTER ===== --}}
    <footer class="relative z-10 border-t border-white/5 bg-zinc-950/80">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-12">
            <div class="grid md:grid-cols-4 gap-10">
                {{-- Brand column --}}
                <div class="md:col-span-1">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-7 h-7 bg-gradient-to-br from-emerald-500 to-cyan-500 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 0h.008v.008h-.008V7.5z" />
                            </svg>
                        </div>
                        <span class="text-base font-bold text-white">Nexus<span class="text-emerald-400">Ops</span></span>
                    </div>
                    <p class="text-sm text-zinc-500 leading-relaxed">Intelligent facility operations platform for the modern enterprise.</p>
                </div>

                {{-- Links --}}
                <div>
                    <h4 class="text-sm font-semibold text-zinc-300 mb-4">Product</h4>
                    <ul class="space-y-2.5">
                        <li><a href="#features" class="text-sm text-zinc-500 hover:text-zinc-300 transition-colors">Features</a></li>
                        <li><a href="#pricing" class="text-sm text-zinc-500 hover:text-zinc-300 transition-colors">Pricing</a></li>
                        <li><a href="#" class="text-sm text-zinc-500 hover:text-zinc-300 transition-colors">Integrations</a></li>
                        <li><a href="#" class="text-sm text-zinc-500 hover:text-zinc-300 transition-colors">Changelog</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-zinc-300 mb-4">Company</h4>
                    <ul class="space-y-2.5">
                        <li><a href="#" class="text-sm text-zinc-500 hover:text-zinc-300 transition-colors">About</a></li>
                        <li><a href="#" class="text-sm text-zinc-500 hover:text-zinc-300 transition-colors">Blog</a></li>
                        <li><a href="#" class="text-sm text-zinc-500 hover:text-zinc-300 transition-colors">Careers</a></li>
                        <li><a href="#" class="text-sm text-zinc-500 hover:text-zinc-300 transition-colors">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-zinc-300 mb-4">Legal</h4>
                    <ul class="space-y-2.5">
                        <li><a href="#" class="text-sm text-zinc-500 hover:text-zinc-300 transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="text-sm text-zinc-500 hover:text-zinc-300 transition-colors">Terms of Service</a></li>
                        <li><a href="#" class="text-sm text-zinc-500 hover:text-zinc-300 transition-colors">Security</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 pt-8 border-t border-zinc-800/50 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-sm text-zinc-600">&copy; 2026 NexusOps. All rights reserved.</p>
                <div class="flex items-center gap-4">
                    {{-- Twitter/X --}}
                    <a href="#" class="text-zinc-600 hover:text-zinc-400 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    {{-- LinkedIn --}}
                    <a href="#" class="text-zinc-600 hover:text-zinc-400 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                    </a>
                    {{-- GitHub --}}
                    <a href="#" class="text-zinc-600 hover:text-zinc-400 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
