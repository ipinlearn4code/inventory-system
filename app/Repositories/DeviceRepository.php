<?php

namespace App\Repositories;

use App\Contracts\DeviceRepositoryInterface;
use App\Models\Device;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class DeviceRepository implements DeviceRepositoryInterface
{
    public function findById(int $id): ?Device
    {
        return Device::with(['bribox.category', 'currentAssignment.user.branch', 'assignments.user'])
            ->find($id);
    }

    public function getAll(): Collection
    {
        return Device::with(['bribox.category', 'currentAssignment.user'])->get();

    }

    public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        // dd('hoho');
        $query = Device::with(['bribox.category', 'currentAssignment.user']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('brand', 'like', "%{$search}%")
                    ->orWhere('brand_name', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('asset_code', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['condition'])) {
            $query->where('condition', $filters['condition']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['branch_id'])) {
            $query->whereHas('currentAssignment.user', function ($q) use ($filters) {
                $q->where('branch_id', $filters['branch_id']);
            });
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): Device
    {
        return Device::create($data);
    }

    public function update(int $id, array $data): Device
    {
        $device = Device::findOrFail($id);
        $device->update($data);
        return $device->fresh();
    }

    public function delete(int $id): bool
    {
        $device = Device::findOrFail($id);
        
        // Check if device is currently assigned
        if ($device->currentAssignment) {
            throw new \Exception('Cannot delete device that is currently assigned.');
        }
        
        return $device->delete();
    }

    public function getAvailableDevices(): Collection
    {
        return Device::whereDoesntHave('currentAssignment')->get();
    }

    public function getDevicesByCondition(string $condition): Collection
    {
        return Device::where('condition', $condition)->get();
    }

    public function getDevicesWithCurrentAssignment(): Collection
    {
        return Device::with(['currentAssignment.user'])->whereHas('currentAssignment')->get();
    }

    public function countByCondition(): Collection
    {
        return Device::selectRaw('`condition`, COUNT(*) as count')
            ->groupBy('condition')
            ->get();
    }

    public function getDeviceHistory(int $deviceId): Collection
    {
        return Device::findOrFail($deviceId)
            ->assignments()
            ->with('user')
            ->orderBy('assigned_date', 'desc')
            ->get();
    }
}
