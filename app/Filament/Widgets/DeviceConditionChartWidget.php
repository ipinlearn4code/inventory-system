<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use App\Filament\Widgets\OptimizedChartWidget;

class DeviceConditionChartWidget extends OptimizedChartWidget
{
    protected static ?string $heading = '📊 Komposisi Kondisi Perangkat';
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = [
        'default' => 1,  // Full width on mobile
        'md' => 1,       // Full width on medium (single chart gets full space)
        'lg' => 2,            // 2 out of 4 columns on large screens (side-by-side with other chart)
        'xl' => 2,            // Keep 2 out of 4 on XL (optimal chart size)
        '2xl' => 2,           // 2 out of 6 on ultra-wide (more breathing room)
    ];

    protected static ?string $maxHeight = '300px';
    

    protected function getChartData(): array
    {
        // Get filter values from session
        $mainBranchId = session('dashboard_main_branch_filter');
        $branchId = session('dashboard_branch_filter');

        // Base query for devices
        $deviceQuery = Device::query();

        // Apply filters
        if ($branchId) {
            $deviceQuery->whereHas('currentAssignment', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            });
        } elseif ($mainBranchId) {
            $deviceQuery->whereHas('currentAssignment.branch', function ($query) use ($mainBranchId) {
                $query->where('main_branch_id', $mainBranchId);
            });
        }

        $conditions = $deviceQuery->selectRaw('`condition`, COUNT(*) as count')
            ->groupBy('condition')
            ->pluck('count', 'condition')
            ->toArray();

        $labels = [];
        $data = [];
        $colors = [];

        foreach (['Baik', 'Perlu Pengecekan', 'Rusak'] as $condition) {
            $count = $conditions[$condition] ?? 0;
            if ($count > 0) {
                $labels[] = $condition;
                $data[] = $count;
                $colors[] = match ($condition) {
                    'Baik' => '#10b981',
                    'Perlu Pengecekan' => '#f59e0b',
                    'Rusak' => '#ef4444',
                    default => '#6b7280'
                };
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Kondisi Perangkat',
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getChartType(): string
    {
        return 'doughnut';
    }

    protected function getChartOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
