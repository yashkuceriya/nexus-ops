<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>NexusOps Monthly Maintenance Report</title>
    <style>
        @page {
            margin: 60px 50px 80px 50px;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #1f2937;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        /* Header */
        .header {
            border-bottom: 3px solid #10b981;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }

        .header h1 {
            font-size: 22px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 4px 0;
        }

        .header .subtitle {
            font-size: 13px;
            color: #6b7280;
            margin: 0;
        }

        .header .date-range {
            font-size: 13px;
            color: #374151;
            font-weight: 600;
            margin-top: 8px;
        }

        .header .tenant-name {
            font-size: 14px;
            color: #059669;
            font-weight: 600;
            margin-top: 2px;
        }

        /* Section titles */
        .section-title {
            font-size: 15px;
            font-weight: 700;
            color: #111827;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 6px;
            margin: 28px 0 14px 0;
        }

        /* KPI cards row */
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .kpi-table td {
            width: 25%;
            text-align: center;
            padding: 14px 8px;
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
        }

        .kpi-value {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            display: block;
            margin-bottom: 2px;
        }

        .kpi-label {
            font-size: 10px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.data-table thead th {
            background-color: #f3f4f6;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #4b5563;
            text-align: left;
            padding: 8px 12px;
            border-bottom: 2px solid #d1d5db;
        }

        table.data-table tbody td {
            padding: 8px 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
            color: #374151;
        }

        table.data-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        table.data-table .text-right {
            text-align: right;
        }

        table.data-table .text-center {
            text-align: center;
        }

        /* PM Compliance highlight */
        .compliance-box {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 16px 20px;
            text-align: center;
            margin-bottom: 24px;
        }

        .compliance-box .value {
            font-size: 36px;
            font-weight: 700;
            color: #059669;
        }

        .compliance-box .label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 600;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            height: 40px;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
            font-size: 9px;
            color: #9ca3af;
        }

        .footer .left {
            float: left;
        }

        .footer .right {
            float: right;
        }

        .no-data {
            text-align: center;
            color: #9ca3af;
            font-style: italic;
            padding: 20px 0;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>NexusOps Monthly Maintenance Report</h1>
        <p class="tenant-name">{{ $tenantName }}</p>
        <p class="date-range">{{ $dateFrom }} &mdash; {{ $dateTo }}</p>
    </div>

    {{-- KPI Summary --}}
    <div class="section-title">Key Performance Indicators</div>
    <table class="kpi-table">
        <tr>
            <td>
                <span class="kpi-value">{{ number_format($kpis['total_work_orders']) }}</span>
                <span class="kpi-label">Total Work Orders</span>
            </td>
            <td>
                <span class="kpi-value">{{ $kpis['avg_mttr_hours'] }} hrs</span>
                <span class="kpi-label">Avg MTTR</span>
            </td>
            <td>
                <span class="kpi-value">${{ number_format($kpis['total_cost'], 0) }}</span>
                <span class="kpi-label">Total Spend</span>
            </td>
            <td>
                <span class="kpi-value">{{ $kpis['pm_compliance'] }}%</span>
                <span class="kpi-label">PM Compliance</span>
            </td>
        </tr>
    </table>

    {{-- Work Order Summary by Type --}}
    <div class="section-title">Work Order Summary by Type</div>
    @if(count($woByType) > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th class="text-right">Count</th>
                </tr>
            </thead>
            <tbody>
                @foreach($woByType as $row)
                    <tr>
                        <td>{{ $row['type'] }}</td>
                        <td class="text-right">{{ $row['count'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="no-data">No work orders found in this period.</p>
    @endif

    {{-- Top 10 Problem Assets --}}
    <div class="section-title">Top 10 Problem Assets</div>
    @if(count($topAssets) > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 10%;">#</th>
                    <th>Asset Name</th>
                    <th class="text-center">Work Orders</th>
                    <th class="text-right">Total Cost</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topAssets as $index => $asset)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $asset['name'] }}</td>
                        <td class="text-center">{{ $asset['wo_count'] }}</td>
                        <td class="text-right">${{ number_format($asset['total_cost'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="no-data">No asset data available for this period.</p>
    @endif

    {{-- PM Compliance --}}
    <div class="section-title">Preventive Maintenance Compliance</div>
    <div class="compliance-box">
        <div class="value">{{ $pmCompliance }}%</div>
        <div class="label">Overall PM Compliance Rate</div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <span class="left">Generated by NexusOps on {{ $generatedAt }}</span>
        <span class="right">Page <script type="text/php">
            if (isset($pdf)) {
                $x = 520;
                $y = 818;
                $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
                $pdf->page_text($x, $y, $text, null, 8, array(0.61, 0.64, 0.69));
            }
        </script></span>
    </div>
</body>
</html>
