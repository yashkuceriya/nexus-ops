<div wire:poll.10s>
    {{-- Live Data Ticker --}}
    @livewire('data-ticker')

    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Portfolio Readiness Dashboard</h1>
                <p class="mt-1 text-sm text-gray-500">Operational readiness across all active projects</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                    Live
                </span>
            </div>
        </div>
    </div>

    {{-- KPI Cards Skeleton (shown during loading) --}}
    <div wire:loading class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <x-skeleton-kpi-card />
        <x-skeleton-kpi-card />
        <x-skeleton-kpi-card />
        <x-skeleton-kpi-card />
    </div>

    {{-- KPI Cards --}}
    <div wire:loading.remove class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">

        {{-- Open Work Orders --}}
        <div class="overflow-hidden rounded-xl bg-white shadow-sm border border-gray-100">
            <div class="p-5">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 w-11 h-11 rounded-full bg-amber-100 flex items-center justify-center">
                        <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Open Work Orders</p>
                        <div class="flex items-center gap-3 mt-0.5">
                            <p class="text-3xl font-bold text-gray-900"
                               x-data="{ value: 0, target: {{ $this->kpis['open_work_orders'] }} }"
                               x-init="let start = performance.now();
                                       function animate(now) {
                                           let progress = Math.min((now - start) / 1000, 1);
                                           value = Math.round(target * progress);
                                           if (progress < 1) requestAnimationFrame(animate);
                                       }
                                       requestAnimationFrame(animate)"
                               x-text="value"
                               style="font-variant-numeric: tabular-nums">
                            </p>
                            <svg width="60" height="24" viewBox="0 0 60 24" class="flex-shrink-0">
                                <polyline points="0,18 10,14 20,20 30,10 40,6 50,8 60,4" fill="none" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-amber-50 px-5 py-2.5 border-t border-amber-100">
                <div class="flex items-center gap-1.5">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    <span class="text-xs font-medium text-amber-700">{{ $this->kpis['sla_breached'] }} SLA breached</span>
                </div>
            </div>
        </div>

        {{-- Avg. MTTR --}}
        <div class="overflow-hidden rounded-xl bg-white shadow-sm border border-gray-100">
            <div class="p-5">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 w-11 h-11 rounded-full bg-blue-100 flex items-center justify-center">
                        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Avg. MTTR</p>
                        <div class="flex items-center gap-3 mt-0.5">
                            <p class="text-3xl font-bold text-gray-900">
                                <span x-data="{ value: 0, target: {{ $this->kpis['mttr_hours'] }} }"
                                      x-init="let start = performance.now();
                                              function animate(now) {
                                                  let progress = Math.min((now - start) / 1000, 1);
                                                  value = Math.round(target * progress * 10) / 10;
                                                  if (progress < 1) requestAnimationFrame(animate);
                                              }
                                              requestAnimationFrame(animate)"
                                      x-text="value"
                                      style="font-variant-numeric: tabular-nums">
                                </span>
                                <span class="text-lg font-semibold text-gray-400"> hrs</span>
                            </p>
                            <svg width="60" height="24" viewBox="0 0 60 24" class="flex-shrink-0">
                                <polyline points="0,12 10,16 20,10 30,14 40,8 50,12 60,6" fill="none" stroke="#3b82f6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-blue-50 px-5 py-2.5 border-t border-blue-100">
                <div class="flex items-center gap-1.5">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    <span class="text-xs font-medium text-blue-700">Mean Time to Repair</span>
                </div>
            </div>
        </div>

        {{-- PM Compliance --}}
        <div class="overflow-hidden rounded-xl bg-white shadow-sm border border-gray-100">
            <div class="p-5">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 w-11 h-11 rounded-full bg-emerald-100 flex items-center justify-center">
                        <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">PM Compliance</p>
                        <div class="flex items-center gap-3 mt-0.5">
                            <p class="text-3xl font-bold text-gray-900">
                                <span x-data="{ value: 0, target: {{ $this->kpis['pm_compliance'] }} }"
                                      x-init="let start = performance.now();
                                              function animate(now) {
                                                  let progress = Math.min((now - start) / 1000, 1);
                                                  value = Math.round(target * progress);
                                                  if (progress < 1) requestAnimationFrame(animate);
                                              }
                                              requestAnimationFrame(animate)"
                                      x-text="value"
                                      style="font-variant-numeric: tabular-nums">
                                </span><span class="text-lg font-semibold text-gray-400">%</span>
                            </p>
                            <svg width="60" height="24" viewBox="0 0 60 24" class="flex-shrink-0">
                                <polyline points="0,16 10,12 20,14 30,8 40,6 50,10 60,4" fill="none" stroke="#10b981" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-emerald-50 px-5 py-2.5 border-t border-emerald-100">
                <div class="flex items-center gap-1.5">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span class="text-xs font-medium text-emerald-700">Preventive maintenance on schedule</span>
                </div>
            </div>
        </div>

        {{-- Active IoT Sensors --}}
        @php
            $hasAnomalies = $this->kpis['anomaly_sensors'] > 0;
            $sensorColor = $hasAnomalies ? 'red' : 'emerald';
        @endphp
        <div class="overflow-hidden rounded-xl bg-white shadow-sm border border-gray-100">
            <div class="p-5">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 w-11 h-11 rounded-full bg-{{ $sensorColor }}-100 flex items-center justify-center">
                        <svg class="h-5 w-5 text-{{ $sensorColor }}-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.348 14.651a3.75 3.75 0 010-5.303m5.304 0a3.75 3.75 0 010 5.303m-7.425 2.122a6.75 6.75 0 010-9.546m9.546 0a6.75 6.75 0 010 9.546M5.106 18.894c-3.808-3.808-3.808-9.98 0-13.788m13.788 0c3.808 3.808 3.808 9.98 0 13.788" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Active IoT Sensors</p>
                        <div class="flex items-center gap-3 mt-0.5">
                            <p class="text-3xl font-bold text-gray-900"
                               x-data="{ value: 0, target: {{ $this->kpis['active_sensors'] }} }"
                               x-init="let start = performance.now();
                                       function animate(now) {
                                           let progress = Math.min((now - start) / 1000, 1);
                                           value = Math.round(target * progress);
                                           if (progress < 1) requestAnimationFrame(animate);
                                       }
                                       requestAnimationFrame(animate)"
                               x-text="value"
                               style="font-variant-numeric: tabular-nums">
                            </p>
                            <svg width="60" height="24" viewBox="0 0 60 24" class="flex-shrink-0">
                                <polyline points="0,20 10,16 20,18 30,12 40,14 50,8 60,6" fill="none" stroke="{{ $hasAnomalies ? '#ef4444' : '#10b981' }}" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-{{ $sensorColor }}-50 px-5 py-2.5 border-t border-{{ $sensorColor }}-100">
                <div class="flex items-center gap-1.5">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-{{ $sensorColor }}-500"></span>
                    <span class="text-xs font-medium text-{{ $sensorColor }}-700">{{ $this->kpis['anomaly_sensors'] }} sensors in alert (last hour)</span>
                </div>
            </div>
        </div>
    </div>

    {{-- AI Insights Panel --}}
    @livewire('ai-insights-panel')

    {{-- Project Readiness Overview --}}
    <div class="mb-8">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-semibold text-gray-900">Project Readiness Overview</h2>
            <a href="{{ route('projects.index') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 transition-colors">View All Projects &rarr;</a>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            @foreach($this->projects->take(3) as $project)
            @php
                $score = $project->readiness_score;
                $scoreColor = $score >= 80 ? 'emerald' : ($score >= 50 ? 'amber' : 'red');
                $circumference = 2 * 3.14159 * 54;
                $dashOffset = $circumference - ($score / 100) * $circumference;

                $typeColors = [
                    'data_infrastructure' => ['bg-emerald-100', 'text-emerald-700', 'border-emerald-200'],
                    'data infrastructure' => ['bg-emerald-100', 'text-emerald-700', 'border-emerald-200'],
                    'commissioning' => ['bg-blue-100', 'text-blue-700', 'border-blue-200'],
                    'closeout' => ['bg-amber-100', 'text-amber-700', 'border-amber-200'],
                    'operational' => ['bg-purple-100', 'text-purple-700', 'border-purple-200'],
                ];
                $typeKey = strtolower($project->project_type ?? 'general');
                $badgeClasses = $typeColors[$typeKey] ?? ['bg-gray-100', 'text-gray-700', 'border-gray-200'];
            @endphp
            <div class="overflow-hidden rounded-xl bg-white shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                <div class="p-6">
                    {{-- Status Badge --}}
                    <div class="mb-4">
                        <span class="inline-flex items-center rounded-md px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $badgeClasses[0] }} {{ $badgeClasses[1] }} border {{ $badgeClasses[2] }}">
                            {{ strtoupper(str_replace('_', ' ', $project->project_type ?? 'General')) }}
                        </span>
                    </div>

                    {{-- Project Name & Location --}}
                    <h3 class="text-base font-semibold text-gray-900">{{ $project->name }}</h3>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $project->city }}, {{ $project->state }}</p>

                    {{-- Readiness Ring --}}
                    <div class="flex items-center justify-center my-6">
                        <div class="relative w-32 h-32">
                            <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 120 120">
                                <circle cx="60" cy="60" r="54" fill="none" stroke="#f3f4f6" stroke-width="8" />
                                <circle cx="60" cy="60" r="54"
                                    fill="none"
                                    stroke="{{ $scoreColor === 'emerald' ? '#10b981' : ($scoreColor === 'amber' ? '#f59e0b' : '#ef4444') }}"
                                    stroke-width="8"
                                    stroke-linecap="round"
                                    stroke-dasharray="{{ $circumference }}"
                                    stroke-dashoffset="{{ $dashOffset }}" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-3xl font-bold text-{{ $scoreColor }}-600">{{ number_format($score, 0) }}%</span>
                                <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Ready</span>
                            </div>
                        </div>
                    </div>

                    {{-- Mini Stat Boxes --}}
                    <div class="grid grid-cols-3 gap-3">
                        <div class="text-center p-2.5 bg-gray-50 rounded-lg border border-gray-100">
                            <p class="text-lg font-bold {{ $project->open_issues > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $project->open_issues }}</p>
                            <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wide mt-0.5">Issues</p>
                        </div>
                        <div class="text-center p-2.5 bg-gray-50 rounded-lg border border-gray-100">
                            <p class="text-lg font-bold text-gray-900">{{ $project->completed_tests }}<span class="text-gray-400 font-normal">/{{ $project->total_tests }}</span></p>
                            <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wide mt-0.5">Tests</p>
                        </div>
                        <div class="text-center p-2.5 bg-gray-50 rounded-lg border border-gray-100">
                            <p class="text-lg font-bold text-gray-900">{{ $project->completed_closeout_docs }}<span class="text-gray-400 font-normal">/{{ $project->total_closeout_docs }}</span></p>
                            <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wide mt-0.5">Docs</p>
                        </div>
                    </div>
                </div>

                {{-- Card Footer --}}
                <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        @if($project->open_issues > 5)
                            <span class="inline-flex items-center gap-1 rounded-md bg-red-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-red-700 border border-red-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                Critical
                            </span>
                        @endif
                        @if($project->target_handover_date)
                            <span class="text-xs text-gray-500">
                                {{ $project->target_handover_date->format('M d, Y') }}
                            </span>
                        @endif
                    </div>
                    <a href="{{ route('projects.show', $project) }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition-colors">
                        View Details &rarr;
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Recent Work Orders + Live Activity --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-semibold text-gray-900">Recent Work Orders</h2>
            <a href="{{ route('work-orders.index') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 transition-colors">View All &rarr;</a>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm border border-gray-100">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-3.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">WO #</th>
                        <th class="px-6 py-3.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Priority</th>
                        <th class="px-6 py-3.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Assigned To</th>
                        <th class="px-6 py-3.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Project</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach($this->recentWorkOrders as $wo)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-mono font-medium text-gray-900">{{ $wo->wo_number }}</span>
                            @if($wo->isSlaBreached())
                                <span class="ml-1.5 inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-bold bg-red-100 text-red-700">SLA</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate">{{ $wo->title }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $priorityStyles = match($wo->priority) {
                                    'emergency' => 'bg-red-100 text-red-700 border-red-200',
                                    'critical' => 'bg-orange-100 text-orange-700 border-orange-200',
                                    'high' => 'bg-amber-100 text-amber-700 border-amber-200',
                                    'medium' => 'bg-blue-100 text-blue-700 border-blue-200',
                                    'low' => 'bg-gray-100 text-gray-600 border-gray-200',
                                    default => 'bg-gray-100 text-gray-600 border-gray-200',
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold border {{ $priorityStyles }}">
                                {{ ucfirst($wo->priority) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusStyles = match($wo->status) {
                                    'pending' => 'bg-gray-100 text-gray-700 border-gray-200',
                                    'assigned' => 'bg-blue-100 text-blue-700 border-blue-200',
                                    'in_progress' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                    'on_hold' => 'bg-purple-100 text-purple-700 border-purple-200',
                                    'completed' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                    'verified' => 'bg-green-100 text-green-700 border-green-200',
                                    'cancelled' => 'bg-red-100 text-red-700 border-red-200',
                                    default => 'bg-gray-100 text-gray-700 border-gray-200',
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold border {{ $statusStyles }}">
                                {{ str_replace('_', ' ', ucfirst($wo->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                @if($wo->assignee)
                                    <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center text-[10px] font-bold text-emerald-700 flex-shrink-0">
                                        {{ substr($wo->assignee->name, 0, 1) }}
                                    </div>
                                    <span class="text-sm text-gray-700">{{ $wo->assignee->name }}</span>
                                @else
                                    <span class="text-sm text-gray-400 italic">Unassigned</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 truncate max-w-[160px]">{{ $wo->project?->name ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Live Activity Feed (right column) --}}
    <div class="lg:col-span-1">
        @livewire('live-activity-feed')
    </div>
    </div>
</div>
