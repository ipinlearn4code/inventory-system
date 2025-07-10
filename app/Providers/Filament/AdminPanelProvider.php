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
            ->id('admin')
            ->path('admin')
            ->login(false) // Still disable Filament's login page
            ->colors([
                'primary' => [
                    50 => '#f8fafd',   // Background Base - Lighter
                    100 => '#cedaeb',  // Border/Neutral - More contrast
                    200 => '#a6c1e0',  // Light blue with more saturation
                    300 => '#729fcf',  // Medium blue with better contrast
                    400 => '#2e7ac8',  // Medium-dark blue with better contrast
                    500 => '#00407b',  // Primary Color (Darker BRI Blue)
                    600 => '#0d5ca0',  // Secondary Blue with better contrast
                    700 => '#0a4c85',  // Darker blue
                    800 => '#083c6b',  // Very dark blue
                    900 => '#062e53',  // Near black blue
                    950 => '#041d34',  // Almost black with blue tint
                ],
                'success' => [
                    50 => '#edfcf2',
                    100 => '#d1f0dd',
                    500 => '#0a6b3d',  // Darker success green
                    600 => '#085c34',  // Even darker green
                ],
                'warning' => [
                    50 => '#fff8e6',
                    100 => '#ffefc2',
                    500 => '#c98000',  // Darker amber for warning
                    600 => '#a36900',  // Even darker amber
                ],
                'danger' => [
                    50 => '#feecee',
                    100 => '#fad3d7',
                    500 => '#c2293a',  // Darker red for danger/error
                    600 => '#a12230',  // Even darker red
                ],
                'info' => [
                    50 => '#eaf5fd',
                    100 => '#cce4f6',
                    500 => '#0367a6',  // Darker info blue
                    600 => '#02558a',  // Even darker info blue
                ],
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
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
            fn (): View => view('components.user-menu'),
        );

        // Add BRI Blue Theme CSS for comprehensive styling using AssetHelper to handle versioning
        // Use Filament's default CSS (remove custom dashboard-theme-improved.css)
        // No need to register additional CSS or JS here for default styling.
    }
}
