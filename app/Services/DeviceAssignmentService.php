<?php

namespace App\Services;

use App\Contracts\DeviceAssignmentRepositoryInterface;
use App\Contracts\DeviceRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Models\InventoryLog;

class DeviceAssignmentService
{
    public function __construct(
        private DeviceAssignmentRepositoryInterface $assignmentRepository,
        private DeviceRepositoryInterface $deviceRepository,
        private UserRepositoryInterface $userRepository
    ) {}

    public function createAssignment(array $data): array
    {
        // Check if device is available
        $device = $this->deviceRepository->findById($data['device_id']);
        if (!$device) {
            throw new \Exception('Device not found');
        }

        if ($device->currentAssignment) {
            throw new \Exception('Device is already assigned to another user.');
        }

        // Check if user already has an active assignment for the same bribox and category
        $user = $this->userRepository->findById($data['user_id']);
        if (!$user) {
            throw new \Exception('User not found');
        }

        $existingAssignment = $this->assignmentRepository->getUserActiveDevices($data['user_id'])
            ->filter(function ($assignment) use ($device) {
                return $assignment->device->bribox_id === $device->bribox_id &&
                       $assignment->device->bribox->category_id === $device->bribox->category_id;
            })->first();

        if ($existingAssignment) {
            $categoryName = $device->bribox->category->category_name ?? 'Unknown Category';
            $briboxType = $device->bribox->type ?? 'Unknown Type';
            throw new \Exception("User already has an active assignment for device type '{$briboxType}' in category '{$categoryName}'.");
        }

        $currentUserPn = $this->getCurrentUserPn();
        
        $assignmentData = array_merge($data, [
            'branch_id' => $user->branch_id,
            'created_by' => $currentUserPn,
            'created_at' => now(),
        ]);

        $assignment = $this->assignmentRepository->create($assignmentData);

        // Update device status
        $device = $this->deviceRepository->update($data['device_id'], [
            'status' => $data['status'] ?? 'Digunakan',
            'updated_by' => $currentUserPn,
            'updated_at' => now(),
        ]);

        // Log the assignment
        $this->logAssignmentAction($assignment, 'CREATE', null, $assignment->toArray());

        return [
            'assignmentId' => $assignment->assignment_id,
            'deviceId' => $assignment->device_id,
            'userId' => $assignment->user_id,
            'assignedDate' => $assignment->assigned_date,
            'status' => $device->status,
        ];
    }

    public function updateAssignment(int $id, array $data): array
    {
        $assignment = $this->assignmentRepository->findById($id);
        if (!$assignment) {
            throw new \Exception('Assignment not found');
        }

        $currentUserPn = $this->getCurrentUserPn();
        $oldData = $assignment->toArray();

        $updateData = array_merge($data, [
            'updated_by' => $currentUserPn,
            'updated_at' => now(),
        ]);

        $updatedAssignment = $this->assignmentRepository->update($id, $updateData);

        // Log the update
        $this->logAssignmentAction($updatedAssignment, 'UPDATE', $oldData, $updatedAssignment->toArray());

        return [
            'assignmentId' => $updatedAssignment->assignment_id,
            'status' => $updatedAssignment->device->status,
            'returnedDate' => $updatedAssignment->returned_date,
            'notes' => $updatedAssignment->notes,
        ];
    }

    public function returnDevice(int $assignmentId, array $data): array
    {
        $assignment = $this->assignmentRepository->findById($assignmentId);
        if (!$assignment) {
            throw new \Exception('Assignment not found');
        }

        if ($assignment->returned_date) {
            throw new \Exception('Device has already been returned.');
        }

        $currentUserPn = $this->getCurrentUserPn();
        $oldData = $assignment->toArray();

        $returnDate = $data['returned_date'] ?? now()->toDateString();
        $notes = $assignment->notes;
        if (!empty($data['return_notes'])) {
            $notes = $notes ? $notes . ' | Return: ' . $data['return_notes'] : 'Return: ' . $data['return_notes'];
        }

        $returnData = [
            'returned_date' => $returnDate,
            'notes' => $notes,
            'updated_by' => $currentUserPn,
            'updated_at' => now(),
        ];

        $updatedAssignment = $this->assignmentRepository->returnDevice($assignmentId, $returnData);

        // Update device status to "Tidak Digunakan" when returned
        $this->deviceRepository->update($assignment->device_id, [
            'status' => 'Tidak Digunakan',
            'updated_by' => $currentUserPn,
            'updated_at' => now(),
        ]);

        // Log the return
        $this->logAssignmentAction($updatedAssignment, 'UPDATE', $oldData, $updatedAssignment->toArray());

        return [
            'assignmentId' => $updatedAssignment->assignment_id,
            'returnedDate' => $updatedAssignment->returned_date,
            'message' => 'Device returned successfully.',
        ];
    }

    public function getAssignmentDetails(int $id): array
    {
        $assignment = $this->assignmentRepository->findById($id);
        
        if (!$assignment) {
            throw new \Exception('Assignment not found');
        }

        // Get assignment letter data if exists (get the latest one)
        $assignmentLetter = $assignment->assignmentLetters->first();
        $assignmentLetterData = null;
        
        if ($assignmentLetter) {
            $assignmentLetterData = [
                'letterId' => $assignmentLetter->letter_id,
                'letterType' => $assignmentLetter->letter_type,
                'approverName' => $assignmentLetter->approver->name ?? null,
                'creator' => $assignmentLetter->creator->name ?? null,
                'updater' => $assignmentLetter->updater->name ?? 'N/A',
                'createdAt' => $assignmentLetter->created_at,
                'hasFile' => !isset($assignmentLetter->file_path),
            ];
        }

        return [
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
            'assignmentLetterData' => $assignmentLetterData,
        ];
    }

    private function logAssignmentAction($assignment, string $actionType, ?array $oldValue, ?array $newValue): void
    {
        InventoryLog::create([
            'changed_fields' => 'device_assignments',
            'action_type' => $actionType,
            'old_value' => $oldValue ? json_encode($oldValue) : null,
            'new_value' => $newValue ? json_encode($newValue) : null,
            'created_by' => $this->getCurrentUserPn(),
            'created_at' => now(),
        ]);
    }

    private function getCurrentUserPn(): string
    {
        return auth()->user()?->pn ?? session('authenticated_user.pn') ?? 'api-system';
    }
}
