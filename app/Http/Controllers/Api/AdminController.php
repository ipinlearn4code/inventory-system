<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceAssignment;
use App\Models\InventoryLog;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Get dashboard KPIs
     */
    public function dashboardKpis(Request $request)
    {
        $branchId = $request->input('branchId');
        
        // Base query for devices
        $deviceQuery = Device::query();
        
        if ($branchId) {
            // Filter by devices assigned to users in specific branch
            $deviceQuery->whereHas('currentAssignment.user', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            });
        }

        $totalDevices = $deviceQuery->count();
        
        // Devices in use (have current assignment)
        $inUse = DeviceAssignment::whereNull('returned_date')
            ->when($branchId, function ($query) use ($branchId) {
                return $query->whereHas('user', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            })
            ->count();

        // Available devices (no current assignment)
        $available = $totalDevices - $inUse;

        // Damaged devices
        $damaged = Device::where('condition', 'Rusak')
            ->when($branchId, function ($query) use ($branchId) {
                return $query->whereHas('currentAssignment.user', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            })
            ->count();

        // Get recent activity from inventory log
        $activityLog = InventoryLog::with('userAffected')
            ->orderBy('created_at', 'desc')
            ->limit(10)
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
            });

        return response()->json([
            'data' => [
                'totalDevices' => $totalDevices,
                'inUse' => $inUse,
                'available' => $available,
                'damaged' => $damaged,
                'activityLog' => $activityLog
            ]
        ]);
    }

    /**
     * Get dashboard chart data
     */
    public function dashboardCharts(Request $request)
    {
        $branchId = $request->input('branchId');

        // Device conditions
        $deviceConditions = Device::select('condition', DB::raw('count(*) as count'))
            ->when($branchId, function ($query) use ($branchId) {
                return $query->whereHas('currentAssignment.user', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            })
            ->groupBy('condition')
            ->get()
            ->map(function ($item) {
                return [
                    'condition' => $item->condition,
                    'count' => $item->count
                ];
            });

        // Devices per branch
        $devicesPerBranch = Branch::with(['users.deviceAssignments' => function ($query) {
                $query->whereNull('returned_date');
            }])
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
            ->values();

        return response()->json([
            'data' => [
                'deviceConditions' => $deviceConditions,
                'devicesPerBranch' => $devicesPerBranch
            ]
        ]);
    }

    /**
     * Get devices with search and pagination
     */
    public function devices(Request $request)
    {
        $search = $request->input('search');
        $condition = $request->input('condition');
        $page = $request->input('page', 1);
        $perPage = $request->input('perPage', 20);

        $query = Device::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('brand', 'like', "%{$search}%")
                  ->orWhere('brand_name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('asset_code', 'like', "%{$search}%");
            });
        }

        if ($condition) {
            $query->where('condition', $condition);
        }

        $devices = $query->paginate($perPage, ['*'], 'page', $page);

        $data = $devices->map(function ($device) {
            return [
                'deviceId' => $device->device_id,
                'brand' => $device->brand,
                'brandName' => $device->brand_name,
                'serialNumber' => $device->serial_number,
                'condition' => $device->condition
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'currentPage' => $devices->currentPage(),
                'lastPage' => $devices->lastPage(),
                'total' => $devices->total()
            ]
        ]);
    }
}
