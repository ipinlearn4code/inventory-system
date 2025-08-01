<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\DeviceRepositoryInterface;
use App\Contracts\DeviceAssignmentRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Contracts\AssignmentLetterRepositoryInterface;
use App\Contracts\BranchRepositoryInterface;
use App\Contracts\BriboxRepositoryInterface;
use App\Contracts\DashboardServiceInterface;
use App\Contracts\FormOptionsServiceInterface;
use App\Contracts\InventoryLogServiceInterface;
use App\Repositories\DeviceRepository;
use App\Repositories\DeviceAssignmentRepository;
use App\Repositories\UserRepository;
use App\Repositories\AssignmentLetterRepository;
use App\Repositories\BranchRepository;
use App\Repositories\BriboxRepository;
use App\Services\DashboardService;
use App\Services\FormOptionsService;
use App\Services\InventoryLogService;

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
        $this->app->bind(AssignmentLetterRepositoryInterface::class, AssignmentLetterRepository::class);
        $this->app->bind(BranchRepositoryInterface::class, BranchRepository::class);
        $this->app->bind(BriboxRepositoryInterface::class, BriboxRepository::class);
        $this->app->bind(DashboardServiceInterface::class, DashboardService::class);
        $this->app->bind(FormOptionsServiceInterface::class, FormOptionsService::class);
        $this->app->bind(InventoryLogServiceInterface::class, InventoryLogService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
