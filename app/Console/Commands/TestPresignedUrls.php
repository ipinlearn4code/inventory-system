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
    protected $description = 'Test internal file URL generation for assignment letters';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing internal file URL generation...');

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
                $this->line('Download URL: ' . $previewData['downloadUrl']);
                
                // Check if URL is internal Laravel route
                if (strpos($previewData['downloadUrl'], route('assignment-letter.download', ['letter' => 0])) !== false) {
                    $this->info('✅ URL is internal Laravel route');
                } else {
                    $this->warn('⚠️  URL is not internal Laravel route');
                }
            } else {
                $this->error('❌ Download URL generation failed');
            }

            // Test preview URL
            if (isset($previewData['previewUrl']) && $previewData['previewUrl']) {
                $this->info('✅ Preview URL generated successfully');
                $this->line('Preview URL: ' . $previewData['previewUrl']);
            } else {
                $this->error('❌ Preview URL generation failed');
            }

            // Test direct download URL method (deprecated)
            $this->info('Testing direct download URL method (deprecated)...');
            $directDownloadUrl = $pdfService->getDownloadUrl($letter, 30);
            
            if ($directDownloadUrl) {
                $this->info('✅ Direct download URL generated successfully (fallback)');
                $this->line('Direct URL: ' . substr($directDownloadUrl, 0, 100) . '...');
            } else {
                $this->error('❌ Direct download URL generation failed');
            }

            // Test print URL
            $this->info('Testing print URL...');
            $printUrl = $pdfService->getPrintUrl($letter);
            
            if ($printUrl) {
                $this->info('✅ Print URL generated successfully');
                $this->line('Print URL: ' . $printUrl);
            } else {
                $this->error('❌ Print URL generation failed');
            }

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile() . ':' . $e->getLine());
        }
    }
}
