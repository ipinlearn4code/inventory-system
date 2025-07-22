<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssignmentLetter;
use App\Services\MinioStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class StorageController extends Controller
{
    protected MinioStorageService $minioService;

    public function __construct(MinioStorageService $minioService)
    {
        $this->minioService = $minioService;
    }

    /**
     * Upload assignment letter file to MinIO
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadAssignmentLetter(Request $request): JsonResponse
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:pdf,jpg,jpeg|max:10240', // 10MB max
                'assignment_id' => 'required|integer|exists:device_assignments,assignment_id',
                'letter_type' => 'required|string|in:assignment,return',
                'letter_number' => 'required|string|max:100',
                'letter_date' => 'required|date',
                'approver_id' => 'required|integer|exists:users,user_id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check MinIO availability
            if (!$this->minioService->isAvailable()) {
                return response()->json([
                    'success' => false,
                    'message' => 'File storage service is currently unavailable',
                    'error_code' => 'STORAGE_UNAVAILABLE'
                ], 503);
            }

            $file = $request->file('file');
            
            // Create assignment letter record
            $assignmentLetter = AssignmentLetter::create([
                'assignment_id' => $request->input('assignment_id'),
                'letter_type' => $request->input('letter_type'),
                'letter_number' => $request->input('letter_number'),
                'letter_date' => Carbon::parse($request->input('letter_date')),
                'approver_id' => $request->input('approver_id'),
                'created_at' => now(),
                'created_by' => auth()->id(),
                'updated_at' => now(),
                'updated_by' => auth()->id(),
            ]);

            // Store file using the model method
            $filePath = $assignmentLetter->storeFile($file);

            if (!$filePath) {
                $assignmentLetter->delete();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to store file',
                    'error_code' => 'STORAGE_FAILED'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Assignment letter uploaded successfully',
                'data' => [
                    'letter_id' => $assignmentLetter->getKey(),
                    'file_path' => $filePath,
                    'download_url' => route('api.minio.download.assignment-letter', $assignmentLetter->getKey())
                ]
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Assignment letter upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error occurred',
                'error_code' => 'INTERNAL_ERROR'
            ], 500);
        }
    }

    /**
     * Download assignment letter file from MinIO
     *
     * @param int $letterId
     * @return Response|JsonResponse
     */
    public function downloadAssignmentLetter(int $letterId)
    {
        try {
            $assignmentLetter = AssignmentLetter::find($letterId);

            if (!$assignmentLetter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assignment letter not found',
                    'error_code' => 'LETTER_NOT_FOUND'
                ], 404);
            }

            if (!$assignmentLetter->hasFile()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No file attached to this assignment letter',
                    'error_code' => 'FILE_NOT_FOUND'
                ], 404);
            }

            // Check if file exists in MinIO
            if (!Storage::disk('minio')->exists($assignmentLetter->getAttribute('file_path'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found in storage',
                    'error_code' => 'FILE_NOT_FOUND'
                ], 404);
            }

            // Get file content and stream it
            $fileContent = Storage::disk('minio')->get($assignmentLetter->getAttribute('file_path'));
            $fileName = basename($assignmentLetter->getAttribute('file_path'));
            
            return response($fileContent)
                ->header('Content-Type', 'application/octet-stream')
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        } catch (\Exception $e) {
            \Log::error('Assignment letter download failed', [
                'letter_id' => $letterId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to download file',
                'error_code' => 'DOWNLOAD_FAILED'
            ], 500);
        }
    }

    /**
     * Get assignment letter file URL (temporary signed URL)
     *
     * @param int $letterId
     * @return JsonResponse
     */
    public function getAssignmentLetterUrl(int $letterId): JsonResponse
    {
        try {
            $assignmentLetter = AssignmentLetter::find($letterId);

            if (!$assignmentLetter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assignment letter not found',
                    'error_code' => 'LETTER_NOT_FOUND'
                ], 404);
            }

            if (!$assignmentLetter->hasFile()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No file attached to this assignment letter',
                    'error_code' => 'FILE_NOT_FOUND'
                ], 404);
            }

            $url = $assignmentLetter->getFileUrl();

            if (!$url) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to generate file URL',
                    'error_code' => 'URL_GENERATION_FAILED'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'url' => $url,
                    'expires_at' => now()->addMinutes(60)->toISOString(),
                    'letter_info' => [
                        'letter_id' => $assignmentLetter->getKey(),
                        'letter_type' => $assignmentLetter->getAttribute('letter_type'),
                        'letter_number' => $assignmentLetter->getAttribute('letter_number'),
                        'letter_date' => $assignmentLetter->getAttribute('letter_date')
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Assignment letter URL generation failed', [
                'letter_id' => $letterId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate file URL',
                'error_code' => 'URL_GENERATION_FAILED'
            ], 500);
        }
    }

    /**
     * Upload general file to MinIO (for other use cases)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadFile(Request $request): JsonResponse
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|max:10240', // 10MB max
                'directory' => 'sometimes|string|max:255',
                'filename' => 'sometimes|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check MinIO availability
            if (!$this->minioService->isAvailable()) {
                return response()->json([
                    'success' => false,
                    'message' => 'File storage service is currently unavailable',
                    'error_code' => 'STORAGE_UNAVAILABLE'
                ], 503);
            }

            $file = $request->file('file');
            $directory = $request->get('directory', 'general');
            $filename = $request->get('filename', $file->getClientOriginalName());

            // Store file
            $path = $file->storeAs($directory, $filename, 'minio');

            if (!$path) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to store file',
                    'error_code' => 'STORAGE_FAILED'
                ], 500);
            }

            // Generate temporary URL
            $temporaryUrl = $this->minioService->getTemporaryUrl($path);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => [
                    'path' => $path,
                    'url' => $temporaryUrl,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'original_name' => $file->getClientOriginalName()
                ]
            ], 201);

        } catch (\Exception $e) {
            \Log::error('General file upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error occurred',
                'error_code' => 'INTERNAL_ERROR'
            ], 500);
        }
    }

    /**
     * Download general file from MinIO
     *
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function downloadFile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'path' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'File path is required',
                    'errors' => $validator->errors()
                ], 422);
            }

            $path = $request->get('path');

            // Check if file exists
            if (!Storage::disk('minio')->exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found',
                    'error_code' => 'FILE_NOT_FOUND'
                ], 404);
            }

            // Stream the file
            $fileContent = Storage::disk('minio')->get($path);
            $fileName = basename($path);
            
            return response($fileContent)
                ->header('Content-Type', 'application/octet-stream')
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        } catch (\Exception $e) {
            \Log::error('General file download failed', [
                'path' => $request->get('path'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to download file',
                'error_code' => 'DOWNLOAD_FAILED'
            ], 500);
        }
    }

    /**
     * Check MinIO storage health status
     *
     * @return JsonResponse
     */
    public function healthCheck(): JsonResponse
    {
        try {
            $health = $this->minioService->isHealthy();

            return response()->json([
                'success' => $health['status'] === 'healthy',
                'data' => $health
            ], $health['status'] === 'healthy' ? 200 : 503);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Health check failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete file from MinIO
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteFile(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'path' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'File path is required',
                    'errors' => $validator->errors()
                ], 422);
            }

            $path = $request->get('path');
            $deleted = $this->minioService->deleteFile($path);

            return response()->json([
                'success' => $deleted,
                'message' => $deleted ? 'File deleted successfully' : 'Failed to delete file'
            ], $deleted ? 200 : 500);

        } catch (\Exception $e) {
            \Log::error('File deletion failed', [
                'path' => $request->get('path'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete file',
                'error_code' => 'DELETE_FAILED'
            ], 500);
        }
    }
}
