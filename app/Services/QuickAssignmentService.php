<?php

namespace App\Services;

use App\Models\AssignmentLetter;
use App\Models\Device;
use App\Models\DeviceAssignment;
use App\Models\User;
use App\Contracts\InventoryLogServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class QuickAssignmentService
{
    public function __construct(
        private readonly MinioStorageService $minioStorageService,
        private readonly AuthenticationService $authService,
        private readonly InventoryLogServiceInterface $inventoryLogService
    ) {}

    /**
     * Create a device assignment and assignment letter in a single transaction
     */
    public function createAssignmentWithLetter(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // 1. Create device assignment
            $deviceAssignment = $this->createDeviceAssignment($data);
            
            // 2. Create assignment letter
            $assignmentLetter = $this->createAssignmentLetter($deviceAssignment, $data);
            
            // 3. Handle file upload if provided
            if (isset($data['file_path']) && $data['file_path']) {
                $this->handleFileUpload($assignmentLetter, $data['file_path']);
            }

            // 4. Log both operations - if this fails, the transaction will rollback
            $this->inventoryLogService->logAssignmentAction(
                $deviceAssignment, 
                'CREATE', 
                null, 
                $deviceAssignment->toArray()
            );

            return [
                'device_assignment' => $deviceAssignment,
                'assignment_letter' => $assignmentLetter,
            ];
        });
    }

    /**
     * Create device assignment
     */
    private function createDeviceAssignment(array $data): DeviceAssignment
    {
        $user = User::findOrFail($data['user_id']);
        
        // Use form's branch_id if provided, otherwise use user's branch_id
        $branchId = $data['branch_id'] ?? $user->branch_id;
        
        $assignment = DeviceAssignment::create([
            'device_id' => $data['device_id'],
            'user_id' => $data['user_id'],
            'branch_id' => $branchId,
            'assigned_date' => $data['assigned_date'],
            'notes' => $data['assignment_notes'] ?? null,
            'created_by' => $this->authService->getCurrentUserId(),
            'created_at' => now(),
        ]);

        // Update device status
        Device::where('device_id', $data['device_id'])->update([
            'status' => 'Digunakan',
            'updated_by' => $this->authService->getCurrentUserId(),
            'updated_at' => now(),
        ]);

        return $assignment;
    }

    /**
     * Create assignment letter
     */
    private function createAssignmentLetter(DeviceAssignment $deviceAssignment, array $data): AssignmentLetter
    {
        return AssignmentLetter::create([
            'assignment_id' => $deviceAssignment->assignment_id,
            'letter_type' => 'assignment',
            'letter_number' => $data['letter_number'],
            'letter_date' => $data['letter_date'],
            'approver_id' => $data['approver_id'],
            'created_by' => $this->authService->getCurrentUserId(),
            'created_at' => now(),
        ]);
    }

    /**
     * Handle file upload to MinIO
     */
    private function handleFileUpload(AssignmentLetter $assignmentLetter, string $filePath): void
    {
        $tempFilePath = storage_path('app/public/' . $filePath);
        
        if (!file_exists($tempFilePath)) {
            throw new Exception("Temporary file not found at {$tempFilePath}");
        }

        // Validate file
        $this->validateUploadedFile($tempFilePath);
        
        // Create UploadedFile instance
        $uploadedFile = $this->createUploadedFileFromTemp($tempFilePath, $filePath);
        
        // Store file in MinIO
        $path = $this->minioStorageService->storeAssignmentLetterFile(
            $uploadedFile,
            $assignmentLetter->letter_type,
            $assignmentLetter->assignment_id,
            $this->formatLetterDate($assignmentLetter->letter_date),
            $assignmentLetter->letter_number
        );
        
        if (!$path) {
            throw new Exception('Failed to upload assignment letter file to MinIO');
        }
        
        // Update assignment letter with file path
        $assignmentLetter->update(['file_path' => $path]);
        
        // Clean up temporary file
        Storage::disk('public')->delete($filePath);
    }

    /**
     * Validate uploaded file
     */
    private function validateUploadedFile(string $filePath): void
    {
        $mimeType = mime_content_type($filePath);
        
        if ($mimeType !== 'application/pdf') {
            throw new Exception("Invalid file type: {$mimeType}. Only PDF files are accepted.");
        }
    }

    /**
     * Create UploadedFile from temporary file
     */
    private function createUploadedFileFromTemp(string $tempFilePath, string $originalPath): UploadedFile
    {
        return new UploadedFile(
            $tempFilePath,
            basename($originalPath),
            mime_content_type($tempFilePath),
            null,
            true
        );
    }

    /**
     * Format letter date for storage
     */
    private function formatLetterDate($letterDate): string
    {
        if ($letterDate instanceof \Carbon\Carbon) {
            return $letterDate->format('Y-m-d');
        }
        
        return \Carbon\Carbon::parse((string) $letterDate)->format('Y-m-d');
    }
}
