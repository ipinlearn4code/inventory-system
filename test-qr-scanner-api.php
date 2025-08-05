<?php

/**
 * QR Scanner API Endpoint Test
 * 
 * This file demonstrates how to test the new QR scanning endpoint.
 * Run this with: php test-qr-scanner-api.php
 */

require_once 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Test Configuration
$baseUrl = 'http://localhost:8000/api/v1';
$testToken = 'your-auth-token'; // Replace with valid token
$testQrCodes = [
    'briven-ABC12345',  // Valid QR code format
    'briven-INVALID',   // Invalid asset code
    'invalid-format',   // Invalid QR format
    'briven-',          // Empty asset code
];

$client = new Client([
    'base_uri' => $baseUrl,
    'timeout' => 30,
    'headers' => [
        'Accept' => 'application/json',
        'Authorization' => "Bearer {$testToken}",
    ]
]);

echo "=== QR Scanner API Endpoint Test ===\n\n";

foreach ($testQrCodes as $qrCode) {
    echo "Testing QR Code: {$qrCode}\n";
    echo str_repeat('-', 50) . "\n";
    
    try {
        $response = $client->get("/user/devices/scan/{$qrCode}");
        $data = json_decode($response->getBody(), true);
        
        echo "Status: " . $response->getStatusCode() . "\n";
        echo "Response:\n";
        echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
        
    } catch (RequestException $e) {
        $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
        $errorBody = $e->hasResponse() ? $e->getResponse()->getBody() : $e->getMessage();
        
        echo "Error Status: {$statusCode}\n";
        echo "Error Response:\n";
        echo $errorBody . "\n";
    }
    
    echo "\n" . str_repeat('=', 70) . "\n\n";
}

echo "Test completed!\n";
echo "\nExpected Results:\n";
echo "- briven-ABC12345: Should return device data if exists, or 404 if not found\n";
echo "- briven-INVALID: Should return 404 (device not found)\n";
echo "- invalid-format: Should return 400 (invalid QR format)\n";
echo "- briven-: Should return 400 (invalid QR format)\n";
