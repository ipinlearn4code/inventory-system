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
            'md' => 4,
            'lg' => 6,
            'xl' => 8,
            '2xl' => 12,
        ];
    }

    public function getWidgets(): array
    {
        return [
            // === TOP PRIORITY SECTION ===
            // User context and quick actions (Always visible first)
            \App\Filament\Widgets\UserInfoWidget::class,
            
            // === CONTROL SECTION ===
            // Essential filters for data control
            \App\Filament\Widgets\GlobalFilterWidget::class,
            
            // === KEY METRICS SECTION ===
            // Most important metrics for quick overview
            \App\Filament\Widgets\InventoryOverviewWidget::class,
            
            // === SYSTEM HEALTH SECTION ===
            // Critical system monitoring (Mobile: takes priority over charts)
            \App\Filament\Widgets\StorageStatusWidget::class,
            
            // === ALERTS & ATTENTION SECTION ===
            // Critical alerts that need immediate attention
            \App\Filament\Widgets\DevicesNeedAttentionWidget::class,
            
            // === ANALYTICS SECTION ===
            // Charts for data analysis (Mobile: lower priority, can scroll)
            \App\Filament\Widgets\DeviceConditionChartWidget::class,
            \App\Filament\Widgets\DeviceDistributionChartWidget::class,
            
            // === ACTIVITY SECTION ===
            // Historical data and logs (Lowest priority on mobile)
            \App\Filament\Widgets\ActivityLogWidget::class,
        ];
    }
}
