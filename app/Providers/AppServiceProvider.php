<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Filament::serving(
            function () {
                Filament::registerNavigationGroups(
                    [
                        NavigationGroup::make()
                            ->label('Dashboard')
                            ->icon('heroicon-s-squares-2x2'),
                        NavigationGroup::make()
                            ->label('Inventory Management')
                            ->collapsed()
                            ->icon('heroicon-s-archive-box'),
                        NavigationGroup::make()
                            ->label('Device Management')
                            ->collapsed()
                            ->icon('heroicon-s-device-phone-mobile'),
                        NavigationGroup::make()
                            ->label('Master Data')
                            ->collapsed()
                            ->icon('heroicon-s-table-cells'),
                        NavigationGroup::make()
                            ->label('User Management')
                            ->collapsed()
                            ->icon('heroicon-s-users'),
                        NavigationGroup::make()
                            ->label('Permission Management')
                            ->collapsed()
                            ->icon('heroicon-s-lock-closed'),
                    ]
                );
            }
        );
    }
}
