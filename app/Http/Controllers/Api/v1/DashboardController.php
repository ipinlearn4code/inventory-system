<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Contracts\DashboardServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardServiceInterface $dashboardService
    ) {}

    /**
     * Get dashboard KPIs
     */
    public function kpis(Request $request): JsonResponse
    {
        $branchId = $request->input('branchId');
        $kpis = $this->dashboardService->getKpis($branchId);
        $activityLog = $this->dashboardService->getActivityLog();

        return response()->json([
            'data' => array_merge($kpis, ['activityLog' => $activityLog])
        ]);
    }

    /**
     * Get dashboard chart data
     */
    public function charts(Request $request): JsonResponse
    {
        $branchId = $request->input('branchId');
        $chartData = $this->dashboardService->getChartData($branchId);

        return response()->json([
            'data' => $chartData
        ]);
    }
}
