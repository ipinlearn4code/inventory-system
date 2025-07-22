<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\DeviceRepositoryInterface;
use App\Contracts\DeviceAssignmentRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Contracts\DashboardServiceInterface;
use App\Repositories\DeviceRepository;
use App\Repositories\DeviceAssignmentRepository;
use App\Repositories\UserRepository;
use App\Services\DashboardService;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(DeviceRepositoryInterface::class, DeviceRepository::class);
        $this->app->bind(DeviceAssignmentRepositoryInterface::class, DeviceAssignmentRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(DashboardServiceInterface::class, DashboardService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
