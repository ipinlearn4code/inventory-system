<?php

namespace App\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use App\Models\DeviceAssignment;

interface DeviceAssignmentRepositoryInterface
{
    public function findById(int $id): ?DeviceAssignment;
    
    public function getAll(): Collection;
    
    public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    
    public function create(array $data): DeviceAssignment;
    
    public function update(int $id, array $data): DeviceAssignment;
    
    public function delete(int $id): bool;
    
    public function getActiveAssignments(): Collection;
    
    public function getAssignmentsByUser(int $userId): Collection;
    
    public function getAssignmentsByDevice(int $deviceId): Collection;
    
    public function getAssignmentsByBranch(int $branchId): Collection;
    
    public function returnDevice(int $assignmentId, array $data): DeviceAssignment;
    
    public function getUserActiveDevices(int $userId): Collection;
    
    public function getUserDeviceHistory(int $userId): Collection;
}
