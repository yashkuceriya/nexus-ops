<?php

declare(strict_types=1);

namespace App\Services\TestExecution;

use App\Models\TestExecution;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;

/**
 * Produces the signed PDF record of a single FPT execution.
 *
 * The generated document is intended to be the authoritative paper trail
 * owners hand to auditors: every step, every measurement, every auto-opened
 * deficiency, the witness signature image (if captured), and the SHA-256
 * integrity hash for later verification.
 */
final class TestReportService
{
    public function __construct(
        private readonly TestExecutionService $executionService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function buildPayload(TestExecution $execution): array
    {
        $execution->loadMissing([
            'script',
            'asset:id,name,asset_tag,serial_number,model_number,manufacturer,project_id,location_id',
            'asset.location:id,name',
            'project:id,name',
            'starter:id,name,email',
            'witness:id,name,email',
            'cxAgent:id,name,email',
            'results' => fn ($q) => $q->orderBy('step_sequence'),
            'results.issue:id,title,priority,status',
            'parent:id,test_script_name,test_script_version,status',
        ]);

        $results = $execution->results->map(function ($r) {
            return [
                'sequence' => $r->step_sequence,
                'title' => $r->step_title,
                'instruction' => $r->step_instruction,
                'measurement_type' => $r->measurement_type,
                'expected_value' => $r->expected_value,
                'measurement_unit' => $r->measurement_unit,
                'acceptable_min' => $r->acceptable_min,
                'acceptable_max' => $r->acceptable_max,
                'status' => $r->status,
                'measured_value' => $r->measured_value,
                'measured_numeric' => $r->measured_numeric,
                'notes' => $r->notes,
                'auto_evaluated' => (bool) $r->auto_evaluated,
                'recorded_by' => $r->recorder?->name,
                'recorded_at' => $r->recorded_at?->format('M d, Y \a\t g:i A'),
                'issue_id' => $r->issue_id,
                'issue_title' => $r->issue?->title,
                'issue_priority' => $r->issue?->priority,
            ];
        })->values()->all();

        $durationMinutes = null;
        if ($execution->started_at && $execution->completed_at) {
            $durationMinutes = (int) round($execution->completed_at->diffInSeconds($execution->started_at) / 60);
        }

        return [
            'execution' => $execution,
            'project' => $execution->project,
            'asset' => $execution->asset,
            'results' => $results,
            'result_count' => count($results),
            'pass_rate' => $execution->total_count > 0
                ? (int) round(($execution->pass_count / max($execution->total_count, 1)) * 100)
                : 0,
            'duration_minutes' => $durationMinutes,
            'witness_signature_valid' => $execution->witness_signature_hash
                ? $this->executionService->verifyWitnessSignature($execution)
                : null,
            'parent' => $execution->parent,
            'generated_at' => Carbon::now()->format('M d, Y \a\t g:i A T'),
            'generated_at_iso' => Carbon::now()->toIso8601String(),
        ];
    }

    public function render(TestExecution $execution): string
    {
        $payload = $this->buildPayload($execution);

        $pdf = Pdf::loadView('fpt.report', $payload)
            ->setPaper('a4', 'portrait')
            ->setOption([
                'defaultFont' => 'sans-serif',
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
            ]);

        return $pdf->output();
    }

    public function filenameFor(TestExecution $execution): string
    {
        $slug = preg_replace('/[^A-Za-z0-9_-]+/', '_', $execution->test_script_name) ?? 'FPT';
        $asset = preg_replace('/[^A-Za-z0-9_-]+/', '_', $execution->asset?->name ?? 'asset') ?? 'asset';

        return sprintf(
            'FPT_%s_%s_%s_%s.pdf',
            trim($slug, '_'),
            trim($asset, '_'),
            $execution->id,
            Carbon::now()->format('Y-m-d'),
        );
    }
}
