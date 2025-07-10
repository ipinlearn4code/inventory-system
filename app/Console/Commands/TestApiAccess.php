<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Auth;
use Illuminate\Support\Facades\Hash;

class TestApiAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:test-access';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test API access and verify setup';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing API Setup...');
        
        // Check if users exist
        $userCount = User::count();
        $this->info("Users in database: {$userCount}");
        
        // Check if auth records exist
        $authCount = Auth::count();
        $this->info("Auth records in database: {$authCount}");
        
        // Show sample user for testing
        $sampleUser = User::with('auth')->first();
        if ($sampleUser) {
            $this->info("Sample user for testing:");
            $this->line("  PN: {$sampleUser->pn}");
            $this->line("  Name: {$sampleUser->name}");
            $this->line("  Role: " . ($sampleUser->auth->role ?? 'No auth record'));
        } else {
            $this->warn("No users found in database");
        }
        
        // Test middleware registration
        $this->info("\nChecking middleware registration...");
        $middleware = app('router')->getMiddleware();
        $requiredMiddleware = ['api.timeout', 'role', 'api.cache'];
        
        foreach ($requiredMiddleware as $mw) {
            if (isset($middleware[$mw])) {
                $this->info("✓ {$mw} middleware registered");
            } else {
                $this->error("✗ {$mw} middleware not registered");
            }
        }
        
        // Test Sanctum
        if (class_exists(\Laravel\Sanctum\Sanctum::class)) {
            $this->info("✓ Laravel Sanctum is available");
        } else {
            $this->error("✗ Laravel Sanctum not found");
        }
        
        $this->info("\nAPI endpoints are configured and ready for testing!");
        $this->info("Use the API_TESTING_GUIDE.md file for testing instructions.");
        
        return Command::SUCCESS;
    }
}
