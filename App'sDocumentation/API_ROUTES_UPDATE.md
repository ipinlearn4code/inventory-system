# API Routes Update Guide

## Overview
This document describes the changes made to the API routes to use the new refactored v1 controllers following SOLID principles.

## What Changed

### Before (Original Routes)
Routes pointed to the monolithic `AdminController` and original `UserController`:

```php
// Old routes
Route::get('/admin/dashboard/kpis', [AdminController::class, 'dashboardKpis']);
Route::get('/admin/devices', [AdminController::class, 'devices']);
Route::post('/admin/devices', [AdminController::class, 'createDevice']);
// ... etc
```

### After (Refactored Routes)
Routes now point to specific, focused v1 controllers:

```php
// New routes
Route::get('/admin/dashboard/kpis', [V1DashboardController::class, 'kpis']);
Route::get('/admin/devices', [V1DeviceController::class, 'index']);
Route::post('/admin/devices', [V1DeviceController::class, 'store']);
// ... etc
```

## Updated Route Mappings

### Dashboard Routes
| Endpoint | Old Controller | New Controller | Method |
|----------|---------------|---------------|---------|
| `GET /admin/dashboard/kpis` | `AdminController::dashboardKpis` | `V1DashboardController::kpis` |
| `GET /admin/dashboard/charts` | `AdminController::dashboardCharts` | `V1DashboardController::charts` |

### Device Routes
| Endpoint | Old Controller | New Controller | Method |
|----------|---------------|---------------|---------|
| `GET /admin/devices` | `AdminController::devices` | `V1DeviceController::index` |
| `GET /admin/devices/{id}` | `AdminController::deviceDetails` | `V1DeviceController::show` |
| `POST /admin/devices` | `AdminController::createDevice` | `V1DeviceController::store` |
| `PUT /admin/devices/{id}` | `AdminController::updateDevice` | `V1DeviceController::update` |
| `DELETE /admin/devices/{id}` | `AdminController::deleteDevice` | `V1DeviceController::destroy` |

### Device Assignment Routes
| Endpoint | Old Controller | New Controller | Method |
|----------|---------------|---------------|---------|
| `GET /admin/device-assignments` | `AdminController::deviceAssignments` | `V1DeviceAssignmentController::index` |
| `GET /admin/device-assignments/{id}` | `AdminController::deviceAssignmentDetails` | `V1DeviceAssignmentController::show` |
| `POST /admin/device-assignments` | `AdminController::createDeviceAssignment` | `V1DeviceAssignmentController::store` |
| `PUT /admin/device-assignments/{id}` | `AdminController::updateDeviceAssignment` | `V1DeviceAssignmentController::update` |
| `POST /admin/device-assignments/{id}/return` | `AdminController::returnDevice` | `V1DeviceAssignmentController::returnDevice` |

### User Routes
| Endpoint | Old Controller | New Controller | Method |
|----------|---------------|---------------|---------|
| `GET /user/home/summary` | `UserController::homeSummary` | `V1UserController::homeSummary` |
| `GET /user/devices` | `UserController::devices` | `V1UserController::devices` |
| `GET /user/devices/{id}` | `UserController::deviceDetails` | `V1UserController::deviceDetails` |
| `POST /user/devices/{id}/report-issue` | `UserController::reportIssue` | `V1UserController::reportIssue` |
| `GET /user/profile` | `UserController::profile` | `V1UserController::profile` |
| `GET /user/history` | `UserController::history` | `V1UserController::history` |

### Metadata Routes
| Endpoint | Old Controller | New Controller | Method |
|----------|---------------|---------------|---------|
| `GET /admin/users` | `AdminController::users` | `V1MetadataController::users` |
| `GET /admin/branches` | `AdminController::branches` | `V1MetadataController::branches` |
| `GET /admin/categories` | `AdminController::categories` | `V1MetadataController::categories` |

## Service Provider Registration

Added the new `RepositoryServiceProvider` to `bootstrap/providers.php`:

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\RepositoryServiceProvider::class,  // <- NEW
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\TelescopeServiceProvider::class,
];
```

## Route File Structure

### Imports Added
```php
use App\Http\Controllers\Api\v1\DeviceController as V1DeviceController;
use App\Http\Controllers\Api\v1\DeviceAssignmentController as V1DeviceAssignmentController;
use App\Http\Controllers\Api\v1\DashboardController as V1DashboardController;
use App\Http\Controllers\Api\v1\UserController as V1UserController;
use App\Http\Controllers\Api\v1\MetadataController as V1MetadataController;
```

### Route Structure
The API maintains the same URL structure but now uses the refactored controllers:

```
/api/v1/
├── auth/               # Authentication routes (unchanged)
├── user/               # User-specific routes (updated controllers)
│   ├── home/summary
│   ├── devices
│   ├── devices/{id}
│   ├── devices/{id}/report-issue  # Changed from /report
│   ├── profile
│   └── history
└── admin/              # Admin routes (updated controllers)
    ├── dashboard/
    │   ├── kpis
    │   └── charts
    ├── devices/
    │   ├── GET /
    │   ├── GET /{id}
    │   ├── POST /
    │   ├── PUT /{id}
    │   └── DELETE /{id}
    ├── device-assignments/
    │   ├── GET /
    │   ├── GET /{id}
    │   ├── POST /
    │   ├── PUT /{id}
    │   └── POST /{id}/return
    ├── users
    ├── branches
    └── categories
```

## Backward Compatibility

### Breaking Changes
1. **Issue Reporting**: Changed from `/devices/{id}/report` to `/devices/{id}/report-issue`
2. **Method Names**: RESTful methods now use standard names (`index`, `show`, `store`, `update`, `destroy`)

### Non-Breaking Changes
- All URL endpoints remain the same (except issue reporting)
- Request/response formats maintained
- Authentication and middleware unchanged

## Benefits of the New Structure

### 1. Better Organization
- Each controller has a single responsibility
- Related functionality grouped together
- Easier to locate and maintain code

### 2. Improved Testability
- Smaller, focused controllers are easier to test
- Dependencies are injected, making mocking easier
- Business logic separated from HTTP concerns

### 3. Enhanced Maintainability
- Changes to business logic don't affect HTTP handling
- New features can be added without modifying existing controllers
- Clear separation of concerns

### 4. SOLID Compliance
- **Single Responsibility**: Each controller handles one domain
- **Open/Closed**: New features can be added without changing existing code
- **Dependency Inversion**: Controllers depend on abstractions (services/repositories)

## Testing the Changes

### 1. Verify Route Registration
```bash
php artisan route:list --path=api/v1
```

### 2. Test API Endpoints
```bash
# Test device listing
curl -X GET "http://localhost:8000/api/v1/admin/devices" \
  -H "Authorization: Bearer {token}"

# Test device creation
curl -X POST "http://localhost:8000/api/v1/admin/devices" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"brand":"Dell","brand_name":"Test","serial_number":"TEST123","asset_code":"TEST-001","bribox_id":1,"condition":"Baik"}'
```

### 3. Monitor for Errors
- Check Laravel logs for any dependency injection issues
- Verify all services are properly registered
- Test authentication and authorization

## Rollback Plan

If issues arise, you can temporarily revert by:

1. **Commenting out new routes** and uncommenting old ones
2. **Removing RepositoryServiceProvider** from providers list
3. **Using original controllers** until issues are resolved

## Next Steps

1. **Update API Documentation**: Ensure all endpoint documentation reflects the new structure
2. **Client Updates**: Notify API consumers of the issue reporting endpoint change
3. **Monitoring**: Monitor application performance and error rates
4. **Gradual Migration**: Phase out old controllers once new ones are stable
5. **Testing**: Add comprehensive tests for the new controller structure

## Conclusion

The route updates successfully implement the new clean architecture while maintaining backward compatibility for most endpoints. The new structure provides better organization, maintainability, and follows SOLID principles for future development.
