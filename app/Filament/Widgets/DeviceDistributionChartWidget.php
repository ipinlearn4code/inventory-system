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
        $borderColors = [
            'rgba(59, 130, 246, 0.8)',
            'rgba(239, 68, 68, 0.8)',
            'rgba(16, 185, 129, 0.8)',
            'rgba(245, 158, 11, 0.8)',
            'rgba(139, 92, 246, 0.8)',
            'rgba(6, 182, 212, 0.8)',
            'rgba(249, 115, 22, 0.8)',
            'rgba(132, 204, 22, 0.8)',
            'rgba(236, 72, 153, 0.8)',
            'rgba(99, 102, 241, 0.8)',
            'rgba(20, 184, 166, 0.8)',
            'rgba(244, 63, 94, 0.8)'
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
                'borderColor' => $borderColors[$colorIndex % count($borderColors)],
                'backgroundColor' => str_replace('0.8', '0.3', $borderColors[$colorIndex % count($borderColors)]),
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
