# Admin API Testing Documentation

## Overview

This document provides comprehensive testing guidelines and scripts for the newly implemented Admin API endpoints. The testing suite includes both PHP (using Laravel Artisan Tinker) and Python scripts to validate all API functionality under various conditions.

## 🎯 Testing Objectives

### Primary Goals
- ✅ Verify all 14 admin endpoints function correctly
- ✅ Test authentication and authorization mechanisms
- ✅ Validate request/response formats and data integrity
- ✅ Ensure proper error handling and validation
- ✅ Test edge cases and boundary conditions
- ✅ Verify business logic implementation

### Test Categories
1. **Authentication & Authorization**
2. **Dashboard Analytics (KPIs & Charts)**
3. **Device Management (Full CRUD)**
4. **Device Assignment Management**
5. **User Management**
6. **Master Data Access**
7. **Error Handling & Validation**
8. **Pagination & Filtering**

## 🛠️ Testing Tools

### 1. PHP Testing Script (`AdminApiTester.php`)
- **Location**: `tests/api/AdminApiTester.php`
- **Framework**: Laravel Artisan Tinker
- **Features**: 
  - Direct model access for setup/teardown
  - Laravel HTTP client for API calls
  - Comprehensive test coverage
  - Automatic cleanup of test data

### 2. Python Testing Script (`admin_api_tester.py`)
- **Location**: `tests/api/admin_api_tester.py`
- **Framework**: Python requests library
- **Features**:
  - Cross-platform compatibility
  - JSON result export
  - Detailed error reporting
  - Independent from Laravel environment

### 3. Automated Test Runners
- **Linux/Mac**: `run_api_tests.sh`
- **Windows**: `run_api_tests.bat`
- **Features**: 
  - Automatic server status check
  - Sequential test execution
  - Results collection and storage

## 🚀 Quick Start Guide

### Prerequisites
1. **Laravel Server Running**:
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```

2. **Admin User Setup**:
   - Ensure admin user exists with PN: `ADMIN01`
   - Default password: `password123`
   - Must have `admin` or `superadmin` role

3. **Dependencies**:
   - PHP 8.1+ with Laravel
   - Python 3.7+ (optional, for Python tests)
   - curl (for server status checks)

### Running Tests

#### Option 1: Automated Runner (Recommended)
```bash
# Linux/Mac
chmod +x run_api_tests.sh
./run_api_tests.sh

# Windows
run_api_tests.bat
```

#### Option 2: Manual PHP Testing
```bash
php artisan tinker
```
```php
require_once 'tests/api/AdminApiTester.php';
$tester = new AdminApiTester();
$tester->runAllTests();
```

#### Option 3: Manual Python Testing
```bash
cd tests/api
python3 admin_api_tester.py
```

## 📊 Test Coverage

### 1. Authentication & Authorization Tests
| Test Case | Description | Expected Result |
|-----------|-------------|-----------------|
| Authentication Required | Access without token | 401 Unauthorized |
| Invalid Token | Access with invalid token | 401 Unauthorized |
| User Role Access | User trying admin endpoints | 403 Forbidden |
| Admin Token Valid | Admin accessing endpoints | 200 Success |

### 2. Dashboard Analytics Tests
| Endpoint | Test Scenarios | Validations |
|----------|----------------|-------------|
| `GET /admin/dashboard/kpis` | Basic KPIs, Branch filtering | Data structure, counts |
| `GET /admin/dashboard/charts` | Device conditions, Branch distribution | Chart data format |

### 3. Device Management Tests
| Operation | Test Scenarios | Validations |
|-----------|----------------|-------------|
| **List Devices** | Pagination, Search, Filtering | Response format, pagination meta |
| **Get Device Details** | Valid ID, Invalid ID | Complete device info, 404 handling |
| **Create Device** | Valid data, Duplicate serial, Missing fields | 201 success, validation errors |
| **Update Device** | Partial update, Invalid ID | 200 success, data persistence |
| **Delete Device** | Available device, Assigned device | 200 success, business logic validation |

### 4. Device Assignment Tests
| Operation | Test Scenarios | Validations |
|-----------|----------------|-------------|
| **List Assignments** | All, Active only, By status, By branch | Filtering accuracy |
| **Create Assignment** | Valid assignment, Already assigned device | Business logic validation |
| **Update Assignment** | Status change, Notes update | Data persistence |
| **Return Device** | Valid return, Already returned | State management |

### 5. User Management Tests
| Test Case | Description | Validations |
|-----------|-------------|-------------|
| List Users | Pagination, Search, Department filter | User data format |
| Active Device Count | Users with/without devices | Accurate device counting |

### 6. Master Data Tests
| Endpoint | Test Scenarios | Validations |
|----------|----------------|-------------|
| `GET /admin/branches` | All branches with hierarchy | Complete branch data |
| `GET /admin/categories` | All device categories | Category structure |

### 7. Error Handling Tests
| Error Type | Test Scenario | Expected Response |
|------------|---------------|-------------------|
| 404 Not Found | Non-existent resource ID | Proper error message |
| 422 Validation | Invalid request data | Field-specific errors |
| 400 Business Logic | Delete assigned device | Business rule error |

### 8. Pagination & Filtering Tests
| Feature | Test Scenarios | Validations |
|---------|----------------|-------------|
| Pagination | page, perPage parameters | Meta data accuracy |
| Search | Multi-field text search | Result relevance |
| Filtering | Multiple filter combinations | Filter accuracy |

## 📝 Test Results Format

### PHP Test Results
```json
{
  "test": "Test Name",
  "success": true|false,
  "details": "Additional information",
  "timestamp": "2024-07-21T10:00:00Z"
}
```

### Python Test Results
```json
{
  "summary": {
    "total_tests": 45,
    "passed": 43,
    "failed": 2,
    "timestamp": "2024-07-21T10:00:00Z",
    "base_url": "http://localhost:8000/api/v1"
  },
  "results": [...],
  "created_resources": {...}
}
```

## 🎯 Expected Test Results

### Success Criteria
- **95%+ Pass Rate**: At least 95% of tests should pass
- **No Authentication Bypasses**: All security tests must pass
- **Data Integrity**: All CRUD operations must preserve data integrity
- **Error Handling**: All error scenarios must return appropriate responses

### Common Test Scenarios

#### Successful Test Flow
1. ✅ Authentication successful
2. ✅ All endpoint basic functionality works
3. ✅ CRUD operations complete successfully
4. ✅ Validation prevents invalid operations
5. ✅ Error handling works correctly
6. ✅ Cleanup removes test data

#### Potential Issues
- **Token Expiration**: Long test runs may encounter token expiry
- **Data Dependencies**: Tests may fail if required master data is missing
- **Concurrent Access**: Multiple test runs may interfere with each other

## 🔧 Test Configuration

### Environment Variables
```bash
# Optional: Custom base URL
export API_BASE_URL="http://localhost:8000/api/v1"

# Optional: Custom admin credentials
export ADMIN_PN="ADMIN01"
export ADMIN_PASSWORD="password123"
```

### Database Considerations
- Tests create and clean up temporary data
- Use separate test database if needed
- Ensure admin user exists with proper permissions

## 📊 Performance Benchmarks

### Expected Response Times
| Endpoint Type | Expected Time | Acceptable Range |
|---------------|---------------|------------------|
| Simple GET | < 100ms | < 500ms |
| List with Pagination | < 200ms | < 1000ms |
| Create Operations | < 300ms | < 1500ms |
| Update Operations | < 200ms | < 1000ms |
| Delete Operations | < 200ms | < 1000ms |

## 🐛 Troubleshooting

### Common Issues

#### 1. Server Not Running
```
❌ Laravel server is not running on localhost:8000
```
**Solution**: Start Laravel server with `php artisan serve --host=0.0.0.0 --port=8000`

#### 2. Authentication Failed
```
❌ Authentication failed: 401 - {"message":"Unauthenticated."}
```
**Solutions**:
- Verify admin user exists with PN: `ADMIN01`
- Check password is correct
- Ensure user has admin/superadmin role

#### 3. Permission Denied
```
❌ 403 Forbidden
```
**Solutions**:
- Verify user has correct role assignment
- Check middleware configuration
- Ensure Sanctum tokens are working

#### 4. Database Errors
```
❌ SQLSTATE[HY000]: General error
```
**Solutions**:
- Check database connection
- Run migrations: `php artisan migrate`
- Seed required data: `php artisan db:seed`

#### 5. Test Data Conflicts
```
❌ Validation Error: serial_number has already been taken
```
**Solutions**:
- Run cleanup manually
- Clear test tokens
- Reset test data

### Manual Cleanup Commands
```php
// In Artisan Tinker
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\Device;

// Clean up test tokens
PersonalAccessToken::where('name', 'like', '%test-token%')->delete();

// Clean up test devices
Device::where('serial_number', 'like', 'TEST-%')->delete();
Device::where('serial_number', 'like', 'PY-TEST-%')->delete();
```

## 📈 Continuous Integration

### CI/CD Integration
The test scripts can be integrated into CI/CD pipelines:

```yaml
# Example GitHub Actions workflow
test_admin_api:
  runs-on: ubuntu-latest
  steps:
    - uses: actions/checkout@v2
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
    - name: Install dependencies
      run: composer install
    - name: Run Laravel server
      run: php artisan serve --host=0.0.0.0 --port=8000 &
    - name: Wait for server
      run: sleep 10
    - name: Run API tests
      run: ./run_api_tests.sh
```

## 📚 Additional Resources

### API Documentation
- Complete API documentation: `InventoryAppAPIDocsFixed.markdown`
- Implementation details: `ADMIN_API_IMPLEMENTATION_COMPLETE.md`

### Laravel Resources
- Laravel HTTP Testing: https://laravel.com/docs/http-tests
- Laravel Sanctum: https://laravel.com/docs/sanctum
- Artisan Tinker: https://laravel.com/docs/artisan#tinker

### Testing Best Practices
- Always test positive and negative scenarios
- Include edge cases and boundary conditions
- Verify data persistence and consistency
- Test error messages and status codes
- Ensure proper cleanup of test data

---

## 🎉 Conclusion

This comprehensive testing suite ensures the Admin API implementation is robust, secure, and ready for production use. The combination of PHP and Python testing approaches provides thorough coverage and flexibility for different development environments.

For any issues or questions, refer to the troubleshooting section or check the implementation documentation.
