<?php

namespace App\Providers;

use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
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
        // Register shared assets to prevent duplicates
        FilamentAsset::register([
            // Chart.js - loaded once for all chart widgets
            Js::make('shared-chart', asset('js/filament/widgets/components/chart.js'))
                ->loadedOnRequest(),
            
            // Support.js - core Filament functionality
            Js::make('shared-support', asset('js/filament/support/support.js'))
                ->loadedOnRequest(),
                
            // Echo.js - real-time features
            Js::make('shared-echo', asset('js/filament/filament/echo.js'))
                ->loadedOnRequest(),
        ]);

        // Bind InventoryLogService interface
        $this->app->bind(
            \App\Contracts\InventoryLogServiceInterface::class,
            \App\Services\InventoryLogService::class
        );

        // Bind AuthService interface
        $this->app->bind(
            \App\Contracts\AuthServiceInterface::class,
            \App\Services\AuthService::class
        );
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
                            ->label('Dashboard'),
                        NavigationGroup::make()
                            ->label('Inventory Management')
                            ->collapsed(),
                        NavigationGroup::make()
                            ->label('Device Management')
                            ->collapsed(),
                        NavigationGroup::make()
                            ->label('Master Data')
                            ->collapsed(),
                        NavigationGroup::make()
                            ->label('User Management')
                            ->collapsed(),
                        NavigationGroup::make()
                            ->label('Settings')
                            ->collapsed(),
                    ]
                );

            }
        );
    }
}
