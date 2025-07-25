<?php

namespace App\Filament\Widgets;

use App\Models\Branch;
use App\Models\Device;
use Filament\Widgets\ChartWidget;

class DeviceDistributionChartWidget extends ChartWidget
{
    protected static ?string $heading = '🏢 Distribusi Perangkat per Cabang';    protected static ?int $sort = 7;

    protected int | string | array $columnSpan = [
        'default' => 1,  // Full width on mobile
        'md' => 1,       // Full width on medium (single chart gets full space)
        'lg' => 2,            // 2 out of 4 columns on large screens (side-by-side with condition chart)
        'xl' => 2,            // Keep 2 out of 4 on XL (optimal chart size)
        '2xl' => 2,           // 3 out of 6 on ultra-wide (more breathing room)
    ];

    protected function getData(): array
    {
        // Get filter values from session
        $mainBranchId = session('dashboard_main_branch_filter');
        $branchId = session('dashboard_branch_filter');

        $branchQuery = Branch::query()->with('mainBranch');
        
        // Apply main branch filter
        if ($mainBranchId) {
            $branchQuery->where('main_branch_id', $mainBranchId);
        }
        
        // If specific branch is selected, show only that branch
        if ($branchId) {
            $branchQuery->where('branch_id', $branchId);
        }

        $branches = $branchQuery->get();
        
        $labels = [];
        $data = [];
        $backgroundColors = [
            '#3b82f6', '#ef4444', '#10b981', '#f59e0b', 
            '#8b5cf6', '#06b6d4', '#f97316', '#84cc16',
            '#ec4899', '#6366f1', '#14b8a6', '#f43f5e'
        ];

        foreach ($branches as $index => $branch) {
            $deviceCount = Device::whereHas('currentAssignment', function ($query) use ($branch) {
                $query->where('branch_id', $branch->branch_id);
            })->count();
            
            if ($deviceCount > 0) {
                $labels[] = $branch->unit_name;
                $data[] = $deviceCount;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Perangkat',
                    'data' => $data,
                    'backgroundColor' => array_slice($backgroundColors, 0, count($data)),
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
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
