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
        $query = Device::with(['bribox.category', 'currentAssignment.user']);
        
        if (!empty($filters['search'])) {
            $search = $filters['search'];

            // Escape special LIKE characters: %, _, and \
            $escapedSearch = addcslashes($search, '%_\\');

            // Use parameter binding and specify ESCAPE for safe LIKE
            $query->where(function ($q) use ($escapedSearch) {
                $q->where('brand', 'like', "%{$escapedSearch}%")
                  ->orWhere('brand_name', 'like', "%{$escapedSearch}%")
                  ->orWhere('serial_number', 'like', "%{$escapedSearch}%")
                  ->orWhere('asset_code', 'like', "%{$escapedSearch}%");
            });
            // Add ESCAPE clause for all LIKE queries
            $query->getQuery()->wheres = array_map(function ($where) {
                if (isset($where['type']) && $where['type'] === 'Basic' && isset($where['operator']) && strtolower($where['operator']) === 'like') {
                    $where['sql'] .= " ESCAPE '\\'";
                }
                return $where;
            }, $query->getQuery()->wheres);
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
        
        if (!empty($filters['bribox_category'])) {
            $query->whereHas('bribox.category', function ($q) use ($filters) {
                $q->where('category_name', 'like', "%{$filters['bribox_category']}%");
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
    
    public function findByAssetCodeWithRelations(string $assetCode): ?Device
    {
        return Device::with([
            'bribox.category',
            'currentAssignment.user.department',
            'currentAssignment.user.branch',
            'assignments' => function ($query) {
                $query->with([
                    'user:user_id,name',
                    'assignmentLetters.approver:user_id,name'
                ])->orderBy('assigned_date', 'desc');
            }
        ])->where('asset_code', $assetCode)->first();
    }
}
