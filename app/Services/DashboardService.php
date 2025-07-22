<?php

namespace App\Services;

use App\Contracts\DashboardServiceInterface;
use App\Contracts\DeviceRepositoryInterface;
use App\Contracts\DeviceAssignmentRepositoryInterface;
use App\Models\InventoryLog;
use App\Models\Branch;

class DashboardService implements DashboardServiceInterface
{
    public function __construct(
        private DeviceRepositoryInterface $deviceRepository,
        private DeviceAssignmentRepositoryInterface $assignmentRepository
    ) {}

    public function getKpis(?int $branchId = null): array
    {
        // Total devices with optional branch filter
        $totalDevices = $this->getTotalDevices($branchId);
        
        // Devices in use (have current assignment)
        $inUse = $this->getDevicesInUse($branchId);
        
        // Available devices (no current assignment)
        $available = $totalDevices - $inUse;
        
        // Damaged devices
        $damaged = $this->getDamagedDevices($branchId);

        return [
            'totalDevices' => $totalDevices,
            'inUse' => $inUse,
            'available' => $available,
            'damaged' => $damaged,
        ];
    }

    public function getChartData(?int $branchId = null): array
    {
        // Device conditions
        $deviceConditions = $this->getDeviceConditions($branchId);
        
        // Devices per branch
        $devicesPerBranch = $this->getDevicesPerBranch();

        return [
            'deviceConditions' => $deviceConditions,
            'devicesPerBranch' => $devicesPerBranch,
        ];
    }

    public function getActivityLog(int $limit = 10): array
    {
        return InventoryLog::with('userAffected')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($log) {
                $newValue = json_decode($log->new_value, true);

                $type = 'device-updated';
                $category = 'info';
                $title = 'Perubahan Perangkat';
                $description = 'Data perangkat diperbarui';

                if ($log->action_type === 'CREATE') {
                    $type = 'device-created';
                    $category = 'success';
                    $title = 'Perangkat Dibuat';
                    $description = 'Perangkat baru ditambahkan';
                } elseif ($log->action_type === 'DELETE') {
                    $type = 'device-deleted';
                    $category = 'warning';
                    $title = 'Perangkat Dihapus';
                    $description = 'Perangkat dihapus dari sistem';
                } elseif (is_array($newValue) && isset($newValue['type']) && $newValue['type'] === 'issue_report') {
                    $type = 'device-issue';
                    $category = 'warning';
                    $title = 'Laporan Masalah';
                    $description = $newValue['description'] ?? 'Laporan masalah perangkat';
                }

                return [
                    'type' => $type,
                    'Category' => $category,
                    'title' => $title,
                    'description' => $description,
                    'user' => $log->created_by ?? 'System',
                    'date' => $log->created_at ? $log->created_at->format('Y-m-d') : date('Y-m-d'),
                    'time' => $log->created_at ? $log->created_at->format('H:i:s') : date('H:i:s')
                ];
            })
            ->toArray();
    }

    private function getTotalDevices(?int $branchId = null): int
    {
        $filters = [];
        if ($branchId) {
            $filters['branch_id'] = $branchId;
        }
        
        return $this->deviceRepository->getPaginated($filters, 1)->total();
    }

    private function getDevicesInUse(?int $branchId = null): int
    {
        $filters = ['active_only' => true];
        if ($branchId) {
            $filters['branch_id'] = $branchId;
        }
        
        return $this->assignmentRepository->getPaginated($filters, 1)->total();
    }

    private function getDamagedDevices(?int $branchId = null): int
    {
        $filters = ['condition' => 'Rusak'];
        if ($branchId) {
            $filters['branch_id'] = $branchId;
        }
        
        return $this->deviceRepository->getPaginated($filters, 1)->total();
    }

    private function getDeviceConditions(?int $branchId = null): array
    {
        $conditions = $this->deviceRepository->countByCondition();
        
        return $conditions->map(function ($item) {
            return [
                'condition' => $item->condition,
                'count' => $item->count
            ];
        })->toArray();
    }

    private function getDevicesPerBranch(): array
    {
        return Branch::with([
            'users.deviceAssignments' => function ($query) {
                $query->whereNull('returned_date');
            }
        ])
            ->get()
            ->map(function ($branch) {
                $deviceCount = 0;
                foreach ($branch->users as $user) {
                    $deviceCount += $user->deviceAssignments->count();
                }

                return [
                    'branchName' => $branch->unit_name,
                    'count' => $deviceCount
                ];
            })
            ->filter(function ($item) {
                return $item['count'] > 0; // Only include branches with devices
            })
            ->values()
            ->toArray();
    }
}
