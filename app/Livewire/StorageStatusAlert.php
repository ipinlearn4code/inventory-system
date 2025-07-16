<?php

namespace App\Livewire;

use App\Services\StorageHealthService;
use Livewire\Component;

class StorageStatusAlert extends Component
{
    public $storageStatus = [];
    public $showAlert = false;
    public $dismissed = false;

    public function mount()
    {
        $this->checkStorageStatus();
    }

    public function checkStorageStatus()
    {
        $healthService = app(StorageHealthService::class);
        $this->storageStatus = $healthService->checkMinioHealth();
        
        // Show alert if storage is not healthy and not dismissed
        $this->showAlert = !$this->dismissed && ($this->storageStatus['status'] !== 'healthy');
    }

    public function dismissAlert()
    {
        $this->dismissed = true;
        $this->showAlert = false;
    }

    public function refreshStatus()
    {
        $healthService = app(StorageHealthService::class);
        $this->storageStatus = $healthService->refreshStorageHealth()['minio'];
        $this->showAlert = !$this->dismissed && ($this->storageStatus['status'] !== 'healthy');
        
        $this->dispatch('storage-status-refreshed', $this->storageStatus);
    }

    public function render()
    {
        return view('livewire.storage-status-alert');
    }
}
