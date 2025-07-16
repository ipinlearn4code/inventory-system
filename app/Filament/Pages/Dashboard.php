<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Welcome';

    public function getColumns(): int|string|array
    {
        return [
            'default' => 1,    // Mobile: Single column for optimal readability
            'sm' => 2,         // Small tablets: 2 columns 
            'md' => 3,         // Medium screens: 3 columns (768px+)
            'lg' => 4,         // Large screens: 4 columns (1024px+) 
            'xl' => 4,         // Extra large: Keep 4 columns (1280px+)
            '2xl' => 6,        // Ultra wide: Max 6 columns (1536px+)
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
