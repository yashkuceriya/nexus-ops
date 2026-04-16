<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\TestExecution;
use App\Services\TestExecution\TestReportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TestReportController extends Controller
{
    public function download(int $executionId, TestReportService $service): StreamedResponse
    {
        $execution = TestExecution::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($executionId);

        $pdfBytes = $service->render($execution);
        $filename = $service->filenameFor($execution);

        AuditLog::record(
            action: 'test_execution_pdf_downloaded',
            model: $execution,
            newValues: ['filename' => $filename],
        );

        return response()->streamDownload(
            fn () => print ($pdfBytes),
            $filename,
            [
                'Content-Type' => 'application/pdf',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }
}
