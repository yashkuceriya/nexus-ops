<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Asset;
use App\Models\MaintenanceSchedule;
use App\Models\Tenant;
use App\Models\WorkOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;

final class ReportPdfGenerator
{
    /**
     * Generate a monthly maintenance report PDF for the given tenant and date range.
     *
     * @return string PDF content as a raw string
     */
    public function generateMonthlyReport(int $tenantId, string $dateFrom, string $dateTo): string
    {
        $tenant = Tenant::find($tenantId);
        $tenantName = $tenant?->name ?? 'Unknown';

        $dateFromParsed = Carbon::parse($dateFrom);
        $dateToParsed = Carbon::parse($dateTo)->endOfDay();

        // KPI Summary — always scope by tenant to prevent data leaks across orgs.
        $query = WorkOrder::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$dateFromParsed, $dateToParsed]);

        $totalWo = (clone $query)->count();

        $completed = (clone $query)->whereNotNull('completed_at')
            ->whereNotNull('started_at')
            ->select(['id', 'started_at', 'completed_at'])
            ->get();

        $avgMttr = $completed->count() > 0
            ? round($completed->avg(fn ($wo) => $wo->started_at->diffInMinutes($wo->completed_at)) / 60, 1)
            : 0;

        $totalCost = (clone $query)->sum('actual_cost') ?: 0;

        $totalPm = MaintenanceSchedule::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->count();
        $completedPm = MaintenanceSchedule::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereNotNull('last_completed_date')
            ->count();
        $pmCompliance = $totalPm > 0 ? round(($completedPm / $totalPm) * 100, 1) : 100;

        $kpis = [
            'total_work_orders' => $totalWo,
            'avg_mttr_hours' => $avgMttr,
            'total_cost' => $totalCost,
            'pm_compliance' => $pmCompliance,
        ];

        // Work Orders by Type
        $woByType = WorkOrder::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$dateFromParsed, $dateToParsed])
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => [
                'type' => ucfirst($row->type ?? 'Unknown'),
                'count' => $row->count,
            ])
            ->toArray();

        // Top 10 Problem Assets
        $topAssets = Asset::where('assets.tenant_id', $tenantId)
            ->join('work_orders', 'work_orders.asset_id', '=', 'assets.id')
            ->whereBetween('work_orders.created_at', [$dateFromParsed, $dateToParsed])
            ->selectRaw('assets.name, COUNT(work_orders.id) as wo_count, COALESCE(SUM(work_orders.actual_cost), 0) as total_cost')
            ->groupBy('assets.id', 'assets.name')
            ->orderByDesc('wo_count')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->name,
                'wo_count' => $row->wo_count,
                'total_cost' => round((float) $row->total_cost, 2),
            ])
            ->toArray();

        $data = [
            'tenantName' => $tenantName,
            'dateFrom' => $dateFromParsed->format('M d, Y'),
            'dateTo' => Carbon::parse($dateTo)->format('M d, Y'),
            'generatedAt' => now()->format('M d, Y \a\t g:i A'),
            'kpis' => $kpis,
            'woByType' => $woByType,
            'topAssets' => $topAssets,
            'pmCompliance' => $pmCompliance,
        ];

        $pdf = Pdf::loadView('reports.pdf', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->output();
    }
}
