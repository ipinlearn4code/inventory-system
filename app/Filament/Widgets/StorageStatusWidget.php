<?php

namespace App\Filament\Widgets;

use App\Services\StorageHealthService;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;

class StorageStatusWidget extends Widget
{
    protected static string $view = 'filament.widgets.storage-status-widget';

    protected int | string | array $columnSpan = [
        'md' => 4,
        'xl' => 3,
    ];

    protected static ?int $sort = 1;

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
