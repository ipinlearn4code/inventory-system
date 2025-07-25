<?php

namespace App\Filament\Widgets;

use App\Services\StorageHealthService;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;

class StorageStatusWidget extends Widget
{
    protected static string $view = 'filament.widgets.storage-status-widget';

    protected int | string | array $columnSpan = [
        'default' => 'full',  // Full width on mobile
        'sm' => 'full',       // Full width on small screens  
        'md' => 1,       // Full width on medium screens (important system status)
        'lg' => 2,            // 2 out of 4 columns on large screens
        'xl' => 2,            // 2 out of 4 columns on XL screens (maintain consistency)
        '2xl' => 2,           // 3 out of 6 columns on ultra-wide screens
    ];

    protected static ?int $sort = 4;

    public function getViewData(): array
    {
        $healthService = app(StorageHealthService::class);
        $storageStatus = $healthService->getStorageStatusSummary();

        return [
            'storageStatus' => $storageStatus,
            'details' => $storageStatus['details'],
        ];
    }

    public function refreshStatus(): void
    {
        $healthService = app(StorageHealthService::class);
        $healthService->refreshStorageHealth();
        
        $this->dispatch('$refresh');
    }

    public function openStorageModal(): void
    {
        $this->dispatch('open-modal', id: 'storage-info-modal');
    }
}
