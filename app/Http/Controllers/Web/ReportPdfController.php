<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ReportPdfGenerator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

final class ReportPdfController extends Controller
{
    public function __construct(
        private readonly ReportPdfGenerator $pdfGenerator,
    ) {}

    /**
     * Export a monthly maintenance report as a downloadable PDF.
     */
    public function export(Request $request): Response
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $tenantId = $request->user()->tenant_id;
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $pdfContent = $this->pdfGenerator->generateMonthlyReport($tenantId, $dateFrom, $dateTo);

        $filename = sprintf(
            'NexusOps_Report_%s_to_%s.pdf',
            Carbon::parse($dateFrom)->format('Y-m-d'),
            Carbon::parse($dateTo)->format('Y-m-d'),
        );

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
