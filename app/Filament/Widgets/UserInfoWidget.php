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
        
        return [
            Stat::make('Selamat datang, ' . $user['name'] . '!', $currentDate)
                ->description('PN: ' . $user['pn'] . ' | Role: ' . ucfirst($user['role']))
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('primary')
                ->extraAttributes([
                    'class' => 'text-center',
                ]),
        ];
    }

    protected function getColumns(): int
    {
        return 1;
    }
}
