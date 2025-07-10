<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use App\Models\DeviceAssignment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventoryOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    
    protected function getStats(): array
    {
        // Get filter values from session
        $mainBranchId = session('dashboard_main_branch_filter');
        $branchId = session('dashboard_branch_filter');

        // Base query for devices
        $deviceQuery = Device::query();
        
        // Apply filters based on device assignments
        if ($branchId) {
            $deviceQuery->whereHas('currentAssignment', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            });
        } elseif ($mainBranchId) {
            $deviceQuery->whereHas('currentAssignment.branch', function ($query) use ($mainBranchId) {
                $query->where('main_branch_id', $mainBranchId);
            });
        }

        // Get totals
        $totalDevices = $deviceQuery->count();
        
        $usedDevices = $deviceQuery->clone()->whereHas('currentAssignment', function ($query) {
            $query->whereNull('returned_date');
        })->count();
        
        $availableDevices = $totalDevices - $usedDevices;
        
        $damagedDevices = $deviceQuery->clone()->where('condition', 'Rusak')->count();
        
        $needsCheckDevices = $deviceQuery->clone()->where('condition', 'Perlu Pengecekan')->count();

        return [
            Stat::make('Total Perangkat', number_format($totalDevices))
                ->description('Semua perangkat dalam sistem BRI')
                ->descriptionIcon('heroicon-m-computer-desktop')
                ->color('primary')
                ->chart([7, 12, 8, 15, 10, 18, 12])
                ->url(\App\Filament\Resources\DeviceResource::getUrl('index'))
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'title' => 'Klik untuk melihat semua perangkat',
                ]),
                
            Stat::make('Perangkat Digunakan', number_format($usedDevices))
                ->description('Sedang digunakan aktif')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('success')
                ->chart([12, 15, 18, 20, 25, 22, 28])
                ->url(\App\Filament\Resources\DeviceAssignmentResource::getUrl('index').'?tableFilters[active][value]=true')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'title' => 'Klik untuk melihat perangkat yang sedang digunakan',
                ]),
                
            Stat::make('Perangkat Tersedia', number_format($availableDevices))
                ->description('Siap untuk digunakan')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info')
                ->chart([8, 5, 12, 10, 15, 8, 12])
                ->url(\App\Filament\Resources\DeviceResource::getUrl('create'))
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'title' => 'Klik untuk membuat penugasan baru',
                ]),
                
            Stat::make('Perangkat Rusak', number_format($damagedDevices))
                ->description('Memerlukan perbaikan segera')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger')
                ->chart([2, 4, 3, 5, 2, 6, 4])
                ->url(\App\Filament\Resources\DeviceResource::getUrl('index').'?tableFilters[condition][value]=Rusak')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'title' => 'Klik untuk melihat perangkat rusak',
                ]),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
