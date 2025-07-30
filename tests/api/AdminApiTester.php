<?php

/**
 * Admin API Testing Script
 * Use with: php artisan tinker
 * Then: require_once 'tests/api/AdminApiTester.php'; $tester = new AdminApiTester(); $tester->runAllTests();
 */

// use App\Models\User;
// use App\Models\Device;
// use App\Models\DeviceAssignment;
// use App\Models\Branch;
// use App\Models\Bribox;
// use App\Models\Auth;
// use Illuminate\Support\Facades\Http;
// use Illuminate\Support\Facades\DB;
// use Laravel\Sanctum\PersonalAccessToken;

// class AdminApiTester
// {
//     private $baseUrl;
//     private $adminToken;
//     private $testResults = [];
//     private $createdResources = [];

//     public function __construct()
//     {
//         $this->baseUrl = 'http://localhost:8000/api/v1';
//         $this->setupTestData();
//     }

//     /**
//      * Setup test data and get admin token
//      */
//     private function setupTestData()
//     {
//         echo "🔧 Setting up test data...\n";
        
//         // Get or create admin user
//         $adminAuth = Auth::where('pn', 'ADMIN01')->first();
//         if (!$adminAuth) {
//             $adminAuth = Auth::create([
//                 'pn' => 'ADMIN01',
//                 'password' => bcrypt('password123'),
//                 'created_at' => now(),
//             ]);
//             $adminAuth->assignRole('admin');
//         }

//         // Create token for admin
//         $user = User::where('pn', 'ADMIN01')->first();
//         if ($user) {
//             $token = $user->createToken('admin-test-token');
//             $this->adminToken = $token->plainTextToken;
//         } else {
//             echo "❌ Admin user not found!\n";
//             return;
//         }

//         echo "✅ Test data setup complete. Token: " . substr($this->adminToken, 0, 20) . "...\n\n";
//     }

//     /**
//      * Make HTTP request with proper headers
//      */
//     private function makeRequest($method, $endpoint, $data = null)
//     {
//         $headers = [
//             'Accept' => 'application/json',
//             'Authorization' => 'Bearer ' . $this->adminToken,
//         ];

//         if ($data) {
//             $headers['Content-Type'] = 'application/json';
//         }

//         $url = $this->baseUrl . $endpoint;

//         try {
//             switch (strtoupper($method)) {
//                 case 'GET':
//                     $response = Http::withHeaders($headers)->get($url);
//                     break;
//                 case 'POST':
//                     $response = Http::withHeaders($headers)->post($url, $data);
//                     break;
//                 case 'PUT':
//                     $response = Http::withHeaders($headers)->put($url, $data);
//                     break;
//                 case 'DELETE':
//                     $response = Http::withHeaders($headers)->delete($url);
//                     break;
//                 default:
//                     throw new Exception("Unsupported method: $method");
//             }

//             return [
//                 'status' => $response->status(),
//                 'data' => $response->json(),
//                 'success' => $response->successful(),
//                 'headers' => $response->headers(),
//             ];
//         } catch (Exception $e) {
//             return [
//                 'status' => 0,
//                 'data' => ['error' => $e->getMessage()],
//                 'success' => false,
//                 'headers' => [],
//             ];
//         }
//     }

//     /**
//      * Log test result
//      */
//     private function logResult($testName, $success, $details = '')
//     {
//         $this->testResults[] = [
//             'test' => $testName,
//             'success' => $success,
//             'details' => $details,
//             'timestamp' => now(),
//         ];

//         $status = $success ? '✅' : '❌';
//         echo "$status $testName";
//         if ($details) {
//             echo " - $details";
//         }
//         echo "\n";
//     }

//     /**
//      * Test Dashboard KPIs
//      */
//     public function testDashboardKpis()
//     {
//         echo "\n📊 Testing Dashboard KPIs...\n";
        
//         // Test basic KPIs
//         $response = $this->makeRequest('GET', '/admin/dashboard/kpis');
//         $success = $response['success'] && isset($response['data']['data']);
//         $this->logResult('Dashboard KPIs - Basic', $success, 
//             $success ? "Status: {$response['status']}" : "Error: " . json_encode($response['data']));

//         // Test with branch filter
//         if (Branch::count() > 0) {
//             $branchId = Branch::first()->branch_id;
//             $response = $this->makeRequest('GET', "/admin/dashboard/kpis?branchId=$branchId");
//             $success = $response['success'] && isset($response['data']['data']);
//             $this->logResult('Dashboard KPIs - With Branch Filter', $success);
//         }
//     }

//     /**
//      * Test Dashboard Charts
//      */
//     public function testDashboardCharts()
//     {
//         echo "\n📈 Testing Dashboard Charts...\n";
        
//         $response = $this->makeRequest('GET', '/admin/dashboard/charts');
//         $success = $response['success'] && 
//                   isset($response['data']['data']['deviceConditions']) &&
//                   isset($response['data']['data']['devicesPerBranch']);
//         $this->logResult('Dashboard Charts', $success);
//     }

//     /**
//      * Test Device Management
//      */
//     public function testDeviceManagement()
//     {
//         echo "\n🖥️ Testing Device Management...\n";
        
//         // Test list devices
//         $response = $this->makeRequest('GET', '/admin/devices');
//         $success = $response['success'] && isset($response['data']['data']);
//         $this->logResult('List Devices', $success);

//         // Test with search
//         $response = $this->makeRequest('GET', '/admin/devices?search=Dell&page=1&perPage=5');
//         $this->logResult('Search Devices', $response['success']);

//         // Test with condition filter
//         $response = $this->makeRequest('GET', '/admin/devices?condition=Baik');
//         $this->logResult('Filter by Condition', $response['success']);

//         // Test device details
//         if (Device::count() > 0) {
//             $deviceId = Device::first()->device_id;
//             $response = $this->makeRequest('GET', "/admin/devices/$deviceId");
//             $success = $response['success'] && isset($response['data']['data']['deviceId']);
//             $this->logResult('Device Details', $success);
//         }

//         // Test create device
//         $this->testCreateDevice();

//         // Test update device
//         if (!empty($this->createdResources['device'])) {
//             $this->testUpdateDevice();
//         }

//         // Test delete device (should fail if assigned)
//         if (!empty($this->createdResources['device'])) {
//             $this->testDeleteDevice();
//         }
//     }

//     /**
//      * Test Create Device
//      */
//     private function testCreateDevice()
//     {
//         $briboxId = Bribox::first()->bribox_id ?? '01';
//         $timestamp = time();
        
//         $deviceData = [
//             'brand' => 'Test Brand',
//             'brand_name' => 'Test Model ' . $timestamp,
//             'serial_number' => 'TEST-' . $timestamp,
//             'asset_code' => 'TEST/DEV/' . $timestamp,
//             'bribox_id' => $briboxId,
//             'condition' => 'Baik',
//             'spec1' => 'Test CPU',
//             'spec2' => '8GB RAM',
//             'spec3' => '256GB SSD',
//             'dev_date' => '2024-07-21',
//         ];

//         $response = $this->makeRequest('POST', '/admin/devices', $deviceData);
//         $success = $response['success'] && $response['status'] === 201;
        
//         if ($success && isset($response['data']['data']['deviceId'])) {
//             $this->createdResources['device'] = $response['data']['data']['deviceId'];
//         }
        
//         $this->logResult('Create Device', $success);

//         // Test validation - duplicate serial number
//         $response = $this->makeRequest('POST', '/admin/devices', $deviceData);
//         $validationWorks = !$response['success'] && $response['status'] === 422;
//         $this->logResult('Create Device - Validation (Duplicate)', $validationWorks);
//     }

//     /**
//      * Test Update Device
//      */
//     private function testUpdateDevice()
//     {
//         $deviceId = $this->createdResources['device'];
        
//         $updateData = [
//             'condition' => 'Perlu Pengecekan',
//             'spec4' => 'Updated Spec',
//         ];

//         $response = $this->makeRequest('PUT', "/admin/devices/$deviceId", $updateData);
//         $success = $response['success'] && $response['status'] === 200;
//         $this->logResult('Update Device', $success);
//     }

//     /**
//      * Test Delete Device
//      */
//     private function testDeleteDevice()
//     {
//         $deviceId = $this->createdResources['device'];
        
//         $response = $this->makeRequest('DELETE', "/admin/devices/$deviceId");
//         $success = $response['success'] && $response['status'] === 200;
//         $this->logResult('Delete Device', $success);
//     }

//     /**
//      * Test Device Assignment Management
//      */
//     public function testDeviceAssignmentManagement()
//     {
//         echo "\n📋 Testing Device Assignment Management...\n";
        
//         // Test list assignments
//         $response = $this->makeRequest('GET', '/admin/device-assignments');
//         $success = $response['success'] && isset($response['data']['data']);
//         $this->logResult('List Device Assignments', $success);

//         // Test with filters
//         $response = $this->makeRequest('GET', '/admin/device-assignments?activeOnly=true&page=1&perPage=5');
//         $this->logResult('Filter Active Assignments', $response['success']);

//         // Test create assignment
//         $this->testCreateAssignment();

//         // Test update assignment
//         if (!empty($this->createdResources['assignment'])) {
//             $this->testUpdateAssignment();
//         }

//         // Test return device
//         if (!empty($this->createdResources['assignment'])) {
//             $this->testReturnDevice();
//         }
//     }

//     /**
//      * Test Create Assignment
//      */
//     private function testCreateAssignment()
//     {
//         // Get available device and user
//         $device = Device::whereDoesntHave('currentAssignment')->first();
//         $user = User::first();

//         if (!$device || !$user) {
//             $this->logResult('Create Assignment', false, 'No available device or user');
//             return;
//         }

//         $assignmentData = [
//             'device_id' => $device->device_id,
//             'user_id' => $user->user_id,
//             'assigned_date' => '2024-07-21',
//             'status' => 'Digunakan',
//             'notes' => 'Test assignment',
//         ];

//         $response = $this->makeRequest('POST', '/admin/device-assignments', $assignmentData);
//         $success = $response['success'] && $response['status'] === 201;
        
//         if ($success && isset($response['data']['data']['assignmentId'])) {
//             $this->createdResources['assignment'] = $response['data']['data']['assignmentId'];
//         }
        
//         $this->logResult('Create Assignment', $success);

//         // Test validation - assign already assigned device
//         $response = $this->makeRequest('POST', '/admin/device-assignments', $assignmentData);
//         $validationWorks = !$response['success'] && 
//                           isset($response['data']['errorCode']) && 
//                           $response['data']['errorCode'] === 'ERR_DEVICE_ALREADY_ASSIGNED';
//         $this->logResult('Create Assignment - Validation (Already Assigned)', $validationWorks);
//     }

//     /**
//      * Test Update Assignment
//      */
//     private function testUpdateAssignment()
//     {
//         $assignmentId = $this->createdResources['assignment'];
        
//         $updateData = [
//             'status' => 'Tidak Digunakan',
//             'notes' => 'Updated assignment notes',
//         ];

//         $response = $this->makeRequest('PUT', "/admin/device-assignments/$assignmentId", $updateData);
//         $success = $response['success'] && $response['status'] === 200;
//         $this->logResult('Update Assignment', $success);
//     }

//     /**
//      * Test Return Device
//      */
//     private function testReturnDevice()
//     {
//         $assignmentId = $this->createdResources['assignment'];
        
//         $returnData = [
//             'returned_date' => '2024-07-21',
//             'return_notes' => 'Test return',
//         ];

//         $response = $this->makeRequest('POST', "/admin/device-assignments/$assignmentId/return", $returnData);
//         $success = $response['success'] && $response['status'] === 200;
//         $this->logResult('Return Device', $success);

//         // Test validation - return already returned device
//         $response = $this->makeRequest('POST', "/admin/device-assignments/$assignmentId/return", $returnData);
//         $validationWorks = !$response['success'] && 
//                           isset($response['data']['errorCode']) && 
//                           $response['data']['errorCode'] === 'ERR_DEVICE_ALREADY_RETURNED';
//         $this->logResult('Return Device - Validation (Already Returned)', $validationWorks);
//     }

//     /**
//      * Test User Management
//      */
//     public function testUserManagement()
//     {
//         echo "\n👥 Testing User Management...\n";
        
//         // Test list users
//         $response = $this->makeRequest('GET', '/admin/users');
//         $success = $response['success'] && isset($response['data']['data']);
//         $this->logResult('List Users', $success);

//         // Test with search
//         $response = $this->makeRequest('GET', '/admin/users?search=admin&page=1&perPage=5');
//         $this->logResult('Search Users', $response['success']);

//         // Test with filters
//         if (User::whereNotNull('department_id')->count() > 0) {
//             $departmentId = User::whereNotNull('department_id')->first()->department_id;
//             $response = $this->makeRequest('GET', "/admin/users?departmentId=$departmentId");
//             $this->logResult('Filter Users by Department', $response['success']);
//         }
//     }

//     /**
//      * Test Master Data
//      */
//     public function testMasterData()
//     {
//         echo "\n📚 Testing Master Data...\n";
        
//         // Test branches
//         $response = $this->makeRequest('GET', '/admin/branches');
//         $success = $response['success'] && isset($response['data']['data']);
//         $this->logResult('List Branches', $success);

//         // Test categories
//         $response = $this->makeRequest('GET', '/admin/categories');
//         $success = $response['success'] && isset($response['data']['data']);
//         $this->logResult('List Categories', $success);
//     }

//     /**
//      * Test Authentication and Authorization
//      */
//     public function testAuthAndPermissions()
//     {
//         echo "\n🔐 Testing Authentication & Permissions...\n";
        
//         // Test without token
//         $response = Http::withHeaders(['Accept' => 'application/json'])
//                        ->get($this->baseUrl . '/admin/devices');
//         $authRequired = $response->status() === 401;
//         $this->logResult('Authentication Required', $authRequired);

//         // Test with user token (should be forbidden)
//         $userAuth = Auth::where('pn', 'USER01')->first();
//         if ($userAuth) {
//             $user = User::where('pn', 'USER01')->first();
//             if ($user) {
//                 $userToken = $user->createToken('user-test-token')->plainTextToken;
//                 $response = Http::withHeaders([
//                     'Accept' => 'application/json',
//                     'Authorization' => 'Bearer ' . $userToken,
//                 ])->get($this->baseUrl . '/admin/devices');
                
//                 $accessDenied = $response->status() === 403;
//                 $this->logResult('User Access Denied', $accessDenied);
                
//                 // Clean up user token
//                 $user->tokens()->where('name', 'user-test-token')->delete();
//             }
//         }
//     }

//     /**
//      * Test Error Handling
//      */
//     public function testErrorHandling()
//     {
//         echo "\n🚫 Testing Error Handling...\n";
        
//         // Test 404 - non-existent device
//         $response = $this->makeRequest('GET', '/admin/devices/99999');
//         $notFound = $response['status'] === 404;
//         $this->logResult('404 - Device Not Found', $notFound);

//         // Test 422 - invalid data
//         $invalidData = [
//             'brand' => '', // Required field
//             'serial_number' => 'test',
//         ];
//         $response = $this->makeRequest('POST', '/admin/devices', $invalidData);
//         $validationError = $response['status'] === 422 && 
//                           isset($response['data']['errors']);
//         $this->logResult('422 - Validation Error', $validationError);
//     }

//     /**
//      * Run all tests
//      */
//     public function runAllTests()
//     {
//         echo "🚀 Starting Admin API Tests...\n";
//         echo "Base URL: {$this->baseUrl}\n";
//         echo "========================================\n";

//         $this->testAuthAndPermissions();
//         $this->testDashboardKpis();
//         $this->testDashboardCharts();
//         $this->testDeviceManagement();
//         $this->testDeviceAssignmentManagement();
//         $this->testUserManagement();
//         $this->testMasterData();
//         $this->testErrorHandling();

//         $this->printSummary();
//         $this->cleanup();
//     }

//     /**
//      * Print test summary
//      */
//     public function printSummary()
//     {
//         echo "\n========================================\n";
//         echo "📊 TEST SUMMARY\n";
//         echo "========================================\n";

//         $total = count($this->testResults);
//         $passed = count(array_filter($this->testResults, fn($r) => $r['success']));
//         $failed = $total - $passed;

//         echo "Total Tests: $total\n";
//         echo "✅ Passed: $passed\n";
//         echo "❌ Failed: $failed\n";
//         echo "Success Rate: " . round(($passed / $total) * 100, 2) . "%\n\n";

//         if ($failed > 0) {
//             echo "Failed Tests:\n";
//             foreach ($this->testResults as $result) {
//                 if (!$result['success']) {
//                     echo "  ❌ {$result['test']} - {$result['details']}\n";
//                 }
//             }
//         }

//         echo "\n🎉 Testing completed!\n";
//     }

//     /**
//      * Cleanup test data
//      */
//     public function cleanup()
//     {
//         echo "\n🧹 Cleaning up test data...\n";
        
//         // Delete created resources
//         if (!empty($this->createdResources['device'])) {
//             try {
//                 Device::where('device_id', $this->createdResources['device'])->delete();
//                 echo "✅ Cleaned up test device\n";
//             } catch (Exception $e) {
//                 echo "⚠️ Could not clean up test device: " . $e->getMessage() . "\n";
//             }
//         }

//         // Delete test tokens
//         PersonalAccessToken::where('name', 'like', '%test-token%')->delete();
//         echo "✅ Cleaned up test tokens\n";
//     }

//     /**
//      * Get test results
//      */
//     public function getResults()
//     {
//         return $this->testResults;
//     }
// }
