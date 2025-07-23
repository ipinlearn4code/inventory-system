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
        // Bind InventoryLogService interface
        $this->app->bind(
            \App\Contracts\InventoryLogServiceInterface::class,
            \App\Services\InventoryLogService::class
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
