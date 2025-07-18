<?php

namespace App\Providers;

use App\Services\AuthenticationService;
use App\Services\MinioStorageService;
use App\Services\NotificationService;
use App\Services\QuickAssignmentFormBuilder;
use App\Services\QuickAssignmentService;
use App\Services\QuickAssignmentValidator;
use Illuminate\Support\ServiceProvider;

class QuickAssignmentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(AuthenticationService::class);
        $this->app->singleton(NotificationService::class);
        $this->app->singleton(QuickAssignmentValidator::class);
        
        $this->app->singleton(QuickAssignmentFormBuilder::class, function ($app) {
            return new QuickAssignmentFormBuilder(
                $app->make(AuthenticationService::class)
            );
        });
        
        $this->app->singleton(QuickAssignmentService::class, function ($app) {
            return new QuickAssignmentService(
                $app->make(MinioStorageService::class),
                $app->make(AuthenticationService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
