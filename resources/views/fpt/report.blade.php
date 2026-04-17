<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>FPT Report — {{ $execution->test_script_name }}</title>
    <style>
        /* NexusOps Cx report — tokens: indigo #4F46E5 primary, emerald #10B981 pass,
           red #EF4444 fail, border #E5E7EB, table header bg #F5F4F9. */
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #0F172A; margin: 0; }
        .page { padding: 24px 32px; }
        h1 { font-size: 20px; margin: 0 0 4px; color: #0F172A; }
        h2 { font-size: 13px; margin: 18px 0 8px; color: #0F172A; border-bottom: 1px solid #E5E7EB; padding-bottom: 4px; }
        h3 { font-size: 11px; margin: 12px 0 4px; color: #475569; }

        .muted { color: #94A3B8; }
        .tag { display: inline-block; padding: 1px 6px; border-radius: 9999px; font-size: 9px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
        .tag-pass { background: #D1FAE5; color: #065F46; }
        .tag-fail { background: #FEE2E2; color: #991B1B; }
        .tag-pending { background: #E5E7EB; color: #475569; }
        .tag-skipped { background: #F5F4F9; color: #94A3B8; }
        .tag-na { background: #F5F4F9; color: #94A3B8; }
        .tag-l1 { background: #EEF2FF; color: #3730A3; }
        .tag-l2 { background: #EEF2FF; color: #3730A3; }
        .tag-l3 { background: #FEF3C7; color: #92400E; }
        .tag-l4 { background: #FECACA; color: #991B1B; }
        .tag-l5 { background: #E0E7FF; color: #4338CA; }

        .hero {
            background: #4F46E5; color: white; padding: 18px 22px; border-radius: 12px;
            margin-bottom: 18px; display: table; width: 100%;
        }
        .hero-left, .hero-right { display: table-cell; vertical-align: middle; }
        .hero-right { text-align: right; }
        .hero-title { font-size: 18px; font-weight: 700; letter-spacing: -0.01em; }
        .hero-sub { font-size: 10px; color: #C7D2FE; margin-top: 4px; }
        .hero-stat-value { font-size: 32px; font-weight: 800; }
        .hero-stat-label { font-size: 9px; color: #C7D2FE; text-transform: uppercase; letter-spacing: 0.1em; }

        table { width: 100%; border-collapse: collapse; }
        th { background: #F5F4F9; text-align: left; padding: 6px 8px; font-size: 9px; text-transform: uppercase; letter-spacing: 0.06em; color: #475569; border-bottom: 1px solid #E5E7EB; }
        td { padding: 6px 8px; border-bottom: 1px solid #E5E7EB; vertical-align: top; }
        tr.row-fail td { background: #FEF2F2; }
        tr.row-pass td { background: #F0FDF4; }

        .grid-2 { display: table; width: 100%; margin-bottom: 6px; }
        .grid-2 > div { display: table-cell; width: 50%; padding-right: 10px; vertical-align: top; }

        .summary-grid { display: table; width: 100%; margin: 10px 0; border: 1px solid #E5E7EB; border-radius: 12px; overflow: hidden; }
        .summary-cell {
            display: table-cell; width: 20%; text-align: center; padding: 10px 8px;
            border-right: 1px solid #E5E7EB; background: #FFFFFF;
        }
        .summary-cell:last-child { border-right: none; }
        .summary-value { font-size: 18px; font-weight: 700; color: #0F172A; }
        .summary-label { font-size: 8px; text-transform: uppercase; color: #94A3B8; letter-spacing: 0.1em; }

        .sig-box {
            border: 1px solid #E5E7EB; border-radius: 12px; padding: 10px 12px;
            margin-top: 6px; background: #FFFFFF;
        }
        .sig-image { max-width: 240px; max-height: 80px; border: 1px solid #E5E7EB; background: white; padding: 4px; }
        .hash { font-family: monospace; font-size: 8px; word-break: break-all; color: #94A3B8; margin-top: 4px; }

        .footer {
            margin-top: 20px; padding-top: 8px; border-top: 1px solid #E5E7EB;
            font-size: 8px; color: #94A3B8;
        }
    </style>
</head>
<body>
<div class="page">

    {{-- Hero header --}}
    <div class="hero">
        <div class="hero-left">
            <div class="hero-title">{{ $execution->test_script_name }}</div>
            <div class="hero-sub">
                v{{ $execution->test_script_version }}
                @if($execution->cx_level) · Commissioning Level {{ $execution->cx_level }} @endif
                · Execution #{{ $execution->id }}
                @if($parent) · Retest of #{{ $parent->id }} @endif
            </div>
        </div>
        <div class="hero-right">
            <div class="hero-stat-value">{{ $pass_rate }}%</div>
            <div class="hero-stat-label">Pass Rate</div>
        </div>
    </div>

    {{-- Result banner --}}
    @php($banner = $banner ?? [
        'passed' => ['bg' => '#D1FAE5', 'border' => '#10B981', 'text' => '#065F46', 'label' => 'PASSED'],
        'failed' => ['bg' => '#FEE2E2', 'border' => '#EF4444', 'text' => '#991B1B', 'label' => 'FAILED'],
        'aborted' => ['bg' => '#F5F4F9', 'border' => '#94A3B8', 'text' => '#475569', 'label' => 'ABORTED'],
    ][$execution->status] ?? ['bg' => '#FEF3C7', 'border' => '#F59E0B', 'text' => '#92400E', 'label' => strtoupper(str_replace('_', ' ', $execution->status))])
    <div style="background: {{ $banner['bg'] }}; border-left: 4px solid {{ $banner['border'] }}; color: {{ $banner['text'] }}; padding: 10px 14px; font-weight: 700; font-size: 14px; margin-bottom: 14px;">
        {{ $banner['label'] }}
    </div>

    {{-- Context grid --}}
    <div class="grid-2">
        <div>
            <h3>Project &amp; Asset</h3>
            <div><strong>Project:</strong> {{ $project?->name ?? '—' }}</div>
            <div><strong>Asset:</strong> {{ $asset?->name ?? '—' }} @if($asset?->asset_tag) ({{ $asset->asset_tag }}) @endif</div>
            @if($asset?->manufacturer)
                <div><strong>Manufacturer:</strong> {{ $asset->manufacturer }} @if($asset->model_number) — {{ $asset->model_number }} @endif</div>
            @endif
            @if($asset?->serial_number)
                <div><strong>Serial:</strong> {{ $asset->serial_number }}</div>
            @endif
            @if($asset?->location?->name)
                <div><strong>Location:</strong> {{ $asset->location->name }}</div>
            @endif
        </div>
        <div>
            <h3>Test Execution</h3>
            <div><strong>Started:</strong> {{ $execution->started_at?->format('M d, Y g:i A') ?? '—' }}</div>
            <div><strong>Completed:</strong> {{ $execution->completed_at?->format('M d, Y g:i A') ?? '—' }}</div>
            @if($duration_minutes !== null)
                <div><strong>Duration:</strong> {{ $duration_minutes }} minutes</div>
            @endif
            <div><strong>Executed by:</strong> {{ $execution->starter?->name ?? '—' }}</div>
            @if($execution->cxAgent)
                <div><strong>Cx Agent:</strong> {{ $execution->cxAgent->name }}</div>
            @endif
        </div>
    </div>

    {{-- Summary counters --}}
    <div class="summary-grid">
        <div class="summary-cell">
            <div class="summary-value" style="color: #10B981">{{ $execution->pass_count }}</div>
            <div class="summary-label">Pass</div>
        </div>
        <div class="summary-cell">
            <div class="summary-value" style="color: #EF4444">{{ $execution->fail_count }}</div>
            <div class="summary-label">Fail</div>
        </div>
        <div class="summary-cell">
            <div class="summary-value" style="color: #94A3B8">{{ $execution->pending_count }}</div>
            <div class="summary-label">Pending</div>
        </div>
        <div class="summary-cell">
            <div class="summary-value">{{ $execution->total_count }}</div>
            <div class="summary-label">Total Steps</div>
        </div>
        <div class="summary-cell">
            <div class="summary-value">{{ $pass_rate }}%</div>
            <div class="summary-label">Pass Rate</div>
        </div>
    </div>

    {{-- Step detail table --}}
    <h2>Step Results</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 24px">#</th>
                <th>Step / Expected</th>
                <th style="width: 120px">Measured</th>
                <th style="width: 64px">Result</th>
            </tr>
        </thead>
        <tbody>
        @foreach($results as $r)
            @php($rowClass = ['pass' => 'row-pass', 'fail' => 'row-fail'][$r['status']] ?? '')
            <tr class="{{ $rowClass }}">
                <td>{{ $r['sequence'] }}</td>
                <td>
                    <div style="font-weight: 600;">{{ $r['title'] }}</div>
                    <div class="muted" style="font-size: 9px;">{{ $r['instruction'] }}</div>
                    @if($r['expected_value'])
                        <div style="font-size: 9px; margin-top: 2px;">
                            <span class="muted">Expected:</span> {{ $r['expected_value'] }}
                            @if($r['measurement_unit']) {{ $r['measurement_unit'] }} @endif
                        </div>
                    @endif
                    @if($r['issue_id'])
                        <div style="font-size: 9px; color: #991B1B; margin-top: 2px;">
                            ⚠ Auto-opened Deficiency Issue #{{ $r['issue_id'] }}
                            @if($r['issue_priority']) · {{ strtoupper($r['issue_priority']) }} @endif
                        </div>
                    @endif
                </td>
                <td>
                    @if($r['measured_value'] !== null && $r['measured_value'] !== '')
                        <div style="font-weight: 600;">{{ $r['measured_value'] }}
                            @if($r['measurement_unit']) {{ $r['measurement_unit'] }} @endif
                        </div>
                    @else
                        <span class="muted">—</span>
                    @endif
                    @if($r['auto_evaluated'])
                        <div style="font-size: 8px; color: #4F46E5;">auto-evaluated</div>
                    @endif
                    @if($r['notes'])
                        <div class="muted" style="font-size: 8px; margin-top: 2px;">{{ $r['notes'] }}</div>
                    @endif
                </td>
                <td>
                    <span class="tag tag-{{ $r['status'] }}">{{ strtoupper($r['status']) }}</span>
                    @if($r['recorded_at'])
                        <div class="muted" style="font-size: 8px; margin-top: 2px;">{{ $r['recorded_at'] }}</div>
                        <div class="muted" style="font-size: 8px;">{{ $r['recorded_by'] }}</div>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{-- Overall notes --}}
    @if($execution->overall_notes)
        <h2>Overall Notes</h2>
        <div style="padding: 8px 10px; background: #F5F4F9; border-left: 3px solid #4F46E5; white-space: pre-line; border-radius: 6px;">{{ $execution->overall_notes }}</div>
    @endif

    {{-- Signatures --}}
    <h2>Signatures &amp; Verification</h2>
    <div class="grid-2">
        <div>
            <h3>Executed By</h3>
            <div class="sig-box">
                <div><strong>{{ $execution->starter?->name ?? '—' }}</strong></div>
                <div class="muted">{{ $execution->starter?->email }}</div>
                <div class="muted">Started: {{ $execution->started_at?->format('M d, Y g:i A') }}</div>
                <div class="muted">Completed: {{ $execution->completed_at?->format('M d, Y g:i A') ?? '—' }}</div>
            </div>
        </div>
        <div>
            <h3>Witness / Cx Authority</h3>
            @if($execution->witness_signed_at)
                <div class="sig-box">
                    @if($execution->witness_signature_image)
                        <img class="sig-image" src="{{ $execution->witness_signature_image }}" alt="signature">
                    @endif
                    <div><strong>{{ $execution->witness?->name ?? '—' }}</strong></div>
                    <div class="muted">{{ $execution->witness?->email }}</div>
                    <div class="muted">Signed: {{ $execution->witness_signed_at->format('M d, Y g:i A T') }}</div>
                    @if($execution->witness_signature_ip)
                        <div class="muted">From: {{ $execution->witness_signature_ip }}</div>
                    @endif
                    <div class="hash"><strong>SHA-256:</strong> {{ $execution->witness_signature_hash }}</div>
                    @if($witness_signature_valid !== null)
                        <div style="margin-top: 4px; font-size: 9px; color: {{ $witness_signature_valid ? '#10B981' : '#EF4444' }};">
                            {{ $witness_signature_valid ? '✓ Signature integrity verified' : '✗ Signature integrity FAILED verification' }}
                        </div>
                    @endif
                </div>
            @else
                <div class="sig-box muted">Not witnessed.</div>
            @endif
        </div>
    </div>

    <div class="footer">
        Generated by NexusOps on {{ $generated_at }} · Execution #{{ $execution->id }}
        @if($parent) · Retest-of #{{ $parent->id }} @endif
    </div>
</div>
</body>
</html>
