<?php

namespace App\Services;

use App\Contracts\DeviceRepositoryInterface;
use App\Contracts\DeviceAssignmentRepositoryInterface;
use App\Contracts\InventoryLogServiceInterface;
use Illuminate\Support\Facades\DB;

class DeviceService
{
    public function __construct(
        private DeviceRepositoryInterface $deviceRepository,
        private DeviceAssignmentRepositoryInterface $assignmentRepository,
        private InventoryLogServiceInterface $inventoryLogService
    ) {
    }

    public function createDevice(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $currentUserPn = $this->inventoryLogService->getCurrentUserPn();

            $deviceData = array_merge($data, [
                'created_by' => $currentUserPn,
                'created_at' => now(),
            ]);

            $device = $this->deviceRepository->create($deviceData);

            // Log the creation - if this fails, the transaction will rollback
            $this->inventoryLogService->logDeviceAction($device, 'CREATE', null, $device->toArray());

            return [
                'deviceId' => $device->device_id,
                'assetCode' => $device->asset_code,
                'brand' => $device->brand,
                'brandName' => $device->brand_name,
                'serialNumber' => $device->serial_number,
                'condition' => $device->condition,
            ];
        });
    }

    public function updateDevice(int $id, array $data): array
    {
        return DB::transaction(function () use ($id, $data) {
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

            // Log the update - if this fails, the transaction will rollback
            $this->inventoryLogService->logDeviceAction($updatedDevice, 'UPDATE', $oldData, $updatedDevice->toArray());

            return [
                'deviceId' => $updatedDevice->device_id,
                'assetCode' => $updatedDevice->asset_code,
                'brand' => $updatedDevice->brand,
                'brandName' => $updatedDevice->brand_name,
                'serialNumber' => $updatedDevice->serial_number,
                'condition' => $updatedDevice->condition,
            ];
        });
    }

    public function deleteDevice(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $device = $this->deviceRepository->findById($id);
            if (!$device) {
                throw new \Exception('Device not found');
            }

            $deviceData = $device->toArray();
            $result = $this->deviceRepository->delete($id);

            if ($result) {
                // Log the deletion - if this fails, the transaction will rollback
                $this->inventoryLogService->logDeviceAction(
                    $device,
                    'DELETE',
                    $deviceData,
                    null,
                    $this->inventoryLogService->getCurrentUserPn()
                );
            }
            return $result;
        });
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

    public function scanDeviceByQRCode(string $qrCode): array
    {
        // Validate QR code format (must start with 'briven-')
        if (!str_starts_with($qrCode, 'briven-')) {
            throw new \InvalidArgumentException('Invalid QR code format. QR code must start with "briven-".');
        }

        // Extract asset code from QR code
        $assetCode = substr($qrCode, 7); // Remove 'briven-' prefix

        // Find device by asset code with necessary relationships
        $device = $this->deviceRepository->findByAssetCodeWithRelations($assetCode);

        if (!$device) {
            throw new \Exception('Device not found with the provided QR code.');
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

        return [
            'device' => $deviceData,
            'history' => $history,
        ];
    }
}
