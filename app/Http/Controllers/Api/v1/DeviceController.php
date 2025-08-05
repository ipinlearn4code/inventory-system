<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\DeviceService;
use App\Contracts\DeviceRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DeviceController extends BaseApiController
{
    public function __construct(
        private DeviceService $deviceService,
        private DeviceRepositoryInterface $deviceRepository
    ) {}

    /**
     * Get devices with search and pagination
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'search' => $request->input('search'),
            'condition' => $request->input('condition'),
            'status' => $request->input('status'),
            'branch_id' => $request->input('branchId'),
        ];

        $perPage = $request->input('perPage', 20);
        $devices = $this->deviceRepository->getPaginated($filters, $perPage);

        $data = collect($devices->items())->map(function ($device) {
            return [
                'deviceId' => $device->device_id,
                'assetCode' => $device->asset_code,
                'brand' => $device->brand,
                'brandName' => $device->brand_name,
                'serialNumber' => $device->serial_number,
                'condition' => $device->condition,
                'isAssigned' => $device->currentAssignment !== null,
                'assignedTo' => $device->currentAssignment ? $device->currentAssignment->user->name : null,
                'category' => $device->bribox->category->category_name ?? null,
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
    public function show(int $id): JsonResponse
    {
        try {
            $data = $this->deviceService->getDeviceDetails($id);
            return $this->successResponse($data);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ERR_DEVICE_NOT_FOUND', 404);
        }
    }

    /**
     * Create a new device
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
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

        try {
            $data = $this->deviceService->createDevice($validatedData);
            return $this->successResponse($data, 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ERR_DEVICE_CREATION_FAILED', 400);
        }
    }

    /**
     * Update a device
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validatedData = $request->validate([
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

        try {
            $data = $this->deviceService->updateDevice($id, $validatedData);
            return $this->successResponse($data);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ERR_DEVICE_UPDATE_FAILED', 400);
        }
    }

    /**
     * Delete a device
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->deviceService->deleteDevice($id);
            return response()->json([
                'message' => 'Device deleted successfully.',
                'errorCode' => null
            ]);
        } catch (\Exception $e) {
            $errorCode = str_contains($e->getMessage(), 'assigned') ? 'ERR_DEVICE_ASSIGNED' : 'ERR_DEVICE_DELETION_FAILED';
            return $this->errorResponse($e->getMessage(), $errorCode, 400);
        }
    }

    /**
     * Scan device by QR code and return device details with assignment history
     */
    public function scanDevice(string $qrCode): JsonResponse
    {
        // Validate QR code format (must start with 'briven-')
        if (!str_starts_with($qrCode, 'briven-')) {
            return $this->errorResponse('Invalid QR code format.', 'ERR_INVALID_QR_FORMAT', 400);
        }

        // Extract asset code from QR code
        $assetCode = substr($qrCode, 7); // Remove 'briven-' prefix

        try {
            // Find device by asset code with necessary relationships
            $device = \App\Models\Device::with([
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

            if (!$device) {
                return $this->errorResponse('Device not found with the provided QR code.', 'ERR_DEVICE_NOT_FOUND', 404);
            }

            // Build device information
            $deviceData = [
                'id' => $device->device_id,
                'asset_code' => $device->asset_code,
                'name' => trim(sprintf('%s %s %s', 
                    $device->bribox->category->category_name ?? '',
                    $device->brand ?? '',
                    $device->brand_name ?? ''
                )),
                'type' => $device->bribox->type ?? 'Unknown',
                'serial_number' => $device->serial_number,
                'dev_date' => $device->dev_date ? (string) $device->dev_date : null,
                'status' => $device->status,
                'condition' => $device->condition,
                'spec1' => $device->spec1,
                'spec2' => $device->spec2,
                'spec3' => $device->spec3,
                'spec4' => $device->spec4,
                'spec5' => $device->spec5,
            ];

            // Add assigned user information if device is currently assigned
            if ($device->currentAssignment) {
                $currentUser = $device->currentAssignment->user;
                $deviceData['assigned_to'] = [
                    'id' => $currentUser->user_id,
                    'name' => $currentUser->name,
                    'department' => $currentUser->department->name ?? 'Unknown',
                    'position' => $currentUser->position,
                    'pn' => $currentUser->pn,
                    'branch' => $currentUser->branch->name ?? 'Unknown',
                    'branch_code' => $currentUser->branch->branch_code ?? 'Unknown',
                ];
            } else {
                $deviceData['assigned_to'] = null;
            }

            // Build assignment history
            $history = [];
            foreach ($device->assignments as $assignment) {
                // Add returned action if device was returned
                if ($assignment->returned_date) {
                    $approver = $assignment->assignmentLetters
                        ->where('letter_type', 'return')
                        ->first()?->approver;

                    $history[] = [
                        'assignment_id' => $assignment->assignment_id,
                        'action' => 'returned',
                        'user' => $assignment->user->name,
                        'approver' => $approver?->name ?? 'Unknown',
                        'date' => (string) $assignment->returned_date,
                        'note' => $assignment->notes ?? '',
                    ];
                }

                // Add assigned action
                $approver = $assignment->assignmentLetters
                    ->where('letter_type', 'assignment')
                    ->first()?->approver;

                $history[] = [
                    'assignment_id' => $assignment->assignment_id,
                    'action' => 'assigned',
                    'user' => $assignment->user->name,
                    'approver' => $approver?->name ?? 'Unknown',
                    'date' => (string) $assignment->assigned_date,
                    'note' => $assignment->notes ?? '',
                ];
            }

            return $this->successResponse([
                'device' => $deviceData,
                'history' => $history,
            ]);

        } catch (\Exception $e) {
            \Log::error('QR scan error', [
                'qr_code' => $qrCode,
                'asset_code' => $assetCode ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse('An error occurred while scanning the device.', 'ERR_SCAN_FAILED', 500);
        }
    }
}
