<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OptimizationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Prevent lazy loading in production
        if ($this->app->environment('production')) {
            Model::preventLazyLoading();
        }

        // Log slow queries in development
        if ($this->app->environment('local')) {
            DB::listen(function ($query) {
                if ($query->time > 100) { // Log queries slower than 100ms
                    Log::warning('Slow query detected', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time . 'ms'
                    ]);
                }
            });
        }

        // Cache configuration values that are frequently accessed
        $this->cacheConfigValues();
    }

    /**
     * Cache frequently accessed configuration values
     */
    private function cacheConfigValues(): void
    {
        // Skip caching in Octane environment to avoid Redis facade conflicts
        if (config('octane.server') === 'swoole') {
            return;
        }
        
        // Cache app configuration for 1 day
        Cache::remember('app_config', 86400, function () {
            return [
                'name' => config('app.name'),
                'url' => config('app.url'),
                'env' => config('app.env'),
                'debug' => config('app.debug'),
            ];
        });

        // Cache filesystem configuration for 1 day
        Cache::remember('filesystem_config', 86400, function () {
            return [
                'default' => config('filesystems.default'),
                'disks' => config('filesystems.disks'),
            ];
        });
    }
}
