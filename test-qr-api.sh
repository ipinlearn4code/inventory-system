#!/bin/bash

# QR Scanner API Test Script
# Usage: ./test-qr-api.sh [QR_CODE] [AUTH_TOKEN]

BASE_URL="http://localhost:8000/api/v1"
QR_CODE=${1:-"briven-ABC12345"}
AUTH_TOKEN=${2:-"your-auth-token-here"}

echo "=== Testing QR Scanner API ==="
echo "QR Code: $QR_CODE"
echo "Base URL: $BASE_URL"
echo "=========================="

# Test the endpoint
curl -X GET \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -w "\nHTTP Status: %{http_code}\n" \
  "$BASE_URL/user/devices/scan/$QR_CODE" | jq '.'

echo ""
echo "=== Test completed ==="

# Examples of different test cases:
echo ""
echo "To test different scenarios:"
echo "./test-qr-api.sh briven-ABC12345 your-token    # Valid format"
echo "./test-qr-api.sh briven-INVALID your-token     # Invalid asset code"
echo "./test-qr-api.sh invalid-format your-token     # Invalid QR format"
