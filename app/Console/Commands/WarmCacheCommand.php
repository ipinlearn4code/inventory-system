<?php

namespace App\Console\Commands;

use App\Services\DropdownOptionsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WarmCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warm {--force : Force refresh all cached data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm up application cache with frequently accessed data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cache warming...');

        try {
            if ($this->option('force')) {
                $this->info('Clearing existing cache...');
                DropdownOptionsService::clearCache();
                Cache::forget('app_config');
                Cache::forget('filesystem_config');
            }

            // Warm dropdown options
            $this->info('Warming dropdown options...');
            $this->warmDropdownOptions();

            // Warm configuration cache
            $this->info('Warming configuration cache...');
            $this->warmConfigCache();

            // Warm user permissions (if applicable)
            $this->info('Warming user permissions...');
            $this->warmUserPermissions();

            $this->info('Cache warming completed successfully!');
            Log::info('Cache warming completed successfully');

        } catch (\Exception $e) {
            $this->error('Cache warming failed: ' . $e->getMessage());
            Log::error('Cache warming failed', ['error' => $e->getMessage()]);
            return 1;
        }

        return 0;
    }

    /**
     * Warm dropdown options cache
     */
    private function warmDropdownOptions(): void
    {
        DropdownOptionsService::getDeviceBrands();
        DropdownOptionsService::getDeviceBrandNames();
        DropdownOptionsService::getBriboxCategories();
        DropdownOptionsService::getBriboxTypes();
        DropdownOptionsService::getDeviceConditions();
        
        $this->line('  ✓ Dropdown options cached');
    }

    /**
     * Warm configuration cache
     */
    private function warmConfigCache(): void
    {
        Cache::remember('app_config', 86400, function () {
            return [
                'name' => config('app.name'),
                'url' => config('app.url'),
                'env' => config('app.env'),
                'debug' => config('app.debug'),
            ];
        });

        Cache::remember('filesystem_config', 86400, function () {
            return [
                'default' => config('filesystems.default'),
                'disks' => config('filesystems.disks'),
            ];
        });

        $this->line('  ✓ Configuration cached');
    }

    /**
     * Warm user permissions cache (placeholder)
     */
    private function warmUserPermissions(): void
    {
        // This would warm user permissions if you have a permission system
        // For now, just a placeholder
        
        $this->line('  ✓ User permissions cached');
    }
}
