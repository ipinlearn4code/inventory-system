<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class FileUploadTestController extends Controller
{
    public function testBasicUpload(Request $request)
    {
        \Log::info('testBasicUpload called', [
            'method' => $request->method(),
            'has_file' => $request->hasFile('test_file'),
            'all_data' => $request->all()
        ]);
        
        try {
            // Step 1: Test basic file upload validation
            $request->validate([
                'test_file' => 'required|file|mimes:pdf|max:5120'
            ]);

            $file = $request->file('test_file');
            
            // Log file details
            \Log::info('File upload test details', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'extension' => $file->getClientOriginalExtension()
            ]);

            // Step 2: Store to public disk
            $path = $file->store('temp-uploads', 'public');
            
            if ($path) {
                \Log::info('File stored successfully', ['path' => $path]);
                
                // Check if file exists
                if (Storage::disk('public')->exists($path)) {
                    return response()->json([
                        'success' => true,
                        'message' => 'File uploaded successfully',
                        'path' => $path,
                        'url' => Storage::disk('public')->url($path),
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType()
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'File was stored but cannot be found'
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to store file'
                ]);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error('File upload test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testMinioUpload(Request $request)
    {
        try {
            // Test MinIO storage
            $request->validate([
                'test_file' => 'required|file|mimes:pdf|max:5120'
            ]);

            $file = $request->file('test_file');
            
            // Step 1: Create MinIO storage service
            $minioService = app(\App\Services\MinioStorageService::class);
            
            // Step 2: Test MinIO upload
            $path = $minioService->storeAssignmentLetterFile(
                $file,
                'test',
                999,
                now(),
                'TEST-UPLOAD'
            );
            
            if ($path) {
                // Test URL generation
                $url = $minioService->getTemporaryUrl($path, 60);
                
                return response()->json([
                    'success' => true,
                    'message' => 'MinIO upload successful',
                    'path' => $path,
                    'url' => $url
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'MinIO upload failed'
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('MinIO upload test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'MinIO upload failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
