<?php

namespace App\Contracts;

interface DashboardServiceInterface
{
    public function getKpis(?int $branchId = null): array;
    
    public function getChartData(?int $branchId = null): array;
    
    public function getActivityLog(int $limit = 10): array;
}
