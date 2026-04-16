<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    /**
     * Portfolio summary: all projects for the authenticated user's tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        $summary = $this->dashboardService->getPortfolioSummary($tenantId);

        return response()->json([
            'data' => $summary,
            'meta' => ['tenant_id' => $tenantId],
        ]);
    }

    /**
     * Single project readiness score and blockers.
     */
    public function projectReadiness(Request $request, int $projectId): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        $readiness = $this->dashboardService->getProjectReadiness($tenantId, $projectId);

        if ($readiness === null) {
            return response()->json([
                'data' => null,
                'meta' => ['error' => 'Project not found.'],
            ], 404);
        }

        return response()->json([
            'data' => $readiness,
            'meta' => ['tenant_id' => $tenantId],
        ]);
    }

    /**
     * Tenant-wide KPI metrics.
     */
    public function kpis(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        $kpis = $this->dashboardService->getKpis($tenantId);

        return response()->json([
            'data' => $kpis,
            'meta' => [
                'tenant_id' => $tenantId,
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Sensor status and anomalies overview.
     */
    public function sensorOverview(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        $overview = $this->dashboardService->getSensorOverview($tenantId);

        return response()->json([
            'data' => $overview,
            'meta' => [
                'tenant_id' => $tenantId,
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
