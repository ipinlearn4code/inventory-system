<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserInfoWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = session('authenticated_user');
        
        if (!$user) {
            return [];
        }

        return [
            Stat::make('Logged in as', $user['name'])
                ->description('PN: ' . $user['pn'])
                ->descriptionIcon('heroicon-m-user')
                ->color('success'),
                
            Stat::make('Role', ucfirst($user['role']))
                ->description('Access Level')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('info'),
                
            Stat::make('Department', $user['department_id'])
                ->description('Department ID')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('warning'),
        ];
    }
}
