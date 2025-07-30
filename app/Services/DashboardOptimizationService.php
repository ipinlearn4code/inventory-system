<?php

namespace App\Services;

use Filament\Support\Assets\Js;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;

class DashboardOptimizationService
{
    private static bool $assetsRegistered = false;

    /**
     * Register optimized assets to prevent duplicate loading
     */
    public static function registerOptimizedAssets(): void
    {
        if (self::$assetsRegistered) {
            return;
        }

        $config = config('dashboard-optimization', []);

        // Register global asset manager
        FilamentAsset::register([
            Js::make('global-asset-manager', asset('js/global-asset-manager.js'))
                ->loadedOnRequest()
                ->defer(),
        ]);

        // Register shared Filament scripts to prevent duplicates
        if ($config['assets']['deduplicate_scripts'] ?? true) {
            self::registerSharedScripts();
        }

        self::$assetsRegistered = true;
    }

    /**
     * Register shared scripts that are commonly loaded multiple times
     */
    private static function registerSharedScripts(): void
    {
        $sharedScripts = [
            'support' => 'js/filament/support/support.js',
            'echo' => 'js/filament/filament/echo.js',
            'chart' => 'js/filament/widgets/components/chart.js',
            'file-upload' => 'js/filament/forms/components/file-upload.js',
            'rich-editor' => 'js/filament/forms/components/rich-editor.js',
            'markdown-editor' => 'js/filament/forms/components/markdown-editor.js',
        ];

        $assets = [];
        foreach ($sharedScripts as $name => $path) {
            if (file_exists(public_path($path))) {
                $assets[] = Js::make("shared-{$name}", asset($path))
                    ->loadedOnRequest()
                    ->defer();
            }
        }

        if (!empty($assets)) {
            FilamentAsset::register($assets);
        }
    }

    /**
     * Get optimization statistics
     */
    public static function getOptimizationStats(): array
    {
        $publicJsPath = public_path('js/filament');
        $stats = [
            'total_js_files' => 0,
            'total_size_kb' => 0,
            'large_files' => [],
            'optimization_enabled' => self::$assetsRegistered,
        ];

        if (!is_dir($publicJsPath)) {
            return $stats;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($publicJsPath)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'js') {
                $size = $file->getSize();
                $sizeKb = round($size / 1024, 2);
                
                $stats['total_js_files']++;
                $stats['total_size_kb'] += $sizeKb;

                // Files larger than 100KB
                if ($sizeKb > 100) {
                    $stats['large_files'][] = [
                        'name' => $file->getFilename(),
                        'path' => str_replace(public_path(), '', $file->getPathname()),
                        'size_kb' => $sizeKb,
                    ];
                }
            }
        }

        // Sort large files by size
        usort($stats['large_files'], fn($a, $b) => $b['size_kb'] <=> $a['size_kb']);

        return $stats;
    }

    /**
     * Generate optimization report
     */
    public static function generateOptimizationReport(): array
    {
        $stats = self::getOptimizationStats();
        
        $report = [
            'summary' => [
                'total_files' => $stats['total_js_files'],
                'total_size_mb' => round($stats['total_size_kb'] / 1024, 2),
                'large_files_count' => count($stats['large_files']),
                'optimization_status' => $stats['optimization_enabled'] ? 'Enabled' : 'Disabled',
            ],
            'largest_files' => array_slice($stats['large_files'], 0, 10),
            'recommendations' => self::getOptimizationRecommendations($stats),
        ];

        return $report;
    }

    /**
     * Get optimization recommendations based on current state
     */
    private static function getOptimizationRecommendations(array $stats): array
    {
        $recommendations = [];

        if (!$stats['optimization_enabled']) {
            $recommendations[] = [
                'type' => 'critical',
                'message' => 'Enable asset optimization by calling DashboardOptimizationService::registerOptimizedAssets()',
                'impact' => 'High - Prevents duplicate script loading'
            ];
        }

        if ($stats['total_size_kb'] > 2000) { // > 2MB
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'Total JavaScript size is quite large. Consider implementing lazy loading for non-critical components.',
                'impact' => 'Medium - Improves initial page load time'
            ];
        }

        foreach ($stats['large_files'] as $file) {
            if ($file['size_kb'] > 500) { // > 500KB
                $recommendations[] = [
                    'type' => 'info',
                    'message' => "Consider lazy loading for {$file['name']} ({$file['size_kb']}KB)",
                    'impact' => 'Low - Reduces initial bundle size'
                ];
            }
        }

        return $recommendations;
    }
}
