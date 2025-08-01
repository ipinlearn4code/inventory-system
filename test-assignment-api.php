<?php
/**
 * DeviceAssignment API Test Script
 * 
 * This script performs real API testing for the device assignment endpoints.
 * It tests login, create assignment, list assignments, get by ID, and update operations.
 * 
 * Usage: php test-assignment-api.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;

class DeviceAssignmentApiTester
{
    private string $baseUrl = 'http://localhost:8000/api/v1';
    private ?string $token = null;
    private array $testResults = [];
    private int $createdAssignmentId;

    public function run(): void
    {
        echo "=== DeviceAssignment API Test Suite ===\n\n";
        
        $this->testLogin();
        $this->testCreateAssignment();
        $this->testGetAssignments();
        $this->testGetAssignmentById();
        $this->testUpdateAssignment();
        $this->testUpdateAssignmentWithFile();
        
        $this->printSummary();
    }

    private function testLogin(): void
    {
        echo "1. Testing Login...\n";
        
        try {
            $response = Http::post("{$this->baseUrl}/auth/login", [
                'email' => 'admin@example.com', // Change to your test admin email
                'password' => 'password'         // Change to your test admin password
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->token = $data['token'];
                $this->testResults['login'] = '✅ PASS';
                echo "   Token received: " . substr($this->token, 0, 20) . "...\n";
            } else {
                $this->testResults['login'] = '❌ FAIL - ' . $response->body();
                echo "   Error: " . $response->body() . "\n";
                exit(1);
            }
        } catch (Exception $e) {
            $this->testResults['login'] = '❌ FAIL - ' . $e->getMessage();
            echo "   Exception: " . $e->getMessage() . "\n";
            exit(1);
        }
        
        echo "\n";
    }

    private function testCreateAssignment(): void
    {
        echo "2. Testing Create Assignment...\n";
        
        try {
            $response = Http::withToken($this->token)
                ->attach('letter_file', file_get_contents(__DIR__ . '/test-letter.pdf'), 'test-letter.pdf') // Create a test PDF file
                ->post("{$this->baseUrl}/admin/device-assignments", [
                    'device_id' => 1,              // Change to existing device ID
                    'user_id' => 2,                // Change to existing user ID
                    'assigned_date' => date('Y-m-d'),
                    'notes' => 'Test assignment via API',
                    'letter_number' => 'TEST-' . time(),
                    'letter_date' => date('Y-m-d')
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->createdAssignmentId = $data['data']['assignmentId'];
                $this->testResults['create'] = '✅ PASS';
                echo "   Assignment created with ID: {$this->createdAssignmentId}\n";
                echo "   Device: {$data['data']['assetCode']} - {$data['data']['brand']}\n";
                echo "   User: {$data['data']['assignedTo']}\n";
            } else {
                $this->testResults['create'] = '❌ FAIL - ' . $response->body();
                echo "   Error: " . $response->body() . "\n";
            }
        } catch (Exception $e) {
            $this->testResults['create'] = '❌ FAIL - ' . $e->getMessage();
            echo "   Exception: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    private function testGetAssignments(): void
    {
        echo "3. Testing Get Assignments List...\n";
        
        try {
            $response = Http::withToken($this->token)
                ->get("{$this->baseUrl}/admin/device-assignments");

            if ($response->successful()) {
                $data = $response->json();
                $count = count($data['data']);
                $this->testResults['list'] = '✅ PASS';
                echo "   Retrieved {$count} assignments\n";
                
                if ($count > 0) {
                    echo "   Sample assignment: ID {$data['data'][0]['assignmentId']}\n";
                }
            } else {
                $this->testResults['list'] = '❌ FAIL - ' . $response->body();
                echo "   Error: " . $response->body() . "\n";
            }
        } catch (Exception $e) {
            $this->testResults['list'] = '❌ FAIL - ' . $e->getMessage();
            echo "   Exception: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    private function testGetAssignmentById(): void
    {
        if (!isset($this->createdAssignmentId)) {
            echo "4. Skipping Get Assignment by ID (no created assignment)\n\n";
            $this->testResults['get_by_id'] = '⚠️ SKIP';
            return;
        }
        
        echo "4. Testing Get Assignment by ID...\n";
        
        try {
            $response = Http::withToken($this->token)
                ->get("{$this->baseUrl}/admin/device-assignments/{$this->createdAssignmentId}");

            if ($response->successful()) {
                $data = $response->json();
                $this->testResults['get_by_id'] = '✅ PASS';
                echo "   Retrieved assignment: {$data['data']['assetCode']}\n";
                echo "   Status: " . ($data['data']['returnedDate'] ? 'Returned' : 'Active') . "\n";
                
                if (isset($data['data']['assignmentLetters']) && count($data['data']['assignmentLetters']) > 0) {
                    echo "   Letter: {$data['data']['assignmentLetters'][0]['letterNumber']}\n";
                }
            } else {
                $this->testResults['get_by_id'] = '❌ FAIL - ' . $response->body();
                echo "   Error: " . $response->body() . "\n";
            }
        } catch (Exception $e) {
            $this->testResults['get_by_id'] = '❌ FAIL - ' . $e->getMessage();
            echo "   Exception: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    private function testUpdateAssignment(): void
    {
        if (!isset($this->createdAssignmentId)) {
            echo "5. Skipping Update Assignment (no created assignment)\n\n";
            $this->testResults['update'] = '⚠️ SKIP';
            return;
        }
        
        echo "5. Testing Update Assignment...\n";
        
        try {
            $response = Http::withToken($this->token)
                ->put("{$this->baseUrl}/admin/device-assignments/{$this->createdAssignmentId}", [
                    'notes' => 'Updated test assignment notes',
                    'letter_number' => 'UPDATED-' . time()
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->testResults['update'] = '✅ PASS';
                echo "   Assignment updated successfully\n";
                echo "   Notes: {$data['notes']}\n";
                
                if (isset($data['assignmentLetters']) && count($data['assignmentLetters']) > 0) {
                    echo "   Updated letter: {$data['assignmentLetters'][0]['letterNumber']}\n";
                }
            } else {
                $this->testResults['update'] = '❌ FAIL - ' . $response->body();
                echo "   Error: " . $response->body() . "\n";
            }
        } catch (Exception $e) {
            $this->testResults['update'] = '❌ FAIL - ' . $e->getMessage();
            echo "   Exception: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    private function testUpdateAssignmentWithFile(): void
    {
        if (!isset($this->createdAssignmentId)) {
            echo "6. Skipping Update Assignment with File (no created assignment)\n\n";
            $this->testResults['update_file'] = '⚠️ SKIP';
            return;
        }
        
        echo "6. Testing Update Assignment with File...\n";
        
        try {
            // Create a simple test PDF content
            $testPdfContent = "%PDF-1.4\n1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n2 0 obj\n<<\n/Type /Pages\n/Kids [3 0 R]\n/Count 1\n>>\nendobj\n3 0 obj\n<<\n/Type /Page\n/Parent 2 0 R\n/MediaBox [0 0 612 792]\n>>\nendobj\nxref\n0 4\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \ntrailer\n<<\n/Size 4\n/Root 1 0 R\n>>\nstartxref\n174\n%%EOF";
            
            $response = Http::withToken($this->token)
                ->attach('letter_file', $testPdfContent, 'updated-letter.pdf')
                ->put("{$this->baseUrl}/admin/device-assignments/{$this->createdAssignmentId}", [
                    'notes' => 'Updated with new file',
                    'letter_number' => 'FILE-UPDATE-' . time(),
                    'letter_date' => date('Y-m-d')
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->testResults['update_file'] = '✅ PASS';
                echo "   Assignment updated with file successfully\n";
                
                if (isset($data['assignmentLetters']) && count($data['assignmentLetters']) > 0) {
                    $letter = $data['assignmentLetters'][0];
                    echo "   Letter: {$letter['letterNumber']}\n";
                    echo "   File URL: " . ($letter['fileUrl'] ? 'Present' : 'Missing') . "\n";
                }
            } else {
                $this->testResults['update_file'] = '❌ FAIL - ' . $response->body();
                echo "   Error: " . $response->body() . "\n";
            }
        } catch (Exception $e) {
            $this->testResults['update_file'] = '❌ FAIL - ' . $e->getMessage();
            echo "   Exception: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    private function printSummary(): void
    {
        echo "=== Test Results Summary ===\n";
        
        foreach ($this->testResults as $test => $result) {
            echo sprintf("%-20s: %s\n", ucwords(str_replace('_', ' ', $test)), $result);
        }
        
        $passed = count(array_filter($this->testResults, fn($result) => str_starts_with($result, '✅')));
        $total = count($this->testResults);
        
        echo "\nOverall: {$passed}/{$total} tests passed\n";
        
        if ($passed === $total) {
            echo "🎉 All tests passed successfully!\n";
        } else {
            echo "⚠️ Some tests failed. Check the output above for details.\n";
        }
    }
}

// Configuration instructions
echo "=== Configuration Required ===\n";
echo "Before running this test, please ensure:\n";
echo "1. Laravel development server is running (php artisan serve)\n";
echo "2. Update login credentials in testLogin() method\n";
echo "3. Update device_id and user_id in testCreateAssignment() method\n";
echo "4. Ensure test database has sample data\n";
echo "5. Create a test PDF file at ./test-letter.pdf (optional)\n\n";

echo "Press Enter to continue or Ctrl+C to cancel...\n";
fgets(STDIN);

// Run the tests
$tester = new DeviceAssignmentApiTester();
$tester->run();
