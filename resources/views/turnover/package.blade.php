<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Turnover Package — {{ $project->name }}</title>
    <style>
        @page { margin: 32px 36px; }
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 10.5pt;
            color: #1f2937;
            line-height: 1.45;
        }
        h1 { font-size: 24pt; margin: 0 0 4px; color: #064e3b; }
        h2 { font-size: 15pt; margin: 24px 0 8px; color: #065f46; border-bottom: 2px solid #10b981; padding-bottom: 4px; }
        h3 { font-size: 12pt; margin: 14px 0 6px; color: #111827; }
        .muted { color: #6b7280; font-size: 9.5pt; }
        .chip {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 999px;
            font-size: 8.5pt;
            font-weight: 600;
        }
        .chip-green { background: #d1fae5; color: #065f46; }
        .chip-amber { background: #fef3c7; color: #92400e; }
        .chip-red { background: #fee2e2; color: #991b1b; }
        .chip-gray { background: #e5e7eb; color: #374151; }
        .chip-blue { background: #dbeafe; color: #1e40af; }

        .cover {
            text-align: center;
            padding: 40px 0 60px;
            border-bottom: 4px double #10b981;
            margin-bottom: 18px;
        }
        .cover .brand { font-size: 11pt; letter-spacing: 4px; text-transform: uppercase; color: #10b981; font-weight: 700; }
        .cover .title { font-size: 30pt; font-weight: 800; margin: 14px 0 6px; color: #064e3b; }
        .cover .sub { font-size: 13pt; color: #374151; }

        .score-box {
            margin: 30px auto;
            width: 70%;
            border: 1px solid #10b981;
            border-radius: 12px;
            padding: 24px;
            background: #ecfdf5;
            text-align: center;
        }
        .score-box .n { font-size: 44pt; font-weight: 800; color: #065f46; }
        .score-box .g { font-size: 14pt; font-weight: 700; color: #047857; }

        .kv-row { display: block; margin-bottom: 3px; }
        .kv-row .k { display: inline-block; color: #6b7280; font-weight: 600; min-width: 130px; }
        .kv-row .v { color: #111827; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 18px;
            font-size: 9pt;
        }
        thead th {
            text-align: left;
            background: #f3f4f6;
            border-bottom: 2px solid #d1d5db;
            padding: 6px 8px;
            font-size: 9pt;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        tbody td {
            padding: 6px 8px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }
        tbody tr:nth-child(even) td { background: #fafafa; }

        .blockers {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 10px 14px;
            margin: 8px 0 14px;
        }
        .blockers .title { color: #991b1b; font-weight: 700; margin-bottom: 4px; }

        .sig-grid { margin-top: 20px; width: 100%; }
        .sig-grid td { border-bottom: 1px solid #111827; padding-top: 40px; width: 50%; }
        .sig-grid .lbl { padding: 4px 0 24px; border: none; font-size: 9pt; color: #6b7280; }

        .footer {
            position: fixed;
            bottom: -24px;
            left: 0;
            right: 0;
            font-size: 8pt;
            color: #9ca3af;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 4px;
        }

        .page-break { page-break-before: always; }
        .avoid-break { page-break-inside: avoid; }
    </style>
</head>
<body>

{{-- ───────────────────── COVER PAGE ───────────────────── --}}
<div class="cover">
    <div class="brand">Nexus Ops · Accelerated Turnover Package</div>
    <div class="title">{{ $project->name }}</div>
    <div class="sub">{{ $tenant->name ?? 'Organization' }}</div>
    @if($project->address)
        <div class="muted" style="margin-top: 8px;">
            {{ $project->address }}@if($project->city), {{ $project->city }}@endif@if($project->state), {{ $project->state }}@endif@if($project->zip) {{ $project->zip }}@endif
        </div>
    @endif
</div>

<div class="score-box">
    <div class="n">{{ number_format($readiness_score, 0) }}%</div>
    <div class="g">Handover Readiness · Grade {{ $readiness_grade }}</div>
    <div class="muted" style="margin-top: 10px;">
        Target Handover: {{ $project->target_handover_date?->format('M d, Y') ?? 'TBD' }}
        @if($project->actual_handover_date)
            · Actual: {{ $project->actual_handover_date->format('M d, Y') }}
        @endif
    </div>
</div>

@if(count($handover_blockers) > 0)
<div class="blockers avoid-break">
    <div class="title">Outstanding Handover Blockers</div>
    <ul style="margin: 4px 0 0 18px; padding: 0;">
        @foreach($handover_blockers as $blocker)
            <li>{{ $blocker['label'] }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="avoid-break">
    <h3>Project Summary</h3>
    <div class="kv-row"><span class="k">Project Type:</span><span class="v">{{ ucfirst($project->project_type ?? '—') }}</span></div>
    <div class="kv-row"><span class="k">Status:</span><span class="v"><span class="chip chip-blue">{{ ucfirst($project->status) }}</span></span></div>
    <div class="kv-row"><span class="k">Total Assets:</span><span class="v">{{ $asset_count }}</span></div>
    <div class="kv-row"><span class="k">Locations:</span><span class="v">{{ $project->locations->count() }}</span></div>
    <div class="kv-row"><span class="k">Closeout Docs:</span><span class="v">{{ $project->completed_closeout_docs }}/{{ $project->total_closeout_docs }}</span></div>
    <div class="kv-row"><span class="k">Commissioning Tests:</span><span class="v">{{ $project->completed_tests }}/{{ $project->total_tests }}</span></div>
    <div class="kv-row"><span class="k">Outstanding Issues:</span><span class="v">{{ count($outstanding_issues) }} of {{ $project->total_issues }} total</span></div>
    @if($fpt['executions_total'] > 0)
        <div class="kv-row">
            <span class="k">FPT Executions:</span>
            <span class="v">
                {{ $fpt['executions_passed'] }} passed / {{ $fpt['executions_failed'] }} failed / {{ $fpt['executions_in_flight'] }} in-flight ·
                <span class="chip @if($fpt['execution_pass_rate'] >= 95) chip-green @elseif($fpt['execution_pass_rate'] >= 80) chip-amber @else chip-red @endif">
                    {{ number_format($fpt['execution_pass_rate'], 1) }}% pass
                </span>
                ·
                <span class="chip chip-blue">{{ $fpt['executions_witnessed'] }} witnessed</span>
            </span>
        </div>
    @endif
</div>

{{-- ───────────────────── ASSET INVENTORY ───────────────────── --}}
<div class="page-break"></div>
<h2>Asset Inventory</h2>
<p class="muted">The following {{ $asset_count }} assets were commissioned and are being turned over to the operations team. Scan the QR code on each asset to pull up its live operational record.</p>

@foreach($assets as $asset)
<div class="avoid-break" style="margin-bottom: 14px; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px 14px; background: #fff;">
    <table style="margin:0; border: none;">
        <tr>
            <td style="width: 64px; padding: 0 10px 0 0; border: none; vertical-align: top;">
                {{-- QR code embedded as SVG data URI --}}
                <div style="border: 1px solid #d1d5db; padding: 4px; border-radius: 4px; text-align: center;">
                    <img src="data:image/svg+xml;base64,{{ base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(80)->margin(0)->generate($asset['qr_code'])) }}" width="56" height="56" alt="QR">
                    <div style="font-size: 6pt; margin-top: 2px; color: #6b7280;">{{ $asset['qr_code'] }}</div>
                </div>
            </td>
            <td style="border: none; padding: 0;">
                <div style="font-size: 11pt; font-weight: 700; color: #111827;">{{ $asset['name'] }}
                    @if($asset['asset_tag'])<span class="muted" style="font-weight: 400;"> · {{ $asset['asset_tag'] }}</span>@endif
                </div>
                <div class="muted" style="margin: 2px 0 6px;">
                    {{ $asset['system_type'] ?? '—' }}
                    @if($asset['location']) · {{ $asset['location'] }}@endif
                    @if($asset['commissioning_status'])
                        ·
                        <span class="chip @if($asset['commissioning_status']==='commissioned') chip-green @else chip-amber @endif">
                            {{ ucfirst(str_replace('_', ' ', $asset['commissioning_status'])) }}
                        </span>
                    @endif
                </div>
                <div style="font-size: 9pt;">
                    <strong>Manufacturer:</strong> {{ $asset['manufacturer'] ?? '—' }}
                    &nbsp;·&nbsp;<strong>Model:</strong> {{ $asset['model_number'] ?? '—' }}
                    &nbsp;·&nbsp;<strong>Serial:</strong> {{ $asset['serial_number'] ?? '—' }}
                </div>
                <div style="font-size: 9pt; margin-top: 2px;">
                    <strong>Installed:</strong> {{ $asset['install_date'] ?? '—' }}
                    &nbsp;·&nbsp;<strong>Warranty Expires:</strong>
                    @if($asset['warranty_expiry'])
                        {{ $asset['warranty_expiry'] }}
                        <span class="chip @if($asset['warranty_active']) chip-green @else chip-red @endif">{{ $asset['warranty_active'] ? 'Active' : 'Expired' }}</span>
                    @else — @endif
                    @if($asset['expected_life'])
                        &nbsp;·&nbsp;<strong>Expected Life:</strong> {{ $asset['expected_life'] }} yrs
                    @endif
                </div>
                @if(count($asset['pm_schedules']) > 0)
                    <div style="font-size: 9pt; margin-top: 4px;">
                        <strong>PM Schedules:</strong>
                        @foreach($asset['pm_schedules'] as $pm)
                            <span class="chip chip-gray">{{ $pm['name'] }} ({{ $pm['frequency'] }})</span>
                        @endforeach
                    </div>
                @endif
            </td>
        </tr>
    </table>
</div>
@endforeach

{{-- ───────────────────── CLOSEOUT REQUIREMENTS ───────────────────── --}}
@if(count($closeout_by_category) > 0)
<div class="page-break"></div>
<h2>Closeout Requirements</h2>
<p class="muted">Status of all closeout deliverables required for handover.</p>

@foreach($closeout_by_category as $cat)
<h3>{{ $cat['category'] }} <span class="muted">({{ $cat['completed'] }}/{{ $cat['total'] }} complete)</span></h3>
<table>
    <thead>
        <tr><th>Requirement</th><th style="width:120px;">Status</th><th style="width:110px;">Due Date</th><th style="width:180px;">Document</th></tr>
    </thead>
    <tbody>
        @foreach($cat['items'] as $item)
        <tr>
            <td>{{ $item['name'] }}</td>
            <td>
                <span class="chip
                    @if($item['status']==='completed') chip-green
                    @elseif($item['status']==='in_progress') chip-amber
                    @elseif($item['status']==='overdue') chip-red
                    @else chip-gray @endif">
                    {{ ucfirst(str_replace('_', ' ', $item['status'] ?? '—')) }}
                </span>
            </td>
            <td>{{ $item['due_date'] ?? '—' }}</td>
            <td>{{ $item['document'] ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endforeach
@endif

{{-- ───────────────────── COMMISSIONING TESTS ───────────────────── --}}
@if(count($completed_tests) > 0)
<h2>Commissioning Test Records</h2>
<table>
    <thead>
        <tr><th style="width:120px;">WO Number</th><th>Test</th><th style="width:120px;">Completed</th><th style="width:120px;">Verified</th></tr>
    </thead>
    <tbody>
        @foreach($completed_tests as $t)
        <tr>
            <td style="font-family: monospace;">{{ $t['wo_number'] }}</td>
            <td>{{ $t['title'] }}</td>
            <td>{{ $t['completed_at'] ?? '—' }}</td>
            <td>
                @if($t['verified_at'])
                    <span class="chip chip-green">{{ $t['verified_at'] }}</span>
                @else
                    <span class="chip chip-amber">Pending</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- ─────────────────── COMMISSIONING PERFORMANCE (FPT) ─────────────────── --}}
@if($fpt['executions_total'] > 0)
<div class="page-break"></div>
<h2>Functional Performance Test Scorecard</h2>
<p class="muted">Every FPT executed against this project, with pass/fail rates and witness attestations. The step-level pass rate is the primary metric used by the commissioning authority to certify readiness.</p>

<table style="margin-bottom: 6px;">
    <thead>
        <tr>
            <th>Metric</th>
            <th style="width:100px;">Value</th>
            <th>Metric</th>
            <th style="width:100px;">Value</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Total Executions</strong></td>
            <td>{{ $fpt['executions_total'] }}</td>
            <td><strong>Total Steps</strong></td>
            <td>{{ $fpt['step_total'] }}</td>
        </tr>
        <tr>
            <td><strong>Execution Pass Rate</strong></td>
            <td>
                <span class="chip @if($fpt['execution_pass_rate'] >= 95) chip-green @elseif($fpt['execution_pass_rate'] >= 80) chip-amber @else chip-red @endif">
                    {{ number_format($fpt['execution_pass_rate'], 1) }}%
                </span>
            </td>
            <td><strong>Step Pass Rate</strong></td>
            <td>
                <span class="chip @if($fpt['step_pass_rate'] >= 95) chip-green @elseif($fpt['step_pass_rate'] >= 80) chip-amber @else chip-red @endif">
                    {{ number_format($fpt['step_pass_rate'], 1) }}%
                </span>
            </td>
        </tr>
        <tr>
            <td><strong>Passed / Failed / In-flight</strong></td>
            <td>{{ $fpt['executions_passed'] }} / {{ $fpt['executions_failed'] }} / {{ $fpt['executions_in_flight'] }}</td>
            <td><strong>Witnessed Executions</strong></td>
            <td>{{ $fpt['executions_witnessed'] }} / {{ $fpt['executions_total'] }}</td>
        </tr>
    </tbody>
</table>

@if(count($fpt['by_level']) > 0)
<h3>Breakdown by Cx Level</h3>
<p class="muted">ASHRAE Guideline 0 commissioning levels (L1 Installation → L5 Occupant Verification) reveal where this project stands in the commissioning lifecycle.</p>
<table>
    <thead>
        <tr>
            <th style="width:100px;">Cx Level</th>
            <th style="width:80px;">Total</th>
            <th style="width:80px;">Passed</th>
            <th style="width:80px;">Failed</th>
            <th>Pass Rate</th>
        </tr>
    </thead>
    <tbody>
        @foreach($fpt['by_level'] as $lvl)
        <tr>
            <td><strong>{{ $lvl['level'] }}</strong></td>
            <td>{{ $lvl['total'] }}</td>
            <td>{{ $lvl['passed'] }}</td>
            <td>{{ $lvl['failed'] }}</td>
            <td>
                <span class="chip @if($lvl['pass_rate'] >= 95) chip-green @elseif($lvl['pass_rate'] >= 80) chip-amber @else chip-red @endif">
                    {{ number_format($lvl['pass_rate'], 1) }}%
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<h3>Execution Log</h3>
<table>
    <thead>
        <tr>
            <th style="width:40px;">#</th>
            <th>Script / Asset</th>
            <th style="width:60px;">Cx</th>
            <th style="width:80px;">Status</th>
            <th style="width:70px;">Steps</th>
            <th style="width:110px;">Completed</th>
            <th style="width:130px;">Witness</th>
        </tr>
    </thead>
    <tbody>
        @foreach($fpt['rows'] as $row)
        <tr>
            <td>{{ $row['id'] }}</td>
            <td>
                <strong>{{ $row['script'] }}</strong>
                @if($row['version']) <span class="muted">v{{ $row['version'] }}</span> @endif
                @if($row['is_retest']) <span class="chip chip-blue">Retest</span> @endif
                <div class="muted" style="font-size: 8pt; margin-top: 1px;">
                    on {{ $row['asset'] }}@if($row['asset_tag']) · {{ $row['asset_tag'] }}@endif
                </div>
            </td>
            <td>{{ $row['cx_level'] ?? '—' }}</td>
            <td>
                <span class="chip
                    @if($row['status']==='passed') chip-green
                    @elseif($row['status']==='failed') chip-red
                    @elseif($row['status']==='in_progress') chip-blue
                    @else chip-amber @endif">
                    {{ ucfirst(str_replace('_', ' ', $row['status'])) }}
                </span>
            </td>
            <td style="font-family: monospace; font-size: 8pt;">
                {{ $row['pass_count'] }}/{{ $row['total_count'] }}
                @if($row['fail_count'] > 0) <span style="color:#991b1b;">({{ $row['fail_count'] }} fail)</span>@endif
            </td>
            <td>{{ $row['completed_at'] ?? $row['started_at'] ?? '—' }}</td>
            <td>
                @if($row['witnessed'])
                    <span class="chip chip-green">Signed</span>
                    <div class="muted" style="font-size: 8pt; margin-top: 1px;">
                        {{ $row['witness_name'] ?? '—' }}
                    </div>
                @else
                    <span class="chip chip-gray">—</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- ───────────────────── OUTSTANDING ISSUES ───────────────────── --}}
@if(count($outstanding_issues) > 0)
<h2>Outstanding Punch List Items</h2>
<p class="muted">The following issues remain open at the time of this handover and must be tracked to completion by the operations team.</p>
<table>
    <thead>
        <tr><th style="width:40px;">#</th><th>Title</th><th style="width:90px;">Priority</th><th style="width:100px;">Status</th><th style="width:110px;">Due Date</th></tr>
    </thead>
    <tbody>
        @foreach($outstanding_issues as $issue)
        <tr>
            <td>{{ $issue['id'] }}</td>
            <td>{{ $issue['title'] }}</td>
            <td>
                <span class="chip
                    @if(in_array($issue['priority'], ['critical', 'emergency'])) chip-red
                    @elseif($issue['priority']==='high') chip-amber
                    @else chip-gray @endif">
                    {{ ucfirst($issue['priority'] ?? '—') }}
                </span>
            </td>
            <td>{{ ucfirst(str_replace('_', ' ', $issue['status'])) }}</td>
            <td>{{ $issue['due_date'] ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- ───────────────────── SIGN-OFF ───────────────────── --}}
<div class="page-break"></div>
<h2>Handover Certification</h2>
<p>
    By signing below, the undersigned parties certify that this project has been commissioned,
    tested, and turned over in accordance with the contract documents, applicable codes, and
    the operational readiness standards documented in this package. Any outstanding punch-list
    items identified above will be tracked to resolution post-handover.
</p>
<table class="sig-grid" style="margin-top: 40px;">
    <tr>
        <td>&nbsp;</td>
        <td style="padding-left: 24px;">&nbsp;</td>
    </tr>
    <tr>
        <td class="lbl">Commissioning Authority · Date</td>
        <td class="lbl" style="padding-left: 24px;">General Contractor · Date</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td style="padding-left: 24px;">&nbsp;</td>
    </tr>
    <tr>
        <td class="lbl">Owner's Representative · Date</td>
        <td class="lbl" style="padding-left: 24px;">Facility Manager · Date</td>
    </tr>
</table>

<div class="footer">
    Generated {{ $generated_at }} · Nexus Ops · {{ $tenant->name ?? 'Organization' }}
</div>

</body>
</html>
