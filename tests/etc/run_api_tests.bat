@echo off
REM Admin API Testing Runner Script for Windows
REM This script runs both PHP (Artisan Tinker) and Python tests

echo 🚀 Admin API Testing Suite
echo ==========================

REM Check if Laravel server is running
curl -s http://localhost:8000/api/v1/status >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Laravel server is not running on localhost:8000
    echo Please start the server with: php artisan serve --host=0.0.0.0 --port=8000
    pause
    exit /b 1
)

echo ✅ Laravel server is running

REM Create results directory
if not exist "tests\results" mkdir "tests\results"

REM Run PHP tests using Artisan Tinker
echo.
echo 📱 Running PHP Tests (Artisan Tinker)...
echo ========================================

php artisan tinker --execute="require_once 'tests/api/AdminApiTester.php'; $tester = new AdminApiTester(); $tester->runAllTests(); file_put_contents('tests/results/php_test_results_' . date('Ymd_His') . '.json', json_encode($tester->getResults(), JSON_PRETTY_PRINT)); echo \"📄 PHP test results saved to tests/results/\";"

REM Run Python tests
echo.
echo 🐍 Running Python Tests...
echo ==========================

python --version >nul 2>&1
if %errorlevel% equ 0 (
    cd tests\api
    python admin_api_tester.py
    move admin_api_test_results_*.json ..\results\ >nul 2>&1
    cd ..\..
) else (
    echo ⚠️ Python not found. Skipping Python tests.
)

echo.
echo ✅ All tests completed!
echo 📄 Results saved in tests\results\
pause
