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
            Stat::make('� Selamat datang, ' . $user['name'] . '!', $currentDate . ' • ' . $currentTime)
                ->description('📋 PN: ' . $user['pn'] . ' • 👤 Role: ' . ucfirst($user['role']) . ' • 🔵 BRI Inventory Management System')
                ->color('secondary')
                ->extraAttributes([
                    'class' => 'text-center bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950 dark:to-indigo-950',
                    'style' => 'border: 3px solid #00529B; border-radius: 16px; background: linear-gradient(135deg, #F5F9FF 0%, #E0E6F0 100%);'
                ]),
        ];
    }

    protected function getColumns(): int
    {
        return 1;
    }
}
