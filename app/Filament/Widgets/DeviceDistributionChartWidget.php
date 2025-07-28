<?php

namespace App\Filament\Widgets;

use App\Models\Branch;
use App\Models\Device;
use Filament\Widgets\ChartWidget;

class DeviceDistributionChartWidget extends ChartWidget
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

    protected function getData(): array
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
            'bribox.category',
            'currentAssignment' => fn($q) => $q->select('device_id', 'branch_id')
        ])
            ->whereHas('currentAssignment', fn($q) => $q->whereIn('branch_id', $branchIds))
            ->get();

        // Inisialisasi array hasil
        $categories = [];
        $branchCategoryCounts = [];

        foreach ($devices as $device) {
            $branchId = $device->currentAssignment?->branch_id;
            $categoryName = $device->bribox->category?->category_name ?? 'Tidak Diketahui';
            // dd($categoryName);
            if (!$branchId)
                continue;

            // Tambahkan nama category
            $categories[$categoryName] = true;

            // Hitung jumlah per kategori di setiap branch
            $branchCategoryCounts[$categoryName][$branchId] =
                ($branchCategoryCounts[$categoryName][$branchId] ?? 0) + 1;
        }

        // Finalisasi labels (branch names)
        $labels = array_values($branchNames);

        // Susun dataset per kategori
        $datasets = [];
        $backgroundColors = [
            '#3b82f6',
            '#ef4444',
            '#10b981',
            '#f59e0b',
            '#8b5cf6',
            '#06b6d4',
            '#f97316',
            '#84cc16',
            '#ec4899',
            '#6366f1',
            '#14b8a6',
            '#f43f5e'
        ];

        $colorIndex = 0;

        foreach (array_keys($categories) as $categoryName) {
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

        // dd($datasets, $labels);

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'radar';
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
