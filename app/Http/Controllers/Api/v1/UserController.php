<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Contracts\UserRepositoryInterface;
use App\Contracts\DeviceAssignmentRepositoryInterface;
use App\Http\Requests\Api\ReportIssueRequest;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private DeviceAssignmentRepositoryInterface $assignmentRepository
    ) {}

    /**
     * Get home summary data for authenticated user
     */
    public function homeSummary(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $activeDevices = $this->assignmentRepository->getUserActiveDevices($user->user_id);
        $deviceHistory = $this->assignmentRepository->getAssignmentsByUser($user->user_id);

        return response()->json([
            'data' => [
                'activeDevicesCount' => $activeDevices->count(),
                'deviceHistoryCount' => $deviceHistory->count()
            ]
        ]);
    }

    /**
     * Get user's active devices with pagination
     */
    public function devices(Request $request): JsonResponse
    {
        $user = $request->user();
        $page = $request->input('page', 1);
        $perPage = $request->input('perPage', 10);

        $activeDevices = $this->assignmentRepository->getUserActiveDevices($user->user_id);
        
        // Manual pagination for collection
        $total = $activeDevices->count();
        $items = $activeDevices->slice(($page - 1) * $perPage, $perPage);

        $data = $items->map(function ($assignment) {
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
                'currentPage' => $page,
                'lastPage' => ceil($total / $perPage),
                'total' => $total
            ]
        ]);
    }

    /**
     * Get device details for user's assigned device
     */
    public function deviceDetails(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $assignment = $this->assignmentRepository->getUserActiveDevices($user->user_id)
            ->where('device_id', $id)
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
    public function reportIssue(ReportIssueRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $assignment = $this->assignmentRepository->getUserActiveDevices($user->user_id)
            ->where('device_id', $id)
            ->first();

        if (!$assignment) {
            return response()->json([
                'message' => 'Device not found or not assigned to you.',
                'errorCode' => 'ERR_DEVICE_NOT_FOUND'
            ], 404);
        }

        // Log the issue report
        InventoryLog::create([
            'changed_fields' => $id,
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
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $userWithRelations = $this->userRepository->findById($user->user_id);

        return response()->json([
            'data' => [
                'name' => $userWithRelations->name,
                'pn' => $userWithRelations->pn,
                'department' => $userWithRelations->department->name ?? 'Unknown',
                'branch' => $userWithRelations->branch->unit_name ?? 'Unknown',
                'position' => $userWithRelations->position
            ]
        ]);
    }

    /**
     * Get device history with pagination
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        $page = $request->input('page', 1);
        $perPage = $request->input('perPage', 10);

        $deviceHistory = $this->assignmentRepository->getUserDeviceHistory($user->user_id);
        
        // Manual pagination for collection
        $total = $deviceHistory->count();
        $items = $deviceHistory->slice(($page - 1) * $perPage, $perPage);

        $data = $items->map(function ($assignment) {
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
                'currentPage' => $page,
                'lastPage' => ceil($total / $perPage),
                'total' => $total
            ]
        ]);
    }
}
