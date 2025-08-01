<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\BaseApiController;
use App\Contracts\DashboardServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardController extends BaseApiController
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

        return $this->successResponse(array_merge($kpis, ['activityLog' => $activityLog]));
    }

    /**
     * Get dashboard chart data
     */
    public function charts(Request $request): JsonResponse
    {
        $branchId = $request->input('branchId');
        $chartData = $this->dashboardService->getChartData($branchId);

        return $this->successResponse($chartData);
    }
}
