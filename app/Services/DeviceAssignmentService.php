<?php

namespace App\Services;

use App\Contracts\DeviceAssignmentRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use App\Contracts\DeviceRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Contracts\InventoryLogServiceInterface;
use App\Models\InventoryLog;
use App\Models\AssignmentLetter;
use App\Models\Device;
use App\Models\User;
use App\Services\PdfPreviewService;
use Illuminate\Support\Facades\DB;

class DeviceAssignmentService
{
    public function __construct(
        private DeviceAssignmentRepositoryInterface $assignmentRepository,
        private DeviceRepositoryInterface $deviceRepository,
        private UserRepositoryInterface $userRepository
    ) {
    }

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
            'assetCode' => $device->asset_code,
            'brand' => $device->brand,
            'brandName' => $device->brand_name,
            'serialNumber' => $device->serial_number,
            'userId' => $assignment->user_id,
            'assignedTo' => $user->name,
            'unitName' => $user->branch?->unit_name ?? 'N/A',
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

        $oldData = $assignment->toArray();
        // Get device status before update for comparison and logging
        $device = $this->deviceRepository->findById($assignment->device_id);
        if (!$device) {
            throw new \Exception('Associated device not found');
        }
        $oldStatus = $device->status;

        $updateData = $data;

        $updatedAssignment = $this->assignmentRepository->update($id, $updateData);

        // Update device status if it's changing
                // if (isset($data['status']) && $data['status'] !== $oldStatus) {
                        //     $this->deviceRepository->update($assignment->device_id, [
                                //         'status' => $data['status'],
                                        //         'updated_by' => $currentUserPn,
                                                //         'updated_at' => now(),
                                                        //     ]);
                                                                // }

        // Log the update
        $this->logAssignmentAction($updatedAssignment, 'UPDATE', $oldData, $updatedAssignment->toArray());

        return [
            'assignmentId' => $updatedAssignment->assignment_id,
            'status' => $updatedAssignment->device->status,
            'returnedDate' => $updatedAssignment->returned_date,
            'notes' => $updatedAssignment->notes,
            'userId' => $updatedAssignment->user_id,
            'oldStatus' => $oldStatus, // Include old status for logging
        ];
    }

    public function returnDevice(int $assignmentId, array $data): array
    {
        $assignment = $this->assignmentRepository->findById($assignmentId);
        if (!$assignment) {
            throw new \Exception('Assignment not found');
        }

        if ($assignment->returned_date) {
            throw new \Exception('Device has already been returned before.');
        }

        $currentUserPn = $data['updated_by'];
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

        // if ($assignmentLetter) {
        //     $assignmentLetterData = [
        //         'letterId' => $assignmentLetter->letter_id,
        //         'letterType' => $assignmentLetter->letter_type,
        //         'approverName' => $assignmentLetter->approver->name ?? null,
        //         'creator' => $assignmentLetter->creator->name ?? null,
        //         'updater' => $assignmentLetter->updater->name ?? 'N/A',
        //         'createdAt' => $assignmentLetter->created_at,
        //         'hasFile' => !isset($assignmentLetter->file_path),
        //     ];
        // }

        return [
            'assignmentId' => $assignment->assignment_id,
            'deviceId' => $assignment->device->device_id,
            'assetCode' => $assignment->device->asset_code,
            'brand' => $assignment->device->brand . ' ' . $assignment->device->brand_name,
            'serialNumber' => $assignment->device->serial_number,
            'assignedTo' => $assignment->user->name,
            'unitName' => $assignment->user->branch->unit_name,
            'assignedDate' => $assignment->assigned_date,
            'returnedDate' => $assignment->returned_date,
            'status' => $assignment->status,
            'notes' => $assignment->notes,
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

    /**
     * Update assignment with optional letter data and file upload
     */
    public function updateAssignmentWithLetter(int $id, array $validated, $request): array
    {
        return DB::transaction(function () use ($id, $validated, $request) {
            $assignmentData = null;
            
            // Update the device assignment if assignment-related fields are provided
            if ($request->hasAny(['assigned_date', 'notes'])) {
                $assignmentUpdateData = [
                    'updated_at' => now(),
                    'updated_by' => User::where('user_id', Auth::id())->value('pn')
                ];
                
                // Only include fields that are actually present in the request
                if ($request->has('assigned_date')) {
                    $assignmentUpdateData['assigned_date'] = $validated['assigned_date'];
                }
                if ($request->has('notes')) {
                    $assignmentUpdateData['notes'] = $validated['notes'];
                }
                
                // Process the assignment update
                $assignmentData = $this->updateAssignment($id, $assignmentUpdateData);
                
                // Get existing assignment for user PN for logging
                $existingAssignment = $this->assignmentRepository->findById($id);
                $userPn = $existingAssignment?->user?->pn;
                
                app(InventoryLogServiceInterface::class)->logAssignmentAction(
                    $assignmentData ?: ['assignmentId' => $id],
                    InventoryLog::ACTION_TYPES['UPDATE'],
                    null, // old_value for assignment update
                    $assignmentUpdateData, // new_value is the updated assignment data
                    $userPn // user_affected
                );
            }

            // Update or create the assignment letter if letter-related data is provided
            if ($request->hasAny(['letter_number', 'letter_date', 'letter_file'])) {
                $letterData = $this->handleAssignmentLetter($id, $validated, $request);
                
                // Return assignment data with the letter in an array (consistent with store method)
                if ($assignmentData) {
                    return array_merge($assignmentData, ['assignmentLetters' => [$letterData]]);
                } else {
                    // If only letter was updated, get assignment data
                    $existingAssignment = $this->getAssignmentDetails($id);
                    return array_merge($existingAssignment, ['assignmentLetters' => [$letterData]]);
                }
            }

            // Return assignment data if no letter update
            return $assignmentData ?: $this->getAssignmentDetails($id);
        });
    }

    /**
     * Handle assignment letter creation or update
     */
    private function handleAssignmentLetter(int $assignmentId, array $validated, $request): array
    {
        // Get existing letter or create new one
        $letter = AssignmentLetter::where('assignment_id', $assignmentId)->first() ??
            new AssignmentLetter(['assignment_id' => $assignmentId]);

        // Update letter details if provided
        if ($request->has('letter_number')) {
            $letter->letter_number = $request->input('letter_number');
        }
        if ($request->has('letter_date')) {
            $letter->letter_date = $request->input('letter_date');
        }
        $letter->letter_type = 'assignment'; // Default for update
        $letter->approver_id = Auth::id();
        
        if ($letter->exists) {
            $letter->updated_by = Auth::id();
            $letter->updated_at = now();
        } else {
            $letter->created_by = Auth::id();
            $letter->created_at = now();
        }

        // Handle file upload with proper rollback support
        if ($request->hasFile('letter_file') && $request->file('letter_file')->isValid()) {
            if ($letter->exists) {
                // Update existing file with rollback protection
                $uploadResult = $letter->updateFile($request->file('letter_file'));
                if (!$uploadResult['success']) {
                    throw new \Exception('Failed to update letter file: ' . $uploadResult['message']);
                }
            } else {
                // Store new file for new letter
                $path = $letter->storeFile($request->file('letter_file'));
                if (!$path) {
                    throw new \Exception('Failed to store letter file');
                }
                $letter->file_path = $path;
            }
        }

        $letter->save();

        // Get the letter data with file URL (consistent with store method)
        $pdfPreviewService = app(PdfPreviewService::class);
        return [
            'assignmentLetterId' => $letter->getKey(),
            'assignmentType' => $letter->getAttribute('letter_type'),
            'letterNumber' => $letter->getAttribute('letter_number'),
            'letterDate' => $letter->getAttribute('letter_date'),
            'fileUrl' => $letter->hasFile() ? $pdfPreviewService->getPreviewData($letter)['previewUrl'] : null,
        ];
    }

    /**
     * Create assignment with letter data and file upload
     */
    public function createAssignmentWithLetter(array $validated, $request): array
    {
        return DB::transaction(function () use ($validated, $request) {
            // Create the device assignment first
            $assignmentData = $this->createAssignment($validated);

            $userPn = User::find($validated['user_id'])->pn;

            app(InventoryLogServiceInterface::class)->logAssignmentAction(
                $assignmentData,
                InventoryLog::ACTION_TYPES['CREATE'],
                null, // old_value not needed for creation
                $assignmentData, // new_value is the assignment data
                $userPn // user_affected is the user being assigned the device
            );

            // Update device status using repository
            $this->deviceRepository->update($validated['device_id'], ['status' => 'Digunakan']);

            // Create the assignment letter
            $letter = new AssignmentLetter([
                'assignment_id' => $assignmentData['assignmentId'],
                'letter_type' => 'assignment', // Default for assignment letter
                'letter_number' => $request->input('letter_number'),
                'letter_date' => $request->input('letter_date'),
                'file_path' => null, // Will be set after file upload
                'approver_id' => Auth::id(), // Get from authenticated user
                'created_by' => Auth::id(),
                'created_at' => now(),
            ]);

            // Store the letter file if present
            if ($request->hasFile('letter_file') && $request->file('letter_file')->isValid()) {
                $path = $letter->storeFile($request->file('letter_file'));
                $letter->file_path = $path;
            }

            $letter->save();

            // Get the letter data with file URL
            $pdfPreviewService = app(PdfPreviewService::class);
            $letterData = [
                'assignmentLetterId' => $letter->getKey(),
                'assignmentType' => $letter->getAttribute('letter_type'),
                'letterNumber' => $letter->getAttribute('letter_number'),
                'letterDate' => $letter->getAttribute('letter_date'),
                'fileUrl' => $letter->hasFile() ? $pdfPreviewService->getPreviewData($letter)['previewUrl'] : null,
            ];

            // Return assignment data with the newly created letter in an array
            return array_merge($assignmentData, ['assignmentLetters' => [$letterData]]);
        });
    }

    /**
     * Return device with letter creation
     */
    public function returnDeviceWithLetter(int $assignmentId, array $validated, $request): array
    {
        return DB::transaction(function () use ($assignmentId, $validated, $request) {
            // Process the device return first
            $data = $this->returnDevice($assignmentId, $validated);

            // Update the device status to "Cadangan" using repository
            $deviceId = $this->assignmentRepository->findById($assignmentId)?->device_id;
            $this->deviceRepository->update($deviceId, ['status' => 'Cadangan']);

            // Create the return letter
            $letter = new AssignmentLetter([
                'assignment_id' => $assignmentId,
                'letter_type' => 'return', // Return letter type
                'letter_number' => $request->input('letter_number'),
                'letter_date' => $request->input('letter_date'),
                'approver_id' => Auth::id(),
                'file_path' => null, // Will be set after file upload
                'created_by' => Auth::id(),
                'created_at' => now(),
            ]);

            // Handle file upload if present
            if ($request->hasFile('letter_file') && $request->file('letter_file')->isValid()) {
                $path = $letter->storeFile($request->file('letter_file'));
                $letter->file_path = $path;
            }

            $letter->save();

            // Get the letter data with file URL
            $pdfPreviewService = app(PdfPreviewService::class);
            $letterData = [
                'assignmentLetterId' => $letter->getKey(),
                'assignmentType' => $letter->getAttribute('letter_type'),
                'letterNumber' => $letter->getAttribute('letter_number'),
                'letterDate' => $letter->getAttribute('letter_date'),
                'fileUrl' => $letter->hasFile() ? $pdfPreviewService->getPreviewData($letter)['previewUrl'] : null,
            ];

            // Combine return data with the newly created letter
            return array_merge($data, ['assignmentLetter' => $letterData]);
        });
    }

    private function getCurrentUserPn(): string
    {
        // return Auth::id();
        return 'system';
    }
}
