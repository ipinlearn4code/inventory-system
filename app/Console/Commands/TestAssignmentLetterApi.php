<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\AssignmentLetterFileController;
use App\Models\AssignmentLetter;

class TestAssignmentLetterApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:assignment-letter-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test assignment letter API endpoints';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Assignment Letter API endpoints...');

        try {
            // Get an assignment letter with a file
            $letter = AssignmentLetter::whereNotNull('file_path')->first();
            
            if (!$letter) {
                $this->warn('No assignment letters with files found.');
                
                // Get any assignment letter for testing
                $letter = AssignmentLetter::first();
                if (!$letter) {
                    $this->error('No assignment letters found in database.');
                    return;
                }
            }

            $this->info('Found assignment letter:');
            $this->line('- Letter ID: ' . $letter->letter_id);
            $this->line('- Assignment ID: ' . $letter->assignment_id);
            $this->line('- Letter Type: ' . $letter->letter_type);
            $this->line('- Has File: ' . ($letter->hasFile() ? 'Yes' : 'No'));

            // Test the controller methods
            $controller = app(AssignmentLetterFileController::class);

            // Test getAssignmentLetterData (by assignment ID)
            $this->info('Testing getAssignmentLetterData method...');
            $response1 = $controller->getAssignmentLetterData($letter->assignment_id);
            $data1 = $response1->getData(true);
            
            $this->info('Response by assignment ID:');
            $this->line('- Success: ' . ($data1['success'] ? 'Yes' : 'No'));
            $this->line('- Message: ' . $data1['message']);
            
            if ($data1['success'] && $data1['data']) {
                $responseData = $data1['data'];
                $this->line('- Letter ID: ' . $responseData['letter_id']);
                $this->line('- Assignment ID: ' . $responseData['assignment_id']);
                $this->line('- Letter Type: ' . $responseData['letter_type']);
                $this->line('- Approver Name: ' . ($responseData['approver_name'] ?? 'N/A'));
                $this->line('- Creator: ' . ($responseData['creator'] ?? 'N/A'));
                $this->line('- Updater: ' . ($responseData['updater'] ?? 'N/A'));
                $this->line('- File URL: ' . ($responseData['file_url'] ? 'Generated' : 'N/A'));
            }

            // Test getAssignmentLetterById (by letter ID)
            $this->info('Testing getAssignmentLetterById method...');
            $response2 = $controller->getAssignmentLetterById($letter);
            $data2 = $response2->getData(true);
            
            $this->info('Response by letter ID:');
            $this->line('- Success: ' . ($data2['success'] ? 'Yes' : 'No'));
            $this->line('- Message: ' . $data2['message']);
            
            if ($data2['success'] && $data2['data']) {
                $responseData = $data2['data'];
                $this->line('- Letter ID: ' . $responseData['letter_id']);
                $this->line('- Assignment ID: ' . $responseData['assignment_id']);
                $this->line('- Letter Type: ' . $responseData['letter_type']);
                $this->line('- File URL: ' . ($responseData['file_url'] ? 'Generated' : 'N/A'));
            }

            $this->info('✅ API testing completed successfully');

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile() . ':' . $e->getLine());
        }
    }
}
