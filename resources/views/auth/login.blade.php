<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome back — NexusOps</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter','ui-sans-serif','system-ui','sans-serif'], mono:['JetBrains Mono','ui-monospace','monospace'] },
                    colors: {
                        ink: { DEFAULT:'#0F172A', muted:'#475569', soft:'#94A3B8' },
                        accent: { 50:'#EEF2FF', 100:'#E0E7FF', 600:'#4F46E5', 700:'#4338CA', 800:'#3730A3' },
                    },
                }
            }
        }
    </script>
    <style>
        html,body { font-family:'Inter',ui-sans-serif,system-ui,sans-serif; -webkit-font-smoothing:antialiased; }
        body { background:#F5F4F9; color:#0F172A; }
        .mono { font-family:'JetBrains Mono', ui-monospace, monospace; }
        .btn-primary { background:#4F46E5; color:#fff; padding:10px 16px; border-radius:10px; font-weight:600; font-size:13px; transition:background 120ms ease; }
        .btn-primary:hover { background:#4338CA; }
        .btn-ghost { background:#fff; color:#0F172A; padding:10px 14px; border-radius:10px; font-weight:600; font-size:12px; border:1px solid #E5E7EB; }
        .btn-ghost:hover { background:#F8FAFC; }
        .hairline { border:1px solid #E5E7EB; }
        .input {
            display:block; width:100%; border-radius:10px; border:1px solid #E5E7EB; background:#fff;
            padding:10px 14px; font-size:13px; color:#0F172A; transition: border-color 120ms, box-shadow 120ms;
        }
        .input:focus { outline:none; border-color:#6366F1; box-shadow: 0 0 0 3px rgba(99,102,241,.15); }
        .label { font-size:10px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:#475569; }
        .right-panel {
            background:
                radial-gradient(1200px 600px at 80% 20%, rgba(99,102,241,.12), transparent 60%),
                radial-gradient(800px 400px at 20% 80%, rgba(79,70,229,.12), transparent 60%),
                linear-gradient(180deg, #F5F4F9 0%, #EEF2FF 100%);
        }
        .grid-texture {
            background-image:
                linear-gradient(rgba(79,70,229,.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(79,70,229,.06) 1px, transparent 1px);
            background-size: 32px 32px;
        }
    </style>
</head>
<body class="h-full">
<div class="min-h-full grid grid-cols-1 lg:grid-cols-2">
    {{-- Left: form --}}
    <div class="flex flex-col justify-between px-8 py-10 lg:px-16 lg:py-16 bg-white">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-accent-600 flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25M3 6.695c0-.836.88-1.38 1.628-1.006L9.503 8.311c.317.158.69.158 1.006 0l4.994-2.497a1.125 1.125 0 011.006 0l4.875 2.437c.381.19.622.58.622 1.006V19.18c0 .836-.88 1.38-1.628 1.006l-3.869-1.934a1.125 1.125 0 00-1.006 0l-4.994 2.497c-.317.158-.69.158-1.006 0l-4.875-2.437A1.125 1.125 0 013 17.305V6.695z"/></svg>
            </div>
            <div class="text-[15px] font-bold tracking-tight text-ink">NexusOps</div>
        </div>

        <div class="max-w-sm w-full mx-auto">
            <h1 class="text-3xl font-bold tracking-tight text-ink">Welcome back</h1>
            <p class="text-[13px] text-ink-muted mt-2">Enter your credentials to access the Facility OS.</p>

            <div class="mt-5 rounded-lg bg-accent-50 border border-accent-200/70 px-4 py-3 flex items-start gap-3" x-data="{ copied: false }">
                <div class="mt-0.5 text-accent-700">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="flex-1">
                    <div class="text-[11px] font-bold tracking-widest text-accent-800 uppercase">Demo Credentials</div>
                    <div class="mt-1 mono text-[12px] text-ink">admin@acme.com&nbsp;&nbsp;/&nbsp;&nbsp;password</div>
                </div>
                <button type="button"
                        @click="navigator.clipboard.writeText('admin@acme.com'); copied = true; setTimeout(() => copied = false, 1500)"
                        class="text-[11px] font-semibold text-accent-700 hover:text-accent-800 self-center"
                        x-text="copied ? 'Copied!' : 'Copy email'"></button>
            </div>

            @if($errors->any())
                <div class="mt-6 rounded-lg bg-red-50 border border-red-200 p-3">
                    <p class="text-[13px] text-red-700">{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                @csrf
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="email" class="label">Email Address</label>
                        <span class="mono text-[10px] text-ink-soft">SYS_AUTH_000</span>
                    </div>
                    <input id="email" name="email" type="email" required autofocus value="{{ old('email', 'admin@acme.com') }}" placeholder="name@nexusops.com" class="input">
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="label">Password</label>
                        <button type="button"
                                onclick="alert('Password reset is disabled in the demo tenant. Use admin@acme.com / password to sign in.')"
                                class="text-[11px] font-semibold text-accent-700 hover:text-accent-800">Forgot Password?</button>
                    </div>
                    <input id="password" name="password" type="password" required value="{{ old('password', 'password') }}" placeholder="••••••••" class="input">
                </div>
                <label class="flex items-center gap-2 text-[12px] text-ink-muted">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-accent-600 focus:ring-accent-500">
                    Stay authenticated for 30 days
                </label>
                <button type="submit" class="btn-primary w-full inline-flex items-center justify-center gap-2">
                    Access Control Center
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </button>

                <div class="relative py-2">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
                    <div class="relative flex justify-center"><span class="bg-white px-3 text-[10px] font-bold tracking-widest text-ink-soft uppercase">External Identity Providers</span></div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button"
                            title="SSO providers aren't configured in the demo tenant — use admin@acme.com / password to sign in."
                            onclick="alert('SSO is disabled in the demo tenant. In production, NexusOps supports Google Workspace and Azure AD via Laravel Socialite. Use admin@acme.com / password for the demo.')"
                            class="btn-ghost inline-flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                        Google SSO
                    </button>
                    <button type="button"
                            title="SSO providers aren't configured in the demo tenant — use admin@acme.com / password to sign in."
                            onclick="alert('SSO is disabled in the demo tenant. In production, NexusOps supports Google Workspace and Azure AD via Laravel Socialite. Use admin@acme.com / password for the demo.')"
                            class="btn-ghost inline-flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="#0078D4"><path d="M11.4 24H0V12.6h11.4V24zM24 24H12.6V12.6H24V24zM11.4 11.4H0V0h11.4v11.4zM24 11.4H12.6V0H24v11.4z"/></svg>
                        Azure AD
                    </button>
                </div>
            </form>
        </div>

        <div class="flex items-center justify-between text-[10px] mono text-ink-soft">
            <span>© {{ date('Y') }} NEXUSOPS SYSTEMS</span>
            <span>SECURE_ENCRYPTION_ACTIVE</span>
        </div>
    </div>

    {{-- Right: brand panel --}}
    <div class="hidden lg:flex right-panel relative overflow-hidden">
        <div class="absolute inset-0 grid-texture opacity-60"></div>
        <div class="absolute top-8 right-8 mono text-[10px] text-ink-soft leading-relaxed text-right">
            SYSTEM STATUS: READY<br>
            PROTOCOL: NEXUS_CORE_V1_5<br>
            LOCATION: GLOBAL_EDGE_NODE
        </div>
        <div class="absolute bottom-8 right-8 mono text-[10px] text-ink-soft text-right">
            LAT: 37.7749° N<br>
            LONG: 122.4194° W<br>
            NODE_SECURED
        </div>
        <div class="m-auto relative z-10 text-center px-10">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/80 backdrop-blur border border-accent-100 px-3 py-1 mb-5">
                <span class="w-1.5 h-1.5 rounded-full bg-accent-600 animate-pulse"></span>
                <span class="text-[10px] font-bold tracking-widest uppercase text-accent-800">Operational Efficiency Elevated</span>
            </div>
            <h2 class="text-5xl font-bold tracking-tight text-ink">NexusOps</h2>
            <p class="text-[14px] text-ink-muted mt-3 max-w-sm mx-auto">Precision Commissioning for <span class="text-accent-700 font-semibold">High-Stakes Facilities</span></p>
            <div class="grid grid-cols-3 gap-6 mt-10 max-w-md mx-auto">
                <div>
                    <div class="text-2xl font-bold tabular-nums text-accent-700">99.9%</div>
                    <div class="mono text-[9px] text-ink-soft tracking-widest uppercase mt-1">Uptime Assurance</div>
                </div>
                <div>
                    <div class="text-2xl font-bold tabular-nums text-accent-700">2.4<span class="text-base">ms</span></div>
                    <div class="mono text-[9px] text-ink-soft tracking-widest uppercase mt-1">Data Latency</div>
                </div>
                <div>
                    <div class="text-2xl font-bold tabular-nums text-accent-700">0.02</div>
                    <div class="mono text-[9px] text-ink-soft tracking-widest uppercase mt-1">Error Margin</div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
