<?php

// Test the updated assignment endpoints
echo "Testing Device Assignment API Endpoints\n";
echo "=====================================\n\n";

// Test 1: Basic ping test
echo "1. Testing ping endpoint\n";
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Accept: application/json',
        'timeout' => 5
    ]
]);

$response = @file_get_contents('http://localhost:8000/api/v1/ping', false, $context);

if ($response !== false) {
    echo "✅ Ping successful\n";
    echo "Response: " . $response . "\n";
} else {
    echo "❌ Ping failed\n";
    $error = error_get_last();
    if ($error) {
        echo "Error: " . $error['message'] . "\n";
    }
}

echo "\n";

// Test 2: Simple assignment count
echo "2. Testing assignment count\n";
$response = @file_get_contents('http://localhost:8000/api/v1/test-assignments-count', false, $context);

if ($response !== false) {
    echo "✅ Assignment count successful\n";
    echo "Response: " . $response . "\n";
} else {
    echo "❌ Assignment count failed\n";
}

echo "\n";

// Test 2: Get specific assignment
echo "2. Testing GET /api/v1/test-assignments/1\n";
$response = @file_get_contents('http://localhost:8000/api/v1/test-assignments/1', false, 
    stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Accept: application/json'
        ]
    ])
);

if ($response !== false) {
    $data = json_decode($response, true);
    echo "✅ GET specific assignment successful\n";
    if (isset($data['data']['assignmentId'])) {
        echo "Assignment ID: " . $data['data']['assignmentId'] . "\n";
        echo "Assigned to: " . $data['data']['assignedTo'] . "\n";
        echo "Asset code: " . $data['data']['assetCode'] . "\n";
    }
} else {
    echo "❌ GET specific assignment failed\n";
}

echo "\n";

// Test 3: Test PATCH validation (should fail with validation errors)
echo "3. Testing PATCH /api/v1/test-assignments/1 (validation test)\n";

$postData = http_build_query([
    'notes' => str_repeat('x', 600), // Too long, should fail validation
    'assigned_date' => '2030-01-01', // Future date, should fail validation
]);

$context = stream_context_create([
    'http' => [
        'method' => 'PATCH',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\nAccept: application/json",
        'content' => $postData
    ]
]);

$response = @file_get_contents('http://localhost:8000/api/v1/test-assignments/1', false, $context);

if ($response !== false) {
    $data = json_decode($response, true);
    if (isset($data['errors'])) {
        echo "✅ Validation working correctly\n";
        echo "Validation errors detected:\n";
        foreach ($data['errors'] as $field => $errors) {
            echo "  - $field: " . implode(', ', $errors) . "\n";
        }
    } else {
        echo "⚠️  No validation errors (unexpected)\n";
    }
} else {
    echo "❌ PATCH validation test failed\n";
}

echo "\n";

// Test 4: Test valid PATCH update
echo "4. Testing PATCH /api/v1/test-assignments/1 (valid update)\n";

$postData = http_build_query([
    'notes' => 'Updated via API test at ' . date('Y-m-d H:i:s'),
]);

$context = stream_context_create([
    'http' => [
        'method' => 'PATCH',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\nAccept: application/json",
        'content' => $postData
    ]
]);

$response = @file_get_contents('http://localhost:8000/api/v1/test-assignments/1', false, $context);

if ($response !== false) {
    $data = json_decode($response, true);
    if (isset($data['data']['assignmentId'])) {
        echo "✅ PATCH update successful\n";
        echo "Assignment ID: " . $data['data']['assignmentId'] . "\n";
        echo "Updated notes: " . $data['data']['notes'] . "\n";
    } else {
        echo "⚠️  Response structure unexpected:\n";
        echo $response . "\n";
    }
} else {
    echo "❌ PATCH update failed\n";
}

echo "\n=== Test Complete ===\n";
