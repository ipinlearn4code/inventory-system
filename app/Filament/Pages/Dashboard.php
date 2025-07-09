<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    public function getColumns(): int|string|array
    {
        return [
            'default' => 1,
            'sm' => 1,
            'md' => 2,
            'lg' => 3,
            'xl' => 4,
            '2xl' => 4,
        ];
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\UserInfoWidget::class,
            \App\Filament\Widgets\GlobalFilterWidget::class,
            \App\Filament\Widgets\InventoryOverviewWidget::class,

            \App\Filament\Widgets\DeviceConditionChartWidget::class,
            \App\Filament\Widgets\DeviceDistributionChartWidget::class,

            \App\Filament\Widgets\DevicesNeedAttentionWidget::class,

            \App\Filament\Widgets\ActivityLogWidget::class,
            \App\Filament\Widgets\QuickActionsWidget::class,

        ];
    }
}
