<div class="min-h-screen bg-gray-50">
    <div class="px-6 py-6 max-w-[1400px] mx-auto">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Analytics & Reports</h1>
                <p class="mt-1 text-sm text-gray-500">Performance metrics and operational insights</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <input type="date" wire:model.live="dateFrom"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                <span class="text-sm text-gray-400">to</span>
                <input type="date" wire:model.live="dateTo"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                <select wire:model.live="projectFilter"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                    <option value="">All Projects</option>
                    @foreach($this->projects as $project)
                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
                <a href="{{ route('reports.export-pdf', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    Export PDF
                </a>
            </div>
        </div>

        {{-- KPI Summary Row --}}
        @php $kpi = $this->kpiSummary; @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {{-- Total Work Orders --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Work Orders</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($kpi['total_work_orders']) }}</p>
                    </div>
                    <div class="h-12 w-12 rounded-lg bg-indigo-50 flex items-center justify-center">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Avg Resolution Time --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Avg Resolution Time</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $kpi['avg_mttr_hours'] }}<span class="text-base font-normal text-gray-400 ml-1">hrs</span></p>
                    </div>
                    <div class="h-12 w-12 rounded-lg bg-amber-50 flex items-center justify-center">
                        <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Total Spend --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Spend</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">${{ number_format($kpi['total_cost'], 0) }}</p>
                    </div>
                    <div class="h-12 w-12 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- PM Compliance --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">PM Compliance</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $kpi['pm_compliance'] }}<span class="text-base font-normal text-gray-400 ml-1">%</span></p>
                    </div>
                    <div class="h-12 w-12 rounded-lg bg-blue-50 flex items-center justify-center">
                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Work Orders by Month --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Work Orders by Month</h3>
                <div wire:ignore>
                    <canvas id="woByMonthChart" height="260"></canvas>
                </div>
            </div>

            {{-- Work Order Aging --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Work Order Aging</h3>
                <div wire:ignore>
                    <canvas id="woAgingChart" height="260"></canvas>
                </div>
            </div>

            {{-- Top 10 Problem Assets --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Top 10 Problem Assets</h3>
                <div wire:ignore>
                    <canvas id="topAssetsChart" height="260"></canvas>
                </div>
            </div>

            {{-- PM Compliance Trend --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">PM Compliance Trend</h3>
                <div wire:ignore>
                    <canvas id="pmComplianceChart" height="260"></canvas>
                </div>
            </div>

            {{-- SLA Compliance --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">SLA Compliance</h3>
                <div wire:ignore>
                    <canvas id="slaComplianceChart" height="260"></canvas>
                </div>
            </div>

            {{-- Cost by System --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Cost by System</h3>
                <div wire:ignore>
                    <canvas id="costBySystemChart" height="260"></canvas>
                </div>
            </div>

        </div>
    </div>

    {{-- Chart Initialization --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartDefaults = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { font: { size: 11 }, usePointStyle: true, padding: 16 } }
                }
            };

            // Work Orders by Month - Stacked Bar
            const woByMonth = @json($this->workOrdersByMonth);
            new Chart(document.getElementById('woByMonthChart'), {
                type: 'bar',
                data: {
                    labels: woByMonth.labels,
                    datasets: [
                        { label: 'Corrective', data: woByMonth.corrective, backgroundColor: '#ef4444', borderRadius: 3 },
                        { label: 'Preventive', data: woByMonth.preventive, backgroundColor: '#3b82f6', borderRadius: 3 },
                        { label: 'Inspection', data: woByMonth.inspection, backgroundColor: '#f59e0b', borderRadius: 3 },
                    ]
                },
                options: {
                    ...chartDefaults,
                    scales: {
                        x: { stacked: true, grid: { display: false }, ticks: { font: { size: 10 } } },
                        y: { stacked: true, beginAtZero: true, ticks: { font: { size: 10 }, stepSize: 1 } }
                    }
                }
            });

            // Work Order Aging - Horizontal Bar
            const woAging = @json($this->workOrderAging);
            new Chart(document.getElementById('woAgingChart'), {
                type: 'bar',
                data: {
                    labels: woAging.labels,
                    datasets: [{
                        label: 'Open Work Orders',
                        data: woAging.values,
                        backgroundColor: ['#10b981', '#f59e0b', '#f97316', '#ef4444'],
                        borderRadius: 3,
                    }]
                },
                options: {
                    ...chartDefaults,
                    indexAxis: 'y',
                    plugins: { ...chartDefaults.plugins, legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, ticks: { font: { size: 10 }, stepSize: 1 } },
                        y: { grid: { display: false }, ticks: { font: { size: 11 } } }
                    }
                }
            });

            // Top 10 Problem Assets - Horizontal Bar
            const topAssets = @json($this->topProblemAssets);
            new Chart(document.getElementById('topAssetsChart'), {
                type: 'bar',
                data: {
                    labels: topAssets.labels,
                    datasets: [{
                        label: 'Work Orders',
                        data: topAssets.wo_counts,
                        backgroundColor: '#6366f1',
                        borderRadius: 3,
                    }]
                },
                options: {
                    ...chartDefaults,
                    indexAxis: 'y',
                    plugins: { ...chartDefaults.plugins, legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, ticks: { font: { size: 10 }, stepSize: 1 } },
                        y: { grid: { display: false }, ticks: { font: { size: 10 } } }
                    }
                }
            });

            // PM Compliance Trend - Line with target
            const pmCompliance = @json($this->pmComplianceOverTime);
            new Chart(document.getElementById('pmComplianceChart'), {
                type: 'line',
                data: {
                    labels: pmCompliance.labels,
                    datasets: [
                        {
                            label: 'PM Compliance %',
                            data: pmCompliance.values,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 3,
                            pointBackgroundColor: '#10b981',
                        },
                        {
                            label: 'Target (85%)',
                            data: Array(pmCompliance.labels.length).fill(85),
                            borderColor: '#ef4444',
                            borderDash: [6, 4],
                            borderWidth: 2,
                            pointRadius: 0,
                            fill: false,
                        }
                    ]
                },
                options: {
                    ...chartDefaults,
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                        y: { min: 0, max: 100, ticks: { font: { size: 10 }, callback: v => v + '%' } }
                    }
                }
            });

            // SLA Compliance - Line
            const slaCompliance = @json($this->slaComplianceTrend);
            new Chart(document.getElementById('slaComplianceChart'), {
                type: 'line',
                data: {
                    labels: slaCompliance.labels,
                    datasets: [{
                        label: 'SLA Compliance %',
                        data: slaCompliance.values,
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 3,
                        pointBackgroundColor: '#6366f1',
                    }]
                },
                options: {
                    ...chartDefaults,
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                        y: { min: 0, max: 100, ticks: { font: { size: 10 }, callback: v => v + '%' } }
                    }
                }
            });

            // Cost by System - Doughnut
            const costBySystem = @json($this->costByCategory);
            new Chart(document.getElementById('costBySystemChart'), {
                type: 'doughnut',
                data: {
                    labels: costBySystem.labels,
                    datasets: [{
                        data: costBySystem.values,
                        backgroundColor: [
                            '#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
                            '#ec4899', '#14b8a6', '#f97316', '#06b6d4', '#84cc16'
                        ],
                        borderWidth: 0,
                    }]
                },
                options: {
                    ...chartDefaults,
                    cutout: '60%',
                    plugins: {
                        ...chartDefaults.plugins,
                        legend: { position: 'right', labels: { font: { size: 11 }, usePointStyle: true, padding: 12 } },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.label + ': $' + ctx.parsed.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</div>
