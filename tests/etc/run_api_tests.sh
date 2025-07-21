#!/bin/bash

# Admin API Testing Runner Script
# This script runs both PHP (Artisan Tinker) and Python tests

echo "🚀 Admin API Testing Suite"
echo "=========================="

# Check if Laravel server is running
if ! curl -s http://localhost:8000/api/v1/status > /dev/null; then
    echo "❌ Laravel server is not running on localhost:8000"
    echo "Please start the server with: php artisan serve --host=0.0.0.0 --port=8000"
    exit 1
fi

echo "✅ Laravel server is running"

# Create results directory
mkdir -p tests/results

# Run PHP tests using Artisan Tinker
echo ""
echo "📱 Running PHP Tests (Artisan Tinker)..."
echo "========================================"

php artisan tinker --execute="
require_once 'tests/api/AdminApiTester.php';
\$tester = new AdminApiTester();
\$tester->runAllTests();
file_put_contents('tests/results/php_test_results_' . date('Ymd_His') . '.json', json_encode(\$tester->getResults(), JSON_PRETTY_PRINT));
echo \"📄 PHP test results saved to tests/results/\";
"

# Run Python tests
echo ""
echo "🐍 Running Python Tests..."
echo "=========================="

if command -v python3 &> /dev/null; then
    cd tests/api
    python3 admin_api_tester.py
    mv admin_api_test_results_*.json ../results/ 2>/dev/null || true
    cd ../..
else
    echo "⚠️ Python 3 not found. Skipping Python tests."
fi

echo ""
echo "✅ All tests completed!"
echo "📄 Results saved in tests/results/"
