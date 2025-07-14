<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->brandName('BRI Inventory-System')
            ->id('admin')
            ->path('admin')
            ->login(false) // Still disable Filament's login page
            ->font('Poppins')
            ->darkMode(true) // Enable dark mode
            ->colors([
                'danger' => Color::Rose,
                'gray' => Color::Gray,
                'info' => Color::Blue,
                'primary' => Color::Indigo,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            // Custom navigation items (optional - uncomment to add)
            // ->navigation([
            //     NavigationItem::make('Reports')
            //         ->url('/admin/reports')
            //         ->icon('heroicon-o-chart-bar')
            //         ->group('Analytics')
            //         ->sort(7),
            // ])
            // ->discoverWidgets(false)
            ->widgets([
                \App\Filament\Widgets\UserInfoWidget::class,
                \App\Filament\Widgets\GlobalFilterWidget::class,
                \App\Filament\Widgets\InventoryOverviewWidget::class,
                \App\Filament\Widgets\DevicesNeedAttentionWidget::class,
                \App\Filament\Widgets\DeviceConditionChartWidget::class,
                \App\Filament\Widgets\DeviceDistributionChartWidget::class,
                \App\Filament\Widgets\ActivityLogWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])
            ->spa() // Enable SPA mode for better performance
            ->unsavedChangesAlerts() // Add unsaved changes alerts
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                \App\Http\Middleware\CustomAuth::class, // Only in auth middleware
            ]);
    }

    public function boot(): void
    {
        // Register the user menu component in the top bar
        FilamentView::registerRenderHook(
            PanelsRenderHook::TOPBAR_END,
            fn(): View => view('components.user-menu'),
        );

        // Add BRI Blue Theme CSS for comprehensive styling using AssetHelper to handle versioning
        // Use Filament's default CSS (remove custom dashboard-theme-improved.css)
        // No need to register additional CSS or JS here for default styling.
    }
}
