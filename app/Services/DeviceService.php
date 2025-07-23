<?php

namespace App\Services;

use App\Contracts\DeviceRepositoryInterface;
use App\Contracts\DeviceAssignmentRepositoryInterface;
use App\Contracts\InventoryLogServiceInterface;

class DeviceService
{
    public function __construct(
        private DeviceRepositoryInterface $deviceRepository,
        private DeviceAssignmentRepositoryInterface $assignmentRepository,
        private InventoryLogServiceInterface $inventoryLogService
    ) {}

    public function createDevice(array $data): array
    {
        $currentUserPn = $this->inventoryLogService->getCurrentUserPn();
        
        $deviceData = array_merge($data, [
            'created_by' => $currentUserPn,
            'created_at' => now(),
        ]);

        $device = $this->deviceRepository->create($deviceData);

        // Log the creation using the injected service
        $this->inventoryLogService->logDeviceAction($device, 'CREATE', null, $device->toArray());

        return [
            'deviceId' => $device->device_id,
            'assetCode' => $device->asset_code,
            'brand' => $device->brand,
            'brandName' => $device->brand_name,
            'serialNumber' => $device->serial_number,
            'condition' => $device->condition,
        ];
    }

    public function updateDevice(int $id, array $data): array
    {
        $device = $this->deviceRepository->findById($id);
        if (!$device) {
            throw new \Exception('Device not found');
        }

        $currentUserPn = $this->inventoryLogService->getCurrentUserPn();
        $oldData = $device->toArray();

        $updateData = array_merge($data, [
            'updated_by' => $currentUserPn,
            'updated_at' => now(),
        ]);

        $updatedDevice = $this->deviceRepository->update($id, $updateData);

        // Log the update using the injected service
        $this->inventoryLogService->logDeviceAction($updatedDevice, 'UPDATE', $oldData, $updatedDevice->toArray());

        return [
            'deviceId' => $updatedDevice->device_id,
            'assetCode' => $updatedDevice->asset_code,
            'brand' => $updatedDevice->brand,
            'brandName' => $updatedDevice->brand_name,
            'serialNumber' => $updatedDevice->serial_number,
            'condition' => $updatedDevice->condition,
        ];
    }

    public function deleteDevice(int $id): bool
    {
        $device = $this->deviceRepository->findById($id);
        if (!$device) {
            throw new \Exception('Device not found');
        }

        $deviceData = $device->toArray();
        $result = $this->deviceRepository->delete($id);

        if ($result) {
            // Log the deletion using the injected service
            $this->inventoryLogService->logDeviceAction($device, 'DELETE', $deviceData, null);
        }

        return $result;
    }

    public function getDeviceDetails(int $id): array
    {
        $device = $this->deviceRepository->findById($id);
        if (!$device) {
            throw new \Exception('Device not found');
        }

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
        ];
    }
}
