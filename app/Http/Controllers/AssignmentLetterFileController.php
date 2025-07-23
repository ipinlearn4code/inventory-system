<?php

namespace App\Http\Controllers;

use App\Models\AssignmentLetter;
use App\Services\MinioStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssignmentLetterFileController extends Controller
{
    public function __construct(
        private readonly MinioStorageService $minioService
    ) {}

    /**
     * Preview assignment letter file (inline display)
     */
    public function preview(AssignmentLetter $letter): Response|StreamedResponse
    {
        if (!$letter->hasFile()) {
            abort(404, 'File not found');
        }

        return $this->serveFile($letter, 'inline');
    }

    /**
     * Download assignment letter file
     */
    public function download(AssignmentLetter $letter): Response|StreamedResponse
    {
        if (!$letter->hasFile()) {
            abort(404, 'File not found');
        }

        return $this->serveFile($letter, 'attachment');
    }

    /**
     * Serve file with appropriate headers
     */
    private function serveFile(AssignmentLetter $letter, string $disposition = 'inline'): Response|StreamedResponse
    {
        try {
            $filePath = $letter->file_path;
            
            if (!Storage::disk('minio')->exists($filePath)) {
                abort(404, 'File not found in storage');
            }

            $fileName = basename($filePath);
            $mimeType = $this->getMimeType($fileName);
            $fileSize = Storage::disk('minio')->size($filePath);

            // Set appropriate headers
            $headers = [
                'Content-Type' => $mimeType,
                'Content-Length' => $fileSize,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ];

            // Add Content-Disposition header
            if ($disposition === 'attachment') {
                $headers['Content-Disposition'] = 'attachment; filename="' . $fileName . '"';
            } else {
                $headers['Content-Disposition'] = 'inline; filename="' . $fileName . '"';
            }

            // Stream the file
            return new StreamedResponse(function () use ($filePath) {
                $stream = Storage::disk('minio')->readStream($filePath);
                if ($stream) {
                    fpassthru($stream);
                    fclose($stream);
                }
            }, 200, $headers);

        } catch (\Exception $e) {
            \Log::error('File serving error', [
                'letter_id' => $letter->letter_id,
                'file_path' => $letter->file_path,
                'error' => $e->getMessage()
            ]);
            
            abort(500, 'Error serving file');
        }
    }

    /**
     * Get MIME type based on file extension
     */
    private function getMimeType(string $fileName): string
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        return match ($extension) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            default => 'application/octet-stream',
        };
    }
}
