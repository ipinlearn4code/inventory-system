<?php

namespace App\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Device;

interface DeviceRepositoryInterface
{
    public function findById(int $id): ?Device;
    
    public function getAll(): Collection;
    
    public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    
    public function create(array $data): Device;
    
    public function update(int $id, array $data): Device;
    
    public function delete(int $id): bool;
    
    public function getAvailableDevices(): Collection;
    
    public function getDevicesByCondition(string $condition): Collection;
    
    public function getDevicesWithCurrentAssignment(): Collection;
    
    public function countByCondition(): Collection;
    
    public function getDeviceHistory(int $deviceId): Collection;
    
    public function findByAssetCodeWithRelations(string $assetCode): ?Device;
}
