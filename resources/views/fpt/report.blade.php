<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>FPT Report — {{ $execution->test_script_name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a202c; margin: 0; }
        .page { padding: 24px 32px; }
        h1 { font-size: 20px; margin: 0 0 4px; color: #111827; }
        h2 { font-size: 13px; margin: 18px 0 8px; color: #1f2937; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        h3 { font-size: 11px; margin: 12px 0 4px; color: #374151; }

        .muted { color: #6b7280; }
        .tag { display: inline-block; padding: 1px 6px; border-radius: 4px; font-size: 9px; font-weight: 600; text-transform: uppercase; }
        .tag-pass { background: #d1fae5; color: #065f46; }
        .tag-fail { background: #fee2e2; color: #991b1b; }
        .tag-pending { background: #e5e7eb; color: #374151; }
        .tag-skipped { background: #f3f4f6; color: #6b7280; }
        .tag-na { background: #f3f4f6; color: #6b7280; }
        .tag-l1 { background: #dbeafe; color: #1e40af; }
        .tag-l2 { background: #dbeafe; color: #1e40af; }
        .tag-l3 { background: #fef3c7; color: #92400e; }
        .tag-l4 { background: #fecaca; color: #991b1b; }
        .tag-l5 { background: #e9d5ff; color: #6b21a8; }

        .hero {
            background: #111827; color: white; padding: 18px 22px; border-radius: 6px;
            margin-bottom: 18px; display: table; width: 100%;
        }
        .hero-left, .hero-right { display: table-cell; vertical-align: middle; }
        .hero-right { text-align: right; }
        .hero-title { font-size: 18px; font-weight: 700; }
        .hero-sub { font-size: 10px; color: #9ca3af; margin-top: 4px; }
        .hero-stat-value { font-size: 32px; font-weight: 800; }
        .hero-stat-label { font-size: 9px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.1em; }

        table { width: 100%; border-collapse: collapse; }
        th { background: #f9fafb; text-align: left; padding: 6px 8px; font-size: 9px; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
        td { padding: 6px 8px; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
        tr.row-fail td { background: #fff7f7; }
        tr.row-pass td { background: #f9fefb; }

        .grid-2 { display: table; width: 100%; margin-bottom: 6px; }
        .grid-2 > div { display: table-cell; width: 50%; padding-right: 10px; vertical-align: top; }

        .summary-grid { display: table; width: 100%; margin: 10px 0; }
        .summary-cell {
            display: table-cell; width: 20%; text-align: center; padding: 8px;
            border-right: 1px solid #e5e7eb;
        }
        .summary-cell:last-child { border-right: none; }
        .summary-value { font-size: 18px; font-weight: 700; color: #111827; }
        .summary-label { font-size: 8px; text-transform: uppercase; color: #6b7280; letter-spacing: 0.1em; }

        .sig-box {
            border: 1px solid #e5e7eb; border-radius: 4px; padding: 10px 12px;
            margin-top: 6px; background: #f9fafb;
        }
        .sig-image { max-width: 240px; max-height: 80px; border: 1px solid #d1d5db; background: white; padding: 4px; }
        .hash { font-family: monospace; font-size: 8px; word-break: break-all; color: #6b7280; margin-top: 4px; }

        .footer {
            margin-top: 20px; padding-top: 8px; border-top: 1px solid #e5e7eb;
            font-size: 8px; color: #9ca3af;
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
        'passed' => ['bg' => '#d1fae5', 'border' => '#059669', 'text' => '#065f46', 'label' => 'PASSED'],
        'failed' => ['bg' => '#fee2e2', 'border' => '#dc2626', 'text' => '#991b1b', 'label' => 'FAILED'],
        'aborted' => ['bg' => '#f3f4f6', 'border' => '#6b7280', 'text' => '#374151', 'label' => 'ABORTED'],
    ][$execution->status] ?? ['bg' => '#fef3c7', 'border' => '#d97706', 'text' => '#92400e', 'label' => strtoupper(str_replace('_', ' ', $execution->status))])
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
            <div class="summary-value" style="color: #059669">{{ $execution->pass_count }}</div>
            <div class="summary-label">Pass</div>
        </div>
        <div class="summary-cell">
            <div class="summary-value" style="color: #dc2626">{{ $execution->fail_count }}</div>
            <div class="summary-label">Fail</div>
        </div>
        <div class="summary-cell">
            <div class="summary-value" style="color: #6b7280">{{ $execution->pending_count }}</div>
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
                        <div style="font-size: 9px; color: #991b1b; margin-top: 2px;">
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
                        <div style="font-size: 8px; color: #1e40af;">auto-evaluated</div>
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
        <div style="padding: 8px 10px; background: #f9fafb; border-left: 3px solid #d1d5db; white-space: pre-line;">{{ $execution->overall_notes }}</div>
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
                        <div style="margin-top: 4px; font-size: 9px; color: {{ $witness_signature_valid ? '#059669' : '#dc2626' }};">
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
