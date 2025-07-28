<?php

namespace App\Providers;

use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Vite;
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
        FilamentAsset::register([
            Js::make('chart-js-plugins', Vite::asset('resources/js/filament-chart-js-plugins.js'))->module(),
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
