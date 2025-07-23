<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\DeviceService;
use App\Contracts\DeviceRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DeviceController extends Controller
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
        // dd($devices);
        $data = collect($devices->items())->map(function ($device) {
            // dd($device);
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
            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errorCode' => 'ERR_DEVICE_NOT_FOUND'
            ], 404);
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
            return response()->json(['data' => $data], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errorCode' => 'ERR_DEVICE_CREATION_FAILED'
            ], 400);
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
            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errorCode' => 'ERR_DEVICE_UPDATE_FAILED'
            ], 400);
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
            return response()->json([
                'message' => $e->getMessage(),
                'errorCode' => $errorCode
            ], 400);
        }
    }
}
