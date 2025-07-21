<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ReportIssueRequest;
use App\Models\Device;
use App\Models\DeviceAssignment;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Get home summary data
     */
    public function homeSummary(Request $request)
    {
        $user = $request->user();
        
        // Count active devices (not returned)
        $activeDevicesCount = DeviceAssignment::where('user_id', $user->user_id)
            ->whereNull('returned_date')
            ->count();
        
        // Count total device history
        $deviceHistoryCount = DeviceAssignment::where('user_id', $user->user_id)
            ->count();

        return response()->json([
            'data' => [
                'activeDevicesCount' => $activeDevicesCount,
                'deviceHistoryCount' => $deviceHistoryCount
            ]
        ]);
    }

    /**
     * Get user's active devices with pagination
     */
    public function devices(Request $request)
    {
        $user = $request->user();
        $page = $request->input('page', 1);
        $perPage = $request->input('perPage', 10);

        $assignments = DeviceAssignment::with(['device.bribox.category'])
            ->where('user_id', $user->user_id)
            ->whereNull('returned_date')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = $assignments->map(function ($assignment) {
            return [
                'assignmentId' => $assignment->assignment_id,
                'device' => [
                    'deviceId' => $assignment->device->device_id,
                    'categoryName' => $assignment->device->bribox->category->category_name ?? 'Unknown',
                    'brand' => $assignment->device->brand,
                    'brandName' => $assignment->device->brand_name,
                    'serialNumber' => $assignment->device->serial_number
                ]
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

    /**
     * Get device details
     */
    public function deviceDetails(Request $request, $id)
    {
        $user = $request->user();
        
        // Find the assignment for this user and device
        $assignment = DeviceAssignment::with('device')
            ->where('user_id', $user->user_id)
            ->where('device_id', $id)
            ->whereNull('returned_date')
            ->first();

        if (!$assignment) {
            return response()->json([
                'message' => 'Device not found or not assigned to you.',
                'errorCode' => 'ERR_DEVICE_NOT_FOUND'
            ], 404);
        }

        $device = $assignment->device;

        return response()->json([
            'data' => [
                'deviceId' => $device->device_id,
                'brand' => $device->brand,
                'brandName' => $device->brand_name,
                'serialNumber' => $device->serial_number,
                'assetCode' => $device->asset_code,
                'assignedDate' => (string) $assignment->assigned_date,
                'spec1' => $device->spec1,
                'spec2' => $device->spec2,
                'spec3' => $device->spec3,
                'spec4' => $device->spec4,
                'spec5' => $device->spec5
            ]
        ]);
    }

    /**
     * Report device issue
     */
    public function reportIssue(ReportIssueRequest $request, $id)
    {
        $user = $request->user();
        
        // Verify user has this device assigned
        $assignment = DeviceAssignment::where('user_id', $user->user_id)
            ->where('device_id', $id)
            ->whereNull('returned_date')
            ->first();

        if (!$assignment) {
            return response()->json([
                'message' => 'Device not found or not assigned to you.',
                'errorCode' => 'ERR_DEVICE_NOT_FOUND'
            ], 404);
        }

        // In a real implementation, you would save this report to a issues/reports table
        // For now, we'll log it in the inventory_log table
        InventoryLog::create([
            'changed_fields' => $id, // Using device_id as the field
            'action_type' => 'UPDATE',
            'old_value' => null,
            'new_value' => json_encode([
                'type' => 'issue_report',
                'description' => $request->description,
                'date' => $request->date,
                'reported_by' => $user->pn
            ]),
            'user_affected' => $user->pn,
            'created_at' => now(),
            'created_by' => $user->pn
        ]);

        return response()->json([
            'message' => 'Report submitted successfully.',
            'errorCode' => null
        ]);
    }

    /**
     * Get user profile
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        $user->load(['department', 'branch']);

        return response()->json([
            'data' => [
                'name' => $user->name,
                'pn' => $user->pn,
                'department' => $user->department->name ?? 'Unknown',
                'branch' => $user->branch->unit_name ?? 'Unknown',
                'position' => $user->position
            ]
        ]);
    }

    /**
     * Get device history with pagination
     */
    public function history(Request $request)
    {
        $user = $request->user();
        $page = $request->input('page', 1);
        $perPage = $request->input('perPage', 10);

        $assignments = DeviceAssignment::with('device')
            ->where('user_id', $user->user_id)
            ->whereNotNull('returned_date')
            ->orderBy('assigned_date', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = $assignments->map(function ($assignment) {
            return [
                'brand' => $assignment->device->brand,
                'deviceName' => $assignment->device->brand_name,
                'serialNumber' => $assignment->device->serial_number,
                'assignedDate' => (string) $assignment->assigned_date,
                'returnedDate' => (string) $assignment->returned_date
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
}
