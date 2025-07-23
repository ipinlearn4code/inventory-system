<!-- unused, Deprecated code -->
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
        $devicesPerBranch = Branch::with([
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

        $query = Device::with(['bribox.category', 'currentAssignment.user']);

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
                'assetCode' => $device->asset_code,
                'brand' => $device->brand,
                'brandName' => $device->brand_name,
                'serialNumber' => $device->serial_number,
                'condition' => $device->condition,
                'category' => $device->bribox->category->name ?? null,
                'spec1' => $device->spec1,
                'spec2' => $device->spec2,
                'spec3' => $device->spec3,
                'spec4' => $device->spec4,
                'spec5' => $device->spec5,
                'isAssigned' => $device->currentAssignment !== null,
                'assignedTo' => $device->currentAssignment ? $device->currentAssignment->user->name : null,
                'assignedDate' => $device->currentAssignment ? $device->currentAssignment->assigned_date : null,
                'createdAt' => $device->created_at,
                'createdBy' => $device->created_by,
                'updatedAt' => $device->updated_at,
                'updatedBy' => $device->updated_by,
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

    /**
     * Get device details by ID
     */
    public function deviceDetails(Request $request, $id)
    {
        $device = Device::with(['bribox.category', 'currentAssignment.user.branch', 'assignments.user'])
            ->findOrFail($id);

        return response()->json([
            'data' => [
                'deviceId' => $device->device_id,
                'assetCode' => $device->asset_code,
                'brand' => $device->brand,
                'brandName' => $device->brand_name,
                'serialNumber' => $device->serial_number,
                'condition' => $device->condition,
                'category' => $device->bribox->category->name ?? null,
                'spec1' => $device->spec1,
                'spec2' => $device->spec2,
                'spec3' => $device->spec3,
                'spec4' => $device->spec4,
                'spec5' => $device->spec5,
                'devDate' => $device->dev_date,
                'currentAssignment' => $device->currentAssignment ? [
                    'assignmentId' => $device->currentAssignment->assignment_id,
                    'user' => [
                        'userId' => $device->currentAssignment->user->user_id,
                        'name' => $device->currentAssignment->user->name,
                        'pn' => $device->currentAssignment->user->pn,
                        'position' => $device->currentAssignment->user->position,
                    ],
                    'branch' => [
                        'branchId' => $device->currentAssignment->user->branch->branch_id,
                        'unitName' => $device->currentAssignment->user->branch->unit_name,
                        'branchCode' => $device->currentAssignment->user->branch->branch_code,
                    ],
                    'assignedDate' => $device->currentAssignment->assigned_date,
                    'notes' => $device->currentAssignment->notes,
                ] : null,
                'status' => $device->status,
                'assignmentHistory' => $device->assignments->map(function ($assignment) {
                    return [
                        'assignmentId' => $assignment->assignment_id,
                        'userName' => $assignment->user->name,
                        'userPn' => $assignment->user->pn,
                        'assignedDate' => $assignment->assigned_date,
                        'returnedDate' => $assignment->returned_date,
                        'notes' => $assignment->notes,
                    ];
                }),
                'createdAt' => $device->created_at,
                'createdBy' => $device->created_by,
                'updatedAt' => $device->updated_at,
                'updatedBy' => $device->updated_by,
            ]
        ]);
    }

    /**
     * Create a new device
     */
    public function createDevice(Request $request)
    {
        $request->validate([
            'brand' => 'required|string|max:50',
            'brand_name' => 'required|string|max:50',
            'serial_number' => 'required|string|max:50|unique:devices,serial_number',
            'asset_code' => 'required|string|max:20|unique:devices,asset_code',
            'bribox_id' => 'required|exists:briboxes,bribox_id',
            'condition' => 'required|in:Baik,Rusak,Perlu Pengecekan',
            'spec1' => 'nullable|string|max:100',
            'spec2' => 'nullable|string|max:100',
            'spec3' => 'nullable|string|max:100',
            'spec4' => 'nullable|string|max:100',
            'spec5' => 'nullable|string|max:100',
            'dev_date' => 'nullable|date',
        ]);

        $currentUserPn = auth()->user()?->pn ?? session('authenticated_user.pn') ?? 'api-system';

        $device = Device::create(array_merge($request->validated(), [
            'created_by' => $currentUserPn,
            'created_at' => now(),
        ]));

        // Log the creation
        InventoryLog::create([
            'resource_type' => 'device',
            'resource_id' => $device->device_id,
            'action_type' => 'CREATE',
            'old_value' => null,
            'new_value' => json_encode($device->toArray()),
            'created_by' => $currentUserPn,
            'created_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'deviceId' => $device->device_id,
                'assetCode' => $device->asset_code,
                'brand' => $device->brand,
                'brandName' => $device->brand_name,
                'serialNumber' => $device->serial_number,
                'condition' => $device->condition,
            ]
        ], 201);
    }

    /**
     * Update a device
     */
    public function updateDevice(Request $request, $id)
    {
        $device = Device::findOrFail($id);

        $request->validate([
            'brand' => 'sometimes|string|max:255',
            'brand_name' => 'sometimes|string|max:255',
            'serial_number' => 'sometimes|string|max:255|unique:devices,serial_number,' . $id . ',device_id',
            'asset_code' => 'sometimes|string|max:20|unique:devices,asset_code,' . $id . ',device_id',
            'bribox_id' => 'sometimes|exists:briboxes,bribox_id',
            'condition' => 'sometimes|in:Baik,Rusak,Perlu Pengecekan',
            'spec1' => 'nullable|string|max:255',
            'spec2' => 'nullable|string|max:255',
            'spec3' => 'nullable|string|max:255',
            'spec4' => 'nullable|string|max:255',
            'spec5' => 'nullable|string|max:255',
            'dev_date' => 'nullable|date',
        ]);

        $currentUserPn = auth()->user()?->pn ?? session('authenticated_user.pn') ?? 'api-system';
        $oldData = $device->toArray();

        $device->update(array_merge($request->validated(), [
            'updated_by' => $currentUserPn,
            'updated_at' => now(),
        ]));

        // Log the update
        InventoryLog::create([
            'resource_type' => 'device',
            'resource_id' => $device->device_id,
            'action_type' => 'UPDATE',
            'old_value' => json_encode($oldData),
            'new_value' => json_encode($device->fresh()->toArray()),
            'created_by' => $currentUserPn,
            'created_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'deviceId' => $device->device_id,
                'assetCode' => $device->asset_code,
                'brand' => $device->brand,
                'brandName' => $device->brand_name,
                'serialNumber' => $device->serial_number,
                'condition' => $device->condition,
            ]
        ]);
    }

    /**
     * Delete a device
     */
    public function deleteDevice($id)
    {
        $device = Device::findOrFail($id);

        // Check if device is currently assigned
        if ($device->currentAssignment) {
            return response()->json([
                'message' => 'Cannot delete device that is currently assigned.',
                'errorCode' => 'ERR_DEVICE_ASSIGNED'
            ], 400);
        }

        $currentUserPn = auth()->user()?->pn ?? session('authenticated_user.pn') ?? 'api-system';
        $deviceData = $device->toArray();

        // Log the deletion
        InventoryLog::create([
            'resource_type' => 'device',
            'resource_id' => $device->device_id,
            'action_type' => 'DELETE',
            'old_value' => json_encode($deviceData),
            'new_value' => null,
            'created_by' => $currentUserPn,
            'created_at' => now(),
        ]);

        $device->delete();

        return response()->json([
            'message' => 'Device deleted successfully.',
            'errorCode' => null
        ]);
    }

    /**
     * Get device assignments with pagination
     */
    public function deviceAssignments(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $branchId = $request->input('branchId');
        $activeOnly = $request->input('activeOnly', false);
        $page = $request->input('page', 1);
        $perPage = $request->input('perPage', 20);

        $query = DeviceAssignment::with(['device', 'user.department', 'branch']);

        if ($search) {
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

        if ($status) {
            $query->where('status', $status);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($activeOnly) {
            $query->whereNull('returned_date');
        }

        $assignments = $query->orderBy('assigned_date', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = $assignments->map(function ($assignment) {
            return [
                'assignmentId' => $assignment->assignment_id,
                'assetCode' => $assignment->device->asset_code,
                'brand' => $assignment->device->brand . ' ' . $assignment->device->brand_name,
                'serialNumber' => $assignment->device->serial_number,
                'Assigned To' => $assignment->user->name,
                'unitName' => $assignment->branch->unit_name,
                'status' => $assignment->device->status,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'currentPage' => $assignments->currentPage(),
                'lastPage' => $assignments->lastPage(),
                'total' => $assignments->total()
            ]
        ]);
    }

    public function deviceAssignmentDetails($id)
    {
        $assignment = DeviceAssignment::with(['device', 'user.branch', 'branch'])
            ->findOrFail($id);

        return response()->json([
            'data' => [
                'assignmentId' => $assignment->assignment_id,
                'deviceId' => $assignment->device->device_id,
                'assetCode' => $assignment->device->asset_code,
                'brand' => $assignment->device->brand . ' ' . $assignment->device->brand_name,
                'serialNumber' => $assignment->device->serial_number,
                'assignedTo' => [
                    'userId' => $assignment->user->user_id,
                    'name' => $assignment->user->name,
                    'pn' => $assignment->user->pn,
                    'branch' => [
                        'branchId' => $assignment->user->branch->branch_id,
                        'unitName' => $assignment->user->branch->unit_name,
                        'branchCode' => $assignment->user->branch->branch_code,
                    ],
                ],
                'assignedDate' => $assignment->assigned_date,
                'returnedDate' => $assignment->returned_date,
                'status' => $assignment->status,
                'notes' => $assignment->notes,
            ]
        ]);
    }

    /**
     * Create a new device assignment
     */
    public function createDeviceAssignment(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:devices,device_id',
            'user_id' => 'required|exists:users,user_id',
            'assigned_date' => 'required|date|before_or_equal:today',
            'status' => 'sometimes|in:Digunakan,Tidak Digunakan,Cadangan',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check if device is available
        $device = Device::with(['bribox.category'])->findOrFail($request->device_id);
        if ($device->currentAssignment) {
            return response()->json([
                'message' => 'Device is already assigned to another user.',
                'errorCode' => 'ERR_DEVICE_ALREADY_ASSIGNED'
            ], 400);
        }

        // Check if user already has an active assignment for the same bribox and category
        $existingAssignment = DeviceAssignment::with(['device.bribox.category'])
            ->whereNull('returned_date')
            ->where('user_id', $request->user_id)
            ->whereHas('device.bribox', function ($query) use ($device) {
                $query->where('bribox_id', $device->bribox_id)
                      ->whereHas('category', function ($categoryQuery) use ($device) {
                          $categoryQuery->where('category_id', $device->bribox->category_id);
                      });
            })
            ->first();

        if ($existingAssignment) {
            $categoryName = $device->bribox->category->category_name ?? 'Unknown Category';
            $briboxType = $device->bribox->type ?? 'Unknown Type';
            return response()->json([
                'message' => "User already has an active assignment for device type '{$briboxType}' in category '{$categoryName}'.",
                'errorCode' => 'ERR_USER_ALREADY_HAS_DEVICE_TYPE',
                'existingAssignment' => [
                    'assignmentId' => $existingAssignment->assignment_id,
                    'deviceAssetCode' => $existingAssignment->device->asset_code,
                    'assignedDate' => $existingAssignment->assigned_date,
                ]
            ], 400);
        }

        // Get user's branch
        $user = \App\Models\User::findOrFail($request->user_id);
        $currentUserPn = auth()->user()?->pn ?? session('authenticated_user.pn') ?? 'api-system';

        $assignment = DeviceAssignment::create([
            'device_id' => $request->device_id,
            'user_id' => $request->user_id,
            'branch_id' => $user->branch_id,
            'assigned_date' => $request->assigned_date,
            'notes' => $request->notes,
            'created_by' => $currentUserPn,
            'created_at' => now(),
        ]);

        // Update device status
        $device->update([
            'status' => $request->status ?? 'Digunakan',
            'updated_by' => $currentUserPn,
            'updated_at' => now(),
        ]);

        // Log the assignment
        InventoryLog::create([
            'resource_type' => 'device_assignment',
            'resource_id' => $assignment->assignment_id,
            'action_type' => 'CREATE',
            'old_value' => null,
            'new_value' => json_encode($assignment->toArray()),
            'created_by' => $currentUserPn,
            'created_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'assignmentId' => $assignment->assignment_id,
                'deviceId' => $assignment->device_id,
                'userId' => $assignment->user_id,
                'assignedDate' => $assignment->assigned_date,
                'status' => $device->status,
            ]
        ], 201);
    }

    /**
     * Update a device assignment
     */
    public function updateDeviceAssignment(Request $request, $id)
    {
        $assignment = DeviceAssignment::findOrFail($id);

        $request->validate([
            'status' => 'sometimes|in:Digunakan,Tidak Digunakan,Cadangan',
            'notes' => 'nullable|string|max:500',
            'returned_date' => 'nullable|date|after_or_equal:assigned_date',
        ]);

        $currentUserPn = auth()->user()?->pn ?? session('authenticated_user.pn') ?? 'api-system';
        $oldData = $assignment->toArray();

        $assignment->update(array_merge($request->validated(), [
            'updated_by' => $currentUserPn,
            'updated_at' => now(),
        ]));

        // Log the update
        InventoryLog::create([
            'resource_type' => 'device_assignment',
            'resource_id' => $assignment->assignment_id,
            'action_type' => 'UPDATE',
            'old_value' => json_encode($oldData),
            'new_value' => json_encode($assignment->fresh()->toArray()),
            'created_by' => $currentUserPn,
            'created_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'assignmentId' => $assignment->assignment_id,
                'status' => $assignment->device->status,
                'returnedDate' => $assignment->returned_date,
                'notes' => $assignment->notes,
            ]
        ]);
    }

    /**
     * Return a device (mark assignment as returned)
     */
    public function returnDevice(Request $request, $id)
    {
        $assignment = DeviceAssignment::findOrFail($id);

        if ($assignment->returned_date) {
            return response()->json([
                'message' => 'Device has already been returned.',
                'errorCode' => 'ERR_DEVICE_ALREADY_RETURNED'
            ], 400);
        }

        $request->validate([
            'returned_date' => 'sometimes|date|after_or_equal:assigned_date',
            'return_notes' => 'nullable|string|max:500',
        ]);

        $currentUserPn = auth()->user()?->pn ?? session('authenticated_user.pn') ?? 'api-system';
        $oldData = $assignment->toArray();

        $returnDate = $request->returned_date ?? now()->toDateString();
        $notes = $assignment->notes;
        if ($request->return_notes) {
            $notes = $notes ? $notes . ' | Return: ' . $request->return_notes : 'Return: ' . $request->return_notes;
        }

        $assignment->update([
            'returned_date' => $returnDate,
            'notes' => $notes,
            'updated_by' => $currentUserPn,
            'updated_at' => now(),
        ]);

        // Update device status to "Tidak Digunakan" when returned
        $assignment->device->update([
            'status' => 'Tidak Digunakan',
            'updated_by' => $currentUserPn,
            'updated_at' => now(),
        ]);

        // Log the return
        InventoryLog::create([
            'resource_type' => 'device_assignment',
            'resource_id' => $assignment->assignment_id,
            'action_type' => 'UPDATE',
            'old_value' => json_encode($oldData),
            'new_value' => json_encode($assignment->fresh()->toArray()),
            'created_by' => $currentUserPn,
            'created_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'assignmentId' => $assignment->assignment_id,
                'returnedDate' => $assignment->returned_date,
                'message' => 'Device returned successfully.',
            ]
        ]);
    }

    /**
     * Get users list with pagination
     */
    public function users(Request $request)
    {
        $search = $request->input('search');
        $departmentId = $request->input('departmentId');
        $branchId = $request->input('branchId');
        $page = $request->input('page', 1);
        $perPage = $request->input('perPage', 20);

        $query = \App\Models\User::with([
            'department',
            'branch',
            'deviceAssignments' => function ($q) {
                $q->whereNull('returned_date');
            }
        ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('pn', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%");
            });
        }

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $users = $query->paginate($perPage, ['*'], 'page', $page);

        $data = $users->map(function ($user) {
            return [
                'userId' => $user->user_id,
                'pn' => $user->pn,
                'name' => $user->name,
                'position' => $user->position,
                'department' => [
                    'departmentId' => $user->department->department_id ?? null,
                    'name' => $user->department->name ?? null,
                ],
                'branch' => [
                    'branchId' => $user->branch->branch_id ?? null,
                    'unitName' => $user->branch->unit_name ?? null,
                    'branchCode' => $user->branch->branch_code ?? null,
                ],
                'activeDevicesCount' => $user->deviceAssignments->count(),
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'currentPage' => $users->currentPage(),
                'lastPage' => $users->lastPage(),
                'total' => $users->total()
            ]
        ]);
    }

    /**
     * Get branches list
     */
    public function branches(Request $request)
    {
        $branches = Branch::with('mainBranch')->get();

        $data = $branches->map(function ($branch) {
            return [
                'branchId' => $branch->branch_id,
                'unitName' => $branch->unit_name,
                'branchCode' => $branch->branch_code,
                'address' => $branch->address,
                'mainBranch' => [
                    'mainBranchId' => $branch->mainBranch->main_branch_id ?? null,
                    'name' => $branch->mainBranch->main_branch_name ?? null,
                ],
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * Get categories (briboxes) list
     */
    public function categories(Request $request)
    {
        $categories = \App\Models\Bribox::with('category')->get();

        $data = $categories->map(function ($bribox) {
            return [
                'briboxId' => $bribox->bribox_id,
                'name' => $bribox->name,
                'description' => $bribox->description,
                'category' => [
                    'categoryId' => $bribox->category->briboxes_category_id ?? null,
                    'name' => $bribox->category->name ?? null,
                ],
            ];
        });

        return response()->json(['data' => $data]);
    }
}
