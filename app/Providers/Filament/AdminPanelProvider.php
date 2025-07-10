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
                    50 => '#eff6ff',
                    100 => '#dbeafe',
                    200 => '#bfdbfe',
                    300 => '#93c5fd',
                    400 => '#60a5fa',
                    500 => '#3b82f6',
                    600 => '#2563eb',
                    700 => '#1d4ed8',
                    800 => '#1e40af',
                    900 => '#1e3a8a',
                    950 => '#172554',
                ],
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger' => Color::Red,
                'info' => Color::Cyan,
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
                \App\Filament\Widgets\QuickActionsWidget::class,
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

        // Add custom CSS for dashboard theme and performance
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => '<link rel="stylesheet" href="' . asset('css/dashboard-theme.css') . '">
            <style>
                /* Blue Theme Dashboard Enhancements */
                :root {
                    --primary-50: #eff6ff;
                    --primary-100: #dbeafe;
                    --primary-200: #bfdbfe;
                    --primary-300: #93c5fd;
                    --primary-400: #60a5fa;
                    --primary-500: #3b82f6;
                    --primary-600: #2563eb;
                    --primary-700: #1d4ed8;
                    --primary-800: #1e40af;
                    --primary-900: #1e3a8a;
                }
                
                /* Dashboard Header */
                .fi-topbar {
                    background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-700) 100%);
                    border-bottom: 2px solid var(--primary-500);
                }
                
                .fi-topbar-end-ctn { 
                    display: flex; 
                    align-items: center; 
                    gap: 1rem; 
                }
                
                /* Widget Container */
                .fi-da-widgets {
                    gap: 1.5rem;
                    padding: 1rem;
                }
                
                /* Stats Overview Widgets */
                .fi-wi-stats-overview {
                    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
                    border: 2px solid var(--primary-200);
                    border-radius: 16px;
                    padding: 1rem;
                }
                
                .dark .fi-wi-stats-overview {
                    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
                    border: 2px solid var(--primary-700);
                }
                
                /* Widget Animations */
                .fi-wi {
                    animation: fadeInUp 0.6s ease-out;
                    transition: all 0.3s ease;
                }
                
                .fi-wi:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 10px 25px rgba(59, 130, 246, 0.15);
                }
                
                @keyframes fadeInUp {
                    from { opacity: 0; transform: translateY(20px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                
                /* Section Headers */
                .fi-section-header {
                    margin-bottom: 1rem;
                }
                
                .fi-section-header-heading {
                    color: var(--primary-700);
                    font-weight: 600;
                    font-size: 1.125rem;
                }
                
                .dark .fi-section-header-heading {
                    color: var(--primary-300);
                }
                
                /* Chart Improvements */
                .fi-wi-chart {
                    min-height: 320px;
                    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
                    border: 2px solid var(--primary-200);
                    border-radius: 16px;
                }
                
                .dark .fi-wi-chart {
                    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
                    border: 2px solid var(--primary-700);
                }
                
                /* Table Improvements */
                .fi-wi-table {
                    border-radius: 16px;
                    overflow: hidden;
                    box-shadow: 0 4px 6px rgba(59, 130, 246, 0.1);
                }
                
                .fi-ta-table {
                    border-radius: 16px;
                }
                
                .fi-ta-header-cell {
                    background-color: var(--primary-50);
                    color: var(--primary-900);
                    font-weight: 600;
                }
                
                .dark .fi-ta-header-cell {
                    background-color: var(--primary-900);
                    color: var(--primary-100);
                }
                
                /* Responsive Improvements */
                @media (max-width: 768px) {
                    .fi-da-widgets {
                        padding: 0.5rem;
                        gap: 1rem;
                    }
                    
                    .fi-wi-stats-overview .fi-wi-stats-overview-stats {
                        grid-template-columns: 1fr;
                    }
                    
                    .fi-wi-chart {
                        min-height: 250px;
                    }
                }
                
                /* Loading States */
                [wire\\:loading] {
                    opacity: 0.6;
                    pointer-events: none;
                }
                
                /* Dropdown Z-Index */
                .fi-dropdown-panel {
                    z-index: 9999 !important;
                }
                
                /* Performance Optimizations */
                .fi-header, .fi-sidebar {
                    transform: translateZ(0);
                }
                
                .fi-ta-table {
                    will-change: auto;
                }
            </style>',
        );
    }
}
