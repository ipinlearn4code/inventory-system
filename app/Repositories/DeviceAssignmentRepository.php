<?php

namespace App\Repositories;

use App\Contracts\DeviceAssignmentRepositoryInterface;
use App\Models\DeviceAssignment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class DeviceAssignmentRepository implements DeviceAssignmentRepositoryInterface
{
    public function findById(int $id): ?DeviceAssignment
    {
        return DeviceAssignment::with(['device', 'user.branch', 'branch'])
            ->find($id);
    }

    public function getAll(): Collection
    {
        return DeviceAssignment::with(['device', 'user.department', 'branch'])->get();
    }

    public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = DeviceAssignment::with(['device', 'user.department', 'branch']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('device', function ($deviceQuery) use ($search) {
                    $deviceQuery->where('asset_code', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%");
                })->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('pn', 'like', "%{$search}%");
                });
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (!empty($filters['active_only'])) {
            $query->whereNull('returned_date');
        }

        return $query->orderBy('assigned_date', 'desc')->paginate($perPage);
    }

    public function create(array $data): DeviceAssignment
    {
        return DeviceAssignment::create($data);
    }

    public function update(int $id, array $data): DeviceAssignment
    {
        $assignment = DeviceAssignment::findOrFail($id);
        $assignment->update($data);
        return $assignment->fresh();
    }

    public function delete(int $id): bool
    {
        $assignment = DeviceAssignment::findOrFail($id);
        return $assignment->delete();
    }

    public function getActiveAssignments(): Collection
    {
        return DeviceAssignment::with(['device', 'user'])
            ->whereNull('returned_date')
            ->get();
    }

    public function getAssignmentsByUser(int $userId): Collection
    {
        return DeviceAssignment::with(['device'])
            ->where('user_id', $userId)
            ->orderBy('assigned_date', 'desc')
            ->get();
    }

    public function getAssignmentsByDevice(int $deviceId): Collection
    {
        return DeviceAssignment::with(['user'])
            ->where('device_id', $deviceId)
            ->orderBy('assigned_date', 'desc')
            ->get();
    }

    public function getAssignmentsByBranch(int $branchId): Collection
    {
        return DeviceAssignment::with(['device', 'user'])
            ->where('branch_id', $branchId)
            ->get();
    }

    public function returnDevice(int $assignmentId, array $data): DeviceAssignment
    {
        $assignment = DeviceAssignment::findOrFail($assignmentId);
        
        if ($assignment->returned_date) {
            throw new \Exception('Device has already been returned.');
        }
        
        $assignment->update($data);
        return $assignment->fresh();
    }

    public function getUserActiveDevices(int $userId): Collection
    {
        return DeviceAssignment::with(['device.bribox.category'])
            ->where('user_id', $userId)
            ->whereNull('returned_date')
            ->get();
    }

    public function getUserDeviceHistory(int $userId): Collection
    {
        return DeviceAssignment::with(['device'])
            ->where('user_id', $userId)
            ->whereNotNull('returned_date')
            ->orderBy('assigned_date', 'desc')
            ->get();
    }
}
