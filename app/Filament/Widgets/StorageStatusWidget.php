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
        'md' => 2,            // 2 out of 3 columns on medium screens
        'lg' => 2,            // 2 out of 4 columns on large screens  
        'xl' => 3,            // 3 out of 6 columns on extra large screens
        '2xl' => 3,           // 3 out of 6 columns on 2xl screens
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
}
