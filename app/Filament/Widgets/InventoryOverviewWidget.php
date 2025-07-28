<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use App\Models\DeviceAssignment;
use App\Models\User;
use App\Models\Bribox;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventoryOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = [
        'default' => 2,  // Full width on mobile for better readability
        'sm' => 'full',       // Full width on small tablets
        'md' => 'full',       // Full width on medium screens (key metrics need space) 
        'lg' => 'full',       // Full width on large screens (stats cards need room)
        'xl' => 'full',       // Keep full width on XL (stats display better)
        '2xl' => 'full',           // Use 4 out of 6 columns on ultra-wide only
    ];

    protected function getStats(): array
    {
        // Get filter values from session
        $mainBranchId = session('dashboard_main_branch_filter');
        $branchId = session('dashboard_branch_filter');

        // Base query
        $deviceQuery = Device::query();
        $deviceAssignmentQuery = DeviceAssignment::query();
        $userQuery = User::query();
        $briboxQuery = Bribox::query();

        // Apply filters based on device assignments
        // if ($branchId) {
        //     $deviceQuery->whereHas('currentAssignment', function ($query) use ($branchId) {
        //         $query->where('branch_id', $branchId);
        //     });
        // } elseif ($mainBranchId) {
        //     $deviceQuery->whereHas('currentAssignment.branch', function ($query) use ($mainBranchId) {
        //         $query->where('main_branch_id', $mainBranchId);
        //     });
        // }

        if ($branchId) {
            $deviceQuery->whereHas('currentAssignment', fn($q) => $q->where('branch_id', $branchId));
            $deviceAssignmentQuery->where('branch_id', $branchId);
            $userQuery->where('branch_id', $branchId);
        } elseif ($mainBranchId) {
            $deviceQuery->whereHas('currentAssignment.branch', fn($q) => $q->where('main_branch_id', $mainBranchId));
            $deviceAssignmentQuery->whereHas('branch', fn($q) => $q->where('main_branch_id', $mainBranchId));
            $userQuery->whereHas('branch', fn($q) => $q->where('main_branch_id', $mainBranchId));
        }

        // Get totals
        $totalDevices = $deviceQuery->count();

        // $usedDevices = $deviceQuery->clone()->whereHas('currentAssignment', function ($query) {
        //     $query->whereNull('returned_date');
        // })->count();

        // $availableDevices = $totalDevices - $usedDevices;

        $deviceAssignmentCount = $deviceAssignmentQuery
            ->where(function ($query) {
            $query->whereNull('returned_date')
                  ->orWhere('returned_date', '');
            })
            ->count();


        $userCount = $userQuery->count();

        $userAsignedCount = $userQuery->whereHas('currentDeviceAssignment')->count();

        $userAssignmentRatio = $userAsignedCount. ' / ' . $userCount;

        $briboxCount = $briboxQuery->count();

        // $damagedDevices = $deviceQuery->clone()->where('condition', 'Rusak')->count();

        // $needsCheckDevices = $deviceQuery->clone()->where('condition', 'Perlu Pengecekan')->count();

        return [
            Stat::make('Total Devices', number_format($totalDevices))
                ->description('All devices in inventory')
                ->descriptionIcon('heroicon-m-computer-desktop')
                ->color('primary')
                ->chart([7, 12, 8, 15, 10, 18, 12])
                ->url(\App\Filament\Resources\DeviceResource::getUrl('index'))
                ->extraAttributes([
                    'style' => 'cursor: pointer;',
                    'title' => 'Click to view all devices',
                ]),

            Stat::make('Devices In Use', number_format($deviceAssignmentCount))
                ->description('Currently assigned devices')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('success')
                ->chart([12, 15, 18, 20, 25, 22, 28])
                ->url(\App\Filament\Resources\DeviceAssignmentResource::getUrl('index') . '?tableFilters[active][value]=true')
                ->extraAttributes([
                    'style' => 'cursor: pointer;',
                    'title' => 'Click to view devices in use',
                ]),

            Stat::make('Users', ($userAssignmentRatio))
                ->description('Users with assigned devices')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info')
                ->chart([8, 5, 12, 10, 15, 8, 12])
                ->url(\App\Filament\Resources\UserResource::getUrl('index'))
                ->extraAttributes([
                    'style' => 'cursor: pointer;',
                    'title' => 'Click to view all users',
                ]),

            Stat::make('Bribox', number_format($briboxCount))
                ->description('Total bribox in system')
                ->descriptionIcon('heroicon-o-squares-2x2')
                ->color('danger')
                ->chart([2, 4, 3, 5, 2, 6, 4])
                ->url(\App\Filament\Resources\BriboxResource::getUrl('index'))
                ->extraAttributes([
                    'style' => 'cursor: pointer;',
                    'title' => 'Click to view bribox data',
                ]),
        ];
    }

    protected function getColumns(): int
    {
        // Return the number of columns for the widget (Filament expects an integer)
        return 4; // 4 columns for medium and up, adjust as needed
    }
}
