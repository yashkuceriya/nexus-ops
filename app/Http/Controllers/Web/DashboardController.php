<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    /**
     * Render the main dashboard view.
     */
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        $portfolio = $this->dashboardService->getPortfolioSummary($tenantId);
        $kpis = $this->dashboardService->getKpis($tenantId);
        $sensorOverview = $this->dashboardService->getSensorOverview($tenantId);

        return view('dashboard', [
            'portfolio' => $portfolio,
            'kpis' => $kpis,
            'sensorOverview' => $sensorOverview,
        ]);
    }
}
