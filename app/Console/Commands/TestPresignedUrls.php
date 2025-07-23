<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PdfPreviewService;
use App\Services\MinioStorageService;
use App\Models\AssignmentLetter;

class TestPresignedUrls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:presigned-urls';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test presigned URL generation for assignment letters';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing presigned URL generation...');

        try {
            // Get an assignment letter with a file
            $letter = AssignmentLetter::whereNotNull('file_path')->first();
            
            if (!$letter) {
                $this->info('No assignment letters with files found. Creating test data...');
                
                // For testing, let's check if MinIO service works directly
                $minioService = app(MinioStorageService::class);
                $testPath = 'assignment-letters/test/test-file.pdf';
                
                $this->info('Testing MinIO temporary URL generation...');
                $tempUrl = $minioService->getTemporaryUrl($testPath, 30);
                
                if ($tempUrl) {
                    $this->info('✅ MinIO temporary URL generated successfully');
                    $this->line('Test URL: ' . substr($tempUrl, 0, 100) . '...');
                } else {
                    $this->warn('⚠️  MinIO temporary URL generation returned null (expected for non-existent file)');
                }
                
                return;
            }

            $this->info('Found assignment letter with file: ' . $letter->letter_id);
            $this->info('File path: ' . $letter->file_path);

            // Test PdfPreviewService
            $pdfService = app(PdfPreviewService::class);
            
            $this->info('Testing PdfPreviewService...');
            $previewData = $pdfService->getPreviewData($letter);
            
            $this->info('Preview data results:');
            $this->line('- Has file: ' . ($previewData['hasFile'] ? 'Yes' : 'No'));
            $this->line('- File name: ' . ($previewData['fileName'] ?? 'N/A'));
            $this->line('- File size: ' . ($previewData['fileSize'] ?? 'N/A'));
            
            if (isset($previewData['downloadUrl']) && $previewData['downloadUrl']) {
                $this->info('✅ Download URL generated successfully');
                $this->line('Download URL: ' . substr($previewData['downloadUrl'], 0, 100) . '...');
                
                // Check if URL contains expected parameters
                if (strpos($previewData['downloadUrl'], 'X-Amz-Expires') !== false) {
                    $this->info('✅ URL appears to be a presigned URL (contains X-Amz-Expires)');
                } else {
                    $this->warn('⚠️  URL does not appear to be presigned');
                }
            } else {
                $this->error('❌ Download URL generation failed');
            }

            // Test direct download URL method
            $this->info('Testing direct download URL method...');
            $directDownloadUrl = $pdfService->getDownloadUrl($letter, 30);
            
            if ($directDownloadUrl) {
                $this->info('✅ Direct download URL generated successfully');
                $this->line('Direct URL: ' . substr($directDownloadUrl, 0, 100) . '...');
            } else {
                $this->error('❌ Direct download URL generation failed');
            }

            // Test print URL
            $this->info('Testing print URL...');
            $printUrl = $pdfService->getPrintUrl($letter);
            
            if ($printUrl) {
                $this->info('✅ Print URL generated successfully');
                $this->line('Print URL: ' . substr($printUrl, 0, 100) . '...');
            } else {
                $this->error('❌ Print URL generation failed');
            }

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile() . ':' . $e->getLine());
        }
    }
}
