<div class="min-h-screen bg-slate-50" wire:poll.5s="refreshReadings">
    {{-- Page Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-1">
            <div class="w-2 h-8 bg-emerald-500 rounded-full"></div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">IoT Sensor Dashboard</h1>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 border border-emerald-200 ml-auto">
                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                Live &middot; 5s
            </span>
        </div>
        <p class="text-sm text-slate-500 ml-5 pl-0.5">Real-time sensor monitoring and anomaly detection &mdash; NexusOps Platform</p>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
        {{-- Total Sensors --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-emerald-50 rounded-bl-[3rem] -mr-2 -mt-2"></div>
            <div class="relative">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.348 14.651a3.75 3.75 0 010-5.303m5.304 0a3.75 3.75 0 010 5.303m-7.425 2.122a6.75 6.75 0 010-9.546m9.546 0a6.75 6.75 0 010 9.546M5.106 18.894c-3.808-3.808-3.808-9.98 0-13.789m13.788 0c3.808 3.808 3.808 9.981 0 13.79"/>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Sensors</span>
                </div>
                <div class="text-3xl font-bold text-slate-900 tracking-tight">{{ number_format($this->sensors->count()) }}</div>
                @php
                    $activePct = $this->sensors->count() > 0
                        ? round($this->sensors->where('is_active', true)->count() / $this->sensors->count() * 100, 1)
                        : 0;
                @endphp
                <p class="text-xs text-emerald-600 font-medium mt-1">{{ $activePct }}% Operational</p>
            </div>
        </div>

        {{-- Active Alerts --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-5 relative overflow-hidden">
            @if($this->anomalyCount > 0)
            <div class="absolute top-0 right-0 w-20 h-20 bg-red-50 rounded-bl-[3rem] -mr-2 -mt-2"></div>
            @endif
            <div class="relative">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 {{ $this->anomalyCount > 0 ? 'bg-red-100' : 'bg-slate-100' }} rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 {{ $this->anomalyCount > 0 ? 'text-red-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Alerts</span>
                </div>
                <div class="text-3xl font-bold {{ $this->anomalyCount > 0 ? 'text-red-600' : 'text-slate-900' }} tracking-tight">{{ $this->anomalyCount }}</div>
                <p class="text-xs {{ $this->anomalyCount > 0 ? 'text-red-500' : 'text-slate-400' }} font-medium mt-1">{{ $this->anomalyCount > 0 ? 'Anomalies detected (24h)' : 'No anomalies (24h)' }}</p>
            </div>
        </div>

        {{-- Sensor Types --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-blue-50 rounded-bl-[3rem] -mr-2 -mt-2"></div>
            <div class="relative">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Sensor Types</span>
                </div>
                <div class="text-3xl font-bold text-slate-900 tracking-tight">{{ $this->sensors->pluck('sensor_type')->unique()->count() }}</div>
                <p class="text-xs text-blue-600 font-medium mt-1">Unique classifications</p>
            </div>
        </div>
    </div>

    {{-- Main Two-Panel Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {{-- LEFT: Sensor Directory --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Sensor Directory</h3>
                    <p class="text-xs text-slate-400 mt-0.5">All Sensors &middot; {{ $this->sensors->count() }} total</p>
                </div>
                <div class="divide-y divide-slate-100 max-h-[640px] overflow-y-auto">
                    @foreach($this->sensors as $sensor)
                    @php
                        $isSelected = $this->selectedSensorId === $sensor->id;
                        $isAlert = $sensor->isValueOutOfRange((float)$sensor->last_value);
                    @endphp
                    <button wire:click="selectSensor({{ $sensor->id }})"
                        class="w-full text-left px-4 py-3.5 transition-all duration-150 hover:bg-slate-50
                        {{ $isSelected ? 'bg-emerald-50/70 border-l-[3px] border-emerald-500' : 'border-l-[3px] border-transparent' }}">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span class="text-sm font-semibold text-slate-900 truncate">{{ $sensor->name }}</span>
                                    @if($isAlert)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700 uppercase tracking-wide flex-shrink-0">Alert</span>
                                    @elseif($sensor->is_active)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 uppercase tracking-wide flex-shrink-0">Online</span>
                                    @else
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 uppercase tracking-wide flex-shrink-0">Offline</span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-400">{{ ucfirst($sensor->sensor_type) }} &middot; {{ $sensor->unit }}</div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <div class="text-base font-mono font-bold {{ $isAlert ? 'text-red-600' : 'text-slate-800' }}">
                                    {{ $sensor->last_value }}{{ $sensor->unit }}
                                </div>
                            </div>
                        </div>
                        @if($sensor->threshold_min !== null || $sensor->threshold_max !== null)
                        <div class="mt-2">
                            <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                @php
                                    $min = (float)($sensor->threshold_min ?? 0);
                                    $max = (float)($sensor->threshold_max ?? 100);
                                    $range = $max - $min ?: 1;
                                    $pct = max(0, min(100, (((float)$sensor->last_value - $min) / $range) * 100));
                                @endphp
                                <div class="h-1.5 rounded-full transition-all duration-300 {{ $isAlert ? 'bg-red-500' : 'bg-emerald-500' }}"
                                    style="width: {{ $pct }}%"></div>
                            </div>
                            <div class="flex justify-between text-[10px] text-slate-400 mt-1 font-mono">
                                <span>{{ $sensor->threshold_min ?? '---' }}</span>
                                <span>{{ $sensor->threshold_max ?? '---' }}</span>
                            </div>
                        </div>
                        @endif
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- RIGHT: Selected Sensor Detail --}}
        <div class="lg:col-span-3">
            @if($this->selectedSensor)
            @php
                $sensorAlert = $this->selectedSensor->isValueOutOfRange((float)$this->selectedSensor->last_value);
            @endphp
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
                {{-- Sensor Header --}}
                <div class="px-6 py-5 border-b border-slate-100 bg-gradient-to-r {{ $sensorAlert ? 'from-red-50/50 to-white' : 'from-emerald-50/30 to-white' }}">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-3 mb-1">
                                <h3 class="text-lg font-bold text-slate-900">{{ $this->selectedSensor->name }}</h3>
                                @if($sensorAlert)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 uppercase tracking-wide">
                                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse"></span>
                                        Alert
                                    </span>
                                @elseif($this->selectedSensor->is_active)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 uppercase tracking-wide">
                                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                        Online
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500 uppercase tracking-wide">
                                        Offline
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm text-slate-500">
                                {{ ucfirst($this->selectedSensor->sensor_type) }} Sensor
                                &middot; {{ $this->selectedSensor->asset?->location?->name ?? 'Unknown Location' }}
                                &middot; <span class="font-mono text-slate-400">ID: {{ $this->selectedSensor->id }}</span>
                            </p>
                        </div>
                        <div class="text-right pl-4">
                            <div class="text-4xl font-bold font-mono tracking-tight {{ $sensorAlert ? 'text-red-600' : 'text-slate-900' }}">
                                {{ $this->selectedSensor->last_value }}<span class="text-lg font-normal text-slate-400 ml-0.5">{{ $this->selectedSensor->unit }}</span>
                            </div>
                            <div class="text-xs text-slate-400 mt-1 font-mono">
                                Range: {{ $this->selectedSensor->threshold_min ?? '---' }} &ndash; {{ $this->selectedSensor->threshold_max ?? '---' }} {{ $this->selectedSensor->unit }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Chart Section --}}
                <div class="px-6 py-5">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                            {{ strtoupper($this->selectedSensor->sensor_type) }} Over Time
                        </h4>
                        <span class="text-xs text-slate-400 font-mono">Sensor Data (24H)</span>
                    </div>
                    <div class="h-80 bg-slate-50/50 rounded-lg p-2" wire:ignore>
                        <canvas id="sensorChart"></canvas>
                    </div>
                </div>

                {{-- Recent Anomalies Table --}}
                <div class="px-6 pb-5">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Recent Anomalies</h4>
                    @php $anomalies = $this->readings->where('is_anomaly', true)->take(10); @endphp
                    @if($anomalies->count() > 0)
                    <div class="border border-slate-200 rounded-lg overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="px-4 py-2.5 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Timestamp</th>
                                    <th class="px-4 py-2.5 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Value</th>
                                    <th class="px-4 py-2.5 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Threshold Exceeded</th>
                                    <th class="px-4 py-2.5 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Severity</th>
                                    <th class="px-4 py-2.5 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($anomalies as $reading)
                                @php
                                    $anomalyType = $this->selectedSensor->getAnomalyType((float)$reading->value);
                                    $exceededThreshold = $anomalyType === 'above_max'
                                        ? 'Max (' . $this->selectedSensor->threshold_max . $this->selectedSensor->unit . ')'
                                        : 'Min (' . $this->selectedSensor->threshold_min . $this->selectedSensor->unit . ')';
                                    $severity = abs((float)$reading->value - (float)($anomalyType === 'above_max' ? $this->selectedSensor->threshold_max : $this->selectedSensor->threshold_min));
                                    $severityLabel = $severity > 20 ? 'Critical' : ($severity > 10 ? 'High' : 'Medium');
                                    $severityColor = $severity > 20 ? 'text-red-700 bg-red-50' : ($severity > 10 ? 'text-orange-700 bg-orange-50' : 'text-amber-700 bg-amber-50');
                                @endphp
                                <tr class="bg-red-50/40 hover:bg-red-50/70 transition-colors">
                                    <td class="px-4 py-2.5 text-slate-600 font-mono text-xs">{{ $reading->recorded_at->format('M d, Y H:i:s') }}</td>
                                    <td class="px-4 py-2.5 font-mono font-bold text-red-600">{{ $reading->value }}{{ $this->selectedSensor->unit }}</td>
                                    <td class="px-4 py-2.5 text-slate-600 text-xs">{{ $exceededThreshold }}</td>
                                    <td class="px-4 py-2.5">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold {{ $severityColor }}">{{ $severityLabel }}</span>
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <span class="text-xs text-slate-500">Auto-flagged</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="border border-dashed border-slate-200 rounded-lg p-8 text-center">
                        <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-slate-400 font-medium">No anomalies in the last 24 hours</p>
                        <p class="text-xs text-slate-300 mt-0.5">All readings within normal thresholds</p>
                    </div>
                    @endif
                </div>

                {{-- System Status Footer --}}
                <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">System Status</span>
                        <span class="flex items-center gap-1.5 text-xs text-slate-500">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                            Last Synced: {{ $this->selectedSensor->last_reading_at?->diffForHumans() ?? 'Never' }}
                        </span>
                    </div>
                    <span class="text-xs text-slate-400 font-mono">Uptime: 99.8%</span>
                </div>
            </div>

            <script>
                document.addEventListener('livewire:navigated', initChart);
                document.addEventListener('DOMContentLoaded', initChart);

                function initChart() {
                    const ctx = document.getElementById('sensorChart');
                    if (!ctx) return;

                    if (window.sensorChartInstance) window.sensorChartInstance.destroy();

                    const readings = @json($this->readings->map(fn($r) => ['t' => $r->recorded_at->format('H:i'), 'v' => (float)$r->value, 'a' => $r->is_anomaly]));
                    const thresholdMin = {{ $this->selectedSensor->threshold_min ?? 'null' }};
                    const thresholdMax = {{ $this->selectedSensor->threshold_max ?? 'null' }};

                    window.sensorChartInstance = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: readings.map(r => r.t),
                            datasets: [
                                {
                                    label: '{{ $this->selectedSensor->name }}',
                                    data: readings.map(r => r.v),
                                    borderColor: '#10b981',
                                    backgroundColor: 'rgba(16,185,129,0.08)',
                                    fill: true,
                                    tension: 0.35,
                                    borderWidth: 2,
                                    pointRadius: readings.map(r => r.a ? 7 : 2),
                                    pointBackgroundColor: readings.map(r => r.a ? '#ef4444' : '#10b981'),
                                    pointBorderColor: readings.map(r => r.a ? '#fecaca' : 'transparent'),
                                    pointBorderWidth: readings.map(r => r.a ? 3 : 0),
                                    pointHoverRadius: 8,
                                },
                                ...(thresholdMax !== null ? [{
                                    label: 'Max Threshold',
                                    data: readings.map(() => thresholdMax),
                                    borderColor: '#ef4444',
                                    borderDash: [6, 4],
                                    borderWidth: 1.5,
                                    pointRadius: 0,
                                    fill: false,
                                }] : []),
                                ...(thresholdMin !== null ? [{
                                    label: 'Min Threshold',
                                    data: readings.map(() => thresholdMin),
                                    borderColor: '#3b82f6',
                                    borderDash: [6, 4],
                                    borderWidth: 1.5,
                                    pointRadius: 0,
                                    fill: false,
                                }] : []),
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                intersect: false,
                                mode: 'index',
                            },
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        usePointStyle: true,
                                        pointStyle: 'circle',
                                        padding: 20,
                                        font: { size: 11, family: 'Inter, system-ui, sans-serif' },
                                    },
                                },
                                tooltip: {
                                    backgroundColor: '#1e293b',
                                    titleFont: { size: 12, family: 'Inter, system-ui, sans-serif' },
                                    bodyFont: { size: 11, family: 'JetBrains Mono, monospace' },
                                    padding: 12,
                                    cornerRadius: 8,
                                    displayColors: true,
                                },
                            },
                            scales: {
                                x: {
                                    display: true,
                                    ticks: { maxTicksLimit: 12, font: { size: 10, family: 'JetBrains Mono, monospace' } },
                                    grid: { color: 'rgba(148,163,184,0.1)' },
                                },
                                y: {
                                    display: true,
                                    ticks: { font: { size: 10, family: 'JetBrains Mono, monospace' } },
                                    grid: { color: 'rgba(148,163,184,0.15)' },
                                },
                            },
                        },
                    });
                }
            </script>
            @else
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-16 text-center">
                <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z"/>
                    </svg>
                </div>
                <p class="text-slate-500 font-medium">Select a sensor to view readings</p>
                <p class="text-sm text-slate-400 mt-1">Choose from the Sensor Directory on the left</p>
            </div>
            @endif
        </div>
    </div>
</div>
