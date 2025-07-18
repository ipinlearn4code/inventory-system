<?php

namespace App\Services;

use App\Models\AssignmentLetter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdfPreviewService
{
    public function __construct(
        private readonly MinioStorageService $minioService
    ) {}

    /**
     * Get PDF preview data for assignment letter
     */
    public function getPreviewData(AssignmentLetter $record): array
    {
        if (!$record->hasFile()) {
            return [
                'hasFile' => false,
                'message' => 'No file attached',
                'previewUrl' => null,
                'downloadUrl' => null,
                'fileName' => null,
            ];
        }

        $fileUrl = $record->getFileUrl();
        $fileName = $this->extractFileName($record->file_path);

        return [
            'hasFile' => true,
            'message' => 'PDF file available',
            'previewUrl' => $fileUrl,
            'downloadUrl' => $fileUrl,
            'fileName' => $fileName,
            'fileSize' => $this->getFileSize($record),
        ];
    }

    /**
     * Extract filename from file path
     */
    private function extractFileName(string $filePath): string
    {
        return basename($filePath);
    }

    /**
     * Get file size in human readable format
     */
    private function getFileSize(AssignmentLetter $record): string
    {
        try {
            if (Storage::disk('minio')->exists($record->file_path)) {
                $size = Storage::disk('minio')->size($record->file_path);
                return $this->formatBytes($size);
            }
        } catch (\Exception $e) {
            // Fallback if file size cannot be determined
        }
        
        return 'Unknown size';
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Generate print URL for PDF
     */
    public function getPrintUrl(AssignmentLetter $record): ?string
    {
        $fileUrl = $record->getFileUrl();
        
        if (!$fileUrl) {
            return null;
        }

        // Add print parameter to open PDF in print mode
        return $fileUrl . '#toolbar=1&navpanes=0&scrollbar=0&view=FitH&print=true';
    }
}
