<div>
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Asset Health Matrix</h1>
        <p class="mt-1 text-sm text-gray-500">Health scores plotted against asset criticality across all assets</p>
    </div>

    {{-- Danger Zone Alert --}}
    @if($this->dangerZoneCount > 0)
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">
        <div class="flex items-center gap-3">
            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-red-800">{{ $this->dangerZoneCount }} {{ Str::plural('asset', $this->dangerZoneCount) }} in the Danger Zone</p>
                <p class="text-xs text-red-600 mt-0.5">High-criticality assets with low health scores require immediate attention</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Scatter Plot --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Criticality vs. Health Score</h2>
            <div class="flex items-center gap-3 flex-wrap">
                @foreach($this->systemTypes as $type)
                    <span class="inline-flex items-center gap-1.5 text-xs text-gray-600">
                        <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $this->getSystemTypeColor($loop->index) }}"></span>
                        {{ $type }}
                    </span>
                @endforeach
            </div>
        </div>
        <div class="relative" style="height: 400px;">
            <canvas id="healthMatrixChart" wire:ignore></canvas>
        </div>
    </div>

    {{-- Asset Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Assets by Health Score</h2>
            <p class="text-xs text-gray-500 mt-0.5">Sorted by lowest health score first</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-3.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Asset</th>
                        <th class="px-6 py-3.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Health Score</th>
                        <th class="px-6 py-3.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Condition</th>
                        <th class="px-6 py-3.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">System Type</th>
                        <th class="px-6 py-3.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Contributing Factors</th>
                        <th class="px-6 py-3.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($this->assetTable as $row)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">{{ $row['name'] }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-24 bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                    <div class="h-2.5 rounded-full transition-all
                                        {{ $row['health_score'] > 70 ? 'bg-emerald-500' : ($row['health_score'] >= 40 ? 'bg-amber-500' : 'bg-red-500') }}"
                                        style="width: {{ $row['health_score'] }}%">
                                    </div>
                                </div>
                                <span class="text-sm font-bold
                                    {{ $row['health_score'] > 70 ? 'text-emerald-600' : ($row['health_score'] >= 40 ? 'text-amber-600' : 'text-red-600') }}">
                                    {{ $row['health_score'] }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium
                                {{ match($row['condition']) {
                                    'excellent' => 'bg-emerald-50 text-emerald-700',
                                    'good' => 'bg-green-50 text-green-700',
                                    'fair' => 'bg-amber-50 text-amber-700',
                                    'poor' => 'bg-orange-50 text-orange-700',
                                    'critical' => 'bg-red-50 text-red-700',
                                    default => 'bg-gray-50 text-gray-700',
                                } }}">
                                <span class="h-1.5 w-1.5 rounded-full
                                    {{ match($row['condition']) {
                                        'excellent' => 'bg-emerald-500',
                                        'good' => 'bg-green-500',
                                        'fair' => 'bg-amber-500',
                                        'poor' => 'bg-orange-500',
                                        'critical' => 'bg-red-500',
                                        default => 'bg-gray-500',
                                    } }}"></span>
                                {{ ucfirst($row['condition']) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-700">{{ $row['system_type'] }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2 flex-wrap">
                                @foreach($row['factors'] as $factor => $score)
                                    @php
                                        $label = match($factor) {
                                            'age' => 'Age',
                                            'condition' => 'Cond',
                                            'work_orders' => 'WOs',
                                            'sensor_anomaly' => 'Sensor',
                                            'pm_compliance' => 'PM',
                                            default => $factor,
                                        };
                                        $factorColor = $score > 70 ? 'emerald' : ($score >= 40 ? 'amber' : 'red');
                                    @endphp
                                    <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-semibold bg-{{ $factorColor }}-50 text-{{ $factorColor }}-700 border border-{{ $factorColor }}-200">
                                        {{ $label }}: {{ $score }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <a href="{{ route('assets.show', $row['id']) }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition-colors">
                                Details &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <p class="text-sm text-gray-500">No assets found for this tenant.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Chart.js Scatter Plot --}}
    @script
    <script>
        const matrixData = @json($this->matrixData);
        const systemTypes = @json($this->systemTypes);

        const systemColors = [
            '#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6',
            '#ec4899', '#14b8a6', '#f97316', '#6366f1', '#84cc16'
        ];

        // Group data by system type
        const datasets = systemTypes.map((type, idx) => {
            const items = matrixData.filter(a => a.system_type === type);
            return {
                label: type,
                data: items.map(a => ({
                    x: a.criticality,
                    y: a.health_score,
                    label: a.name,
                })),
                backgroundColor: items.map(a => {
                    // Highlight danger zone: high criticality + low health
                    const maxCrit = Math.max(...matrixData.map(d => d.criticality), 1);
                    if (a.health_score < 40 && a.criticality > maxCrit * 0.5) {
                        return '#ef444490';
                    }
                    return systemColors[idx % systemColors.length] + '90';
                }),
                borderColor: systemColors[idx % systemColors.length],
                borderWidth: 1.5,
                pointRadius: 7,
                pointHoverRadius: 10,
            };
        });

        const ctx = document.getElementById('healthMatrixChart').getContext('2d');
        new Chart(ctx, {
            type: 'scatter',
            data: { datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const point = context.raw;
                                return `${point.label}: Health ${point.y}, Cost $${point.x.toLocaleString()}`;
                            }
                        }
                    },
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, pointStyle: 'circle', padding: 20 }
                    },
                    annotation: {
                        annotations: {
                            dangerZone: {
                                type: 'box',
                                xMin: Math.max(...matrixData.map(d => d.criticality), 1) * 0.5,
                                yMax: 40,
                                backgroundColor: 'rgba(239, 68, 68, 0.05)',
                                borderColor: 'rgba(239, 68, 68, 0.3)',
                                borderWidth: 2,
                                borderDash: [6, 3],
                                label: {
                                    display: true,
                                    content: 'DANGER ZONE',
                                    color: '#dc2626',
                                    font: { size: 11, weight: 'bold' },
                                    position: 'start'
                                }
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: { display: true, text: 'Replacement Cost ($)', font: { weight: '600' } },
                        grid: { color: '#f3f4f6' },
                        ticks: {
                            callback: function(value) { return '$' + value.toLocaleString(); }
                        }
                    },
                    y: {
                        title: { display: true, text: 'Health Score (0-100)', font: { weight: '600' } },
                        min: 0,
                        max: 100,
                        grid: { color: '#f3f4f6' },
                    }
                }
            }
        });
    </script>
    @endscript
</div>
