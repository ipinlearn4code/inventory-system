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

        return $this->paginatedResponse($devices, ['data' => $data->toArray()]);
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
        try {
            $data = $this->deviceService->scanDeviceByQRCode($qrCode);
            return $this->successResponse($data);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 'ERR_INVALID_QR_FORMAT', 400);
        } catch (\Exception $e) {
            \Log::error('QR scan error', [
                'qr_code' => $qrCode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if (str_contains($e->getMessage(), 'not found')) {
                return $this->errorResponse($e->getMessage(), 'ERR_DEVICE_NOT_FOUND', 404);
            }

            return $this->errorResponse('An error occurred while scanning the device.', 'ERR_SCAN_FAILED', 500);
        }
    }
}
