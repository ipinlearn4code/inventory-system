# Storage Health Monitoring Documentation

## Overview

This system includes comprehensive storage health monitoring for MinIO and local storage systems. The monitoring provides real-time status updates, warnings, and error notifications across the application.

## Features

### 1. Storage Health Service (`StorageHealthService`)

**Location:** `app/Services/StorageHealthService.php`

**Features:**
- Real-time health checking for MinIO and public storage
- Caching to prevent excessive health checks (5-minute cache)
- Detailed status reporting with response times and connection details
- Overall storage system status aggregation

**Methods:**
- `checkMinioHealth()` - Check MinIO connection and read/write capabilities
- `checkAllStorageHealth()` - Check all storage systems
- `refreshStorageHealth()` - Force refresh bypassing cache
- `isMinioHealthy()` - Quick boolean check for MinIO status

### 2. Dashboard Storage Status Widget

**Location:** `app/Filament/Widgets/StorageStatusWidget.php`

**Features:**
- Real-time storage status display on the dashboard
- Visual indicators (icons and color coding)
- One-click refresh functionality
- Detailed breakdown of MinIO and public storage health
- Response time monitoring

**Status Indicators:**
- 🟢 **Healthy** - All systems operational
- 🟡 **Warning** - Minor issues detected
- 🔴 **Error** - Storage system unavailable

### 3. Assignment Letter Page Monitoring

**Features:**
- Storage status alerts at the top of Assignment Letter pages
- Automatic warning display when storage issues are detected
- File upload form indicators showing storage health
- Dynamic help text based on storage status

**Pages with Monitoring:**
- List Assignment Letters
- Create Assignment Letter
- Edit Assignment Letter

### 4. Storage Status Alerts (`StorageStatusAlert` Livewire Component)

**Location:** `app/Livewire/StorageStatusAlert.php`

**Features:**
- Prominent alert banners for storage issues
- Dismissible notifications
- Real-time status refresh
- Impact descriptions for users

### 5. Command Line Health Check

**Command:** `php artisan storage:health-check [--refresh]`

**Features:**
- Comprehensive command-line storage diagnostics
- Colored output with status indicators
- Detailed recommendations for issue resolution
- Cache refresh option for immediate checking

## Storage Status Types

### Healthy ✅
- All storage systems are operational
- Read/write tests pass
- Connection response times are normal
- No user action required

### Warning ⚠️
- Storage is functional but with performance issues
- Slower response times detected
- Some operations may be delayed
- Monitor closely but system remains usable

### Error ❌
- Storage system is not accessible
- Connection failures detected
- File operations will fail
- Immediate attention required

## Implementation Details

### Health Check Process

1. **Connection Test** - Verify basic connectivity to storage endpoints
2. **Write Test** - Attempt to upload a small test file
3. **Read Test** - Verify the uploaded file can be retrieved
4. **Cleanup** - Remove test files
5. **Performance Monitoring** - Measure response times

### Caching Strategy

- Health checks are cached for 5 minutes to prevent excessive API calls
- Cache can be bypassed using refresh methods
- Cache keys are prefixed with `storage_health_`

### Error Handling

- All storage operations include comprehensive error handling
- Errors are logged with detailed context
- Fallback messaging for various failure scenarios
- User-friendly error descriptions

## Configuration

### Environment Variables

```env
# MinIO Configuration
MINIO_ACCESS_KEY=minioadmin
MINIO_SECRET_KEY=minioadmin123
MINIO_REGION=us-east-1
MINIO_BUCKET=assignment-letter
MINIO_ENDPOINT=http://localhost:9000
MINIO_URL=http://localhost:9000
MINIO_USE_PATH_STYLE_ENDPOINT=true

# Primary storage disk
FILESYSTEM_DISK=minio
```

### Storage Configuration

The system uses the `filesystems.php` configuration with the following disks:
- **minio** - Primary storage for assignment letters
- **public** - Backup/local storage
- **local** - Private local storage

## Usage Examples

### Check Storage Health Programmatically

```php
$healthService = app(\App\Services\StorageHealthService::class);

// Quick health check
$isHealthy = $healthService->isMinioHealthy();

// Detailed health report
$status = $healthService->checkAllStorageHealth();

// Force refresh
$refreshedStatus = $healthService->refreshStorageHealth();
```

### Display Storage Status in Views

```php
@php
    $healthService = app(\App\Services\StorageHealthService::class);
    $status = $healthService->checkMinioHealth();
@endphp

@if($status['status'] !== 'healthy')
    <div class="alert alert-warning">
        Storage Issue: {{ $status['message'] }}
    </div>
@endif
```

### Command Line Monitoring

```bash
# Basic health check
php artisan storage:health-check

# Force refresh cache
php artisan storage:health-check --refresh
```

## Troubleshooting

### Common Issues

1. **MinIO Connection Failed**
   - Verify MinIO server is running
   - Check endpoint URL in .env
   - Confirm credentials are correct

2. **Bucket Access Denied**
   - Verify bucket exists
   - Check access key permissions
   - Confirm bucket policy allows read/write

3. **Public Storage Issues**
   - Check file system permissions
   - Verify storage directories exist
   - Confirm disk space availability

### Diagnostic Steps

1. Run the health check command:
   ```bash
   php artisan storage:health-check --refresh
   ```

2. Check application logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. Test MinIO connection manually:
   ```bash
   php artisan tinker
   Storage::disk('minio')->files()
   ```

## Monitoring Integration

The storage health monitoring integrates with:
- Laravel's logging system
- Filament notification system
- Cache system for performance
- Livewire for real-time updates

## Future Enhancements

Planned improvements include:
- Email notifications for storage failures
- Historical health monitoring
- Storage usage analytics
- Automated failover mechanisms
- Integration with external monitoring tools

## Security Considerations

- Health check operations use minimal test files
- No sensitive data is used in health checks
- All test files are immediately cleaned up
- Access credentials are never logged in health reports
