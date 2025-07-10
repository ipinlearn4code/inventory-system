<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Welcome';

    public function getColumns(): int|string|array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'md' => 3,
            'lg' => 4,
            'xl' => 6,
            '2xl' => 6,
        ];
    }

    public function getWidgets(): array
    {
        return [
            // Header Section - Full Width
            \App\Filament\Widgets\UserInfoWidget::class,
            
            // Filter Section - Full Width
            \App\Filament\Widgets\GlobalFilterWidget::class,
            
            // Key Metrics - Top Priority Stats (4 columns)
            \App\Filament\Widgets\InventoryOverviewWidget::class,
            
            // Action & Charts Section (2 columns layout)
            \App\Filament\Widgets\QuickActionsWidget::class,
            \App\Filament\Widgets\DeviceConditionChartWidget::class,
            
            // Attention Alerts - Full Width for visibility
            \App\Filament\Widgets\DevicesNeedAttentionWidget::class,
            
            // Secondary Charts & Analytics (2 columns)
            \App\Filament\Widgets\DeviceDistributionChartWidget::class,
            \App\Filament\Widgets\ActivityLogWidget::class,
        ];
    }
}
