<?php

namespace App\Providers;

use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;

class FilamentAssetProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register shared JavaScript assets to prevent duplicate loading
        FilamentAsset::register([
            // Core shared scripts
            Js::make('shared-chart-js', asset('js/filament/widgets/components/chart.js'))
                ->loadedOnRequest()
                ->defer(),
            
            Js::make('shared-support-js', asset('js/filament/support/support.js'))
                ->loadedOnRequest()
                ->defer(),
                
            Js::make('shared-echo-js', asset('js/filament/filament/echo.js'))
                ->loadedOnRequest()
                ->defer(),
                
            // QR Code scanner - load once globally
            Js::make('qr-scanner-global', asset('js/qrcode-field/qrcode-scanner.js'))
                ->loadedOnRequest()
                ->defer(),
        ]);
    }
}
