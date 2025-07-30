<?php

namespace App\Filament\Widgets;

use App\Models\Branch;
use App\Models\Device;
use App\Filament\Widgets\OptimizedChartWidget;

class DeviceDistributionChartWidget extends OptimizedChartWidget
{
    protected static ?string $heading = '🏢 Distribusi Perangkat per Cabang';
    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = [
        'default' => 1,  // Full width on mobile
        'md' => 1,       // Full width on medium (single chart gets full space)
        'lg' => 2,            // 2 out of 4 columns on large screens (side-by-side with condition chart)
        'xl' => 2,            // Keep 2 out of 4 on XL (optimal chart size)
        '2xl' => 2,           // 3 out of 6 on ultra-wide (more breathing room)
    ];

    protected static ?string $maxHeight = '300px';

    protected function getChartData(): array
    {
        $mainBranchId = session('dashboard_main_branch_filter');
        $branchId = session('dashboard_branch_filter');

        $branchQuery = Branch::query()->with('mainBranch');

        if ($mainBranchId) {
            $branchQuery->where('main_branch_id', $mainBranchId);
        }

        if ($branchId) {
            $branchQuery->where('branch_id', $branchId);
        }

        $branches = $branchQuery->get();

        $branchNames = [];
        $branchIds = [];

        foreach ($branches as $branch) {
            $branchNames[$branch->branch_id] = $branch->unit_name;
            $branchIds[] = $branch->branch_id;
        }

        // Ambil semua device yang sedang aktif dan berada di branch yang disaring
        $devices = Device::with([
            'currentAssignment.branch',
            'bribox.category'
        ])->whereHas('currentAssignment', function ($query) use ($branchIds) {
            if (!empty($branchIds)) {
                $query->whereIn('branch_id', $branchIds);
            }
        })->get();

        // Group by category dan branch
        $categoryBranchCounts = [];
        foreach ($devices as $device) {
            $category = $device->bribox->category->category_name ?? 'Unknown';
            $assignedBranchId = $device->currentAssignment->branch_id ?? 'unassigned';

            if (!isset($categoryBranchCounts[$category])) {
                $categoryBranchCounts[$category] = [];
            }

            if (!isset($categoryBranchCounts[$category][$assignedBranchId])) {
                $categoryBranchCounts[$category][$assignedBranchId] = 0;
            }

            $categoryBranchCounts[$category][$assignedBranchId]++;
        }

        // Reorganize data for chart
        $branchCategoryCounts = [];
        foreach ($categoryBranchCounts as $categoryName => $branchCounts) {
            foreach ($branchCounts as $branchId => $count) {
                if (!isset($branchCategoryCounts[$categoryName])) {
                    $branchCategoryCounts[$categoryName] = [];
                }
                $branchCategoryCounts[$categoryName][$branchId] = $count;
            }
        }

        $labels = array_values($branchNames);
        $datasets = [];

        $backgroundColors = [
            '#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6',
            '#ec4899', '#06b6d4', '#84cc16', '#f97316', '#6366f1'
        ];

        $colorIndex = 0;
        foreach ($branchCategoryCounts as $categoryName => $branchCounts) {
            $data = [];

            foreach ($branchIds as $branchId) {
                $data[] = $branchCategoryCounts[$categoryName][$branchId] ?? 0;
            }

            $datasets[] = [
                'label' => $categoryName,
                'data' => $data,
                'backgroundColor' => $backgroundColors[$colorIndex % count($backgroundColors)],
                'borderColor' => '#ffffff',
                'borderWidth' => 2,
            ];

            $colorIndex++;
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }

    protected function getChartType(): string
    {
        return 'radar';
    }

    protected function getChartOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
