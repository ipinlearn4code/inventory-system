<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserInfoWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    
    protected function getStats(): array
    {
        $user = session('authenticated_user');
        
        if (!$user) {
            return [];
        }

        $currentDate = now()->locale('id')->translatedFormat('l, j F Y');
        $currentTime = now()->locale('id')->translatedFormat('H:i');
        
        return [
            Stat::make('🌟 Selamat datang, ' . $user['name'] . '!', $currentDate . ' • ' . $currentTime)
                ->description('📋 PN: ' . $user['pn'] . ' • 👤 Role: ' . ucfirst($user['role']) . ' • 🏢 Sistem Inventory Management')
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('primary')
                ->extraAttributes([
                    'class' => 'text-center bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950 dark:to-indigo-950',
                    'style' => 'border: 2px solid #3b82f6; border-radius: 12px;'
                ]),
        ];
    }

    protected function getColumns(): int
    {
        return 1;
    }
}
