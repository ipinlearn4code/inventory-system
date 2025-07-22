# MinIO API Implementation Test Guide

## Overview
This guide helps you test the MinIO file upload/download API endpoints that have been implemented.

## Prerequisites

1. **MinIO Configuration**: Ensure MinIO is properly configured in your `.env` file:
```env
MINIO_ENDPOINT=http://localhost:9000
MINIO_KEY=your-access-key
MINIO_SECRET=your-secret-key
MINIO_REGION=us-east-1
MINIO_BUCKET=inventory-system
```

2. **Authentication Token**: You need a valid Bearer token for API access.

## Quick Test Steps

### 1. Test MinIO Health
```bash
curl -X GET \
  http://localhost:8000/api/v1/admin/files/health \
  -H 'Authorization: Bearer your-token-here'
```

Expected Response:
```json
{
    "success": true,
    "data": {
        "status": "healthy",
        "message": "MinIO storage is working properly",
        "timestamp": "2025-07-22T14:30:00Z"
    }
}
```

### 2. Upload Test File
```bash
curl -X POST \
  http://localhost:8000/api/v1/admin/files/upload \
  -H 'Authorization: Bearer your-token-here' \
  -F 'file=@test-file.pdf' \
  -F 'directory=test' \
  -F 'filename=uploaded-test.pdf'
```

### 3. Run Internal Test Script
```bash
php artisan tinker
include 'tests/minio-test.php';
```

## Available Endpoints Summary

| Method | Endpoint | Purpose |
|--------|----------|---------|
| `GET` | `/api/v1/admin/files/health` | Check MinIO health |
| `POST` | `/api/v1/admin/files/upload` | Upload general file |
| `POST` | `/api/v1/admin/files/download` | Download file by path |
| `DELETE` | `/api/v1/admin/files/delete` | Delete file |
| `POST` | `/api/v1/admin/files/assignment-letters` | Upload assignment letter |
| `GET` | `/api/v1/admin/files/assignment-letters/{id}/download` | Download assignment letter |
| `GET` | `/api/v1/admin/files/assignment-letters/{id}/url` | Get temporary URL |

## Code Implementation Details

### Controller Location
- **File**: `app/Http/Controllers/Api/MinioController.php`
- **Service**: `app/Services/MinioStorageService.php` (already exists)

### Key Features Implemented

1. **File Upload with Validation**
   - File type and size validation
   - Directory structure creation
   - Error handling and logging

2. **Secure File Download**
   - File existence validation
   - Stream-based download for performance
   - Proper content headers

3. **Assignment Letter Management**
   - Structured file storage
   - Database integration with AssignmentLetter model
   - Automatic path generation

4. **Health Monitoring**
   - MinIO connectivity check
   - Read/write operation testing
   - Service availability status

### Storage Path Structure

Assignment letters are stored following this pattern:
```
{letter_type}/{assignment_id}/{letter_date}/{letter_number}/{filename}
```

Example:
```
assignment/456/2025-07-22/letter-001/document.pdf
return/789/2025-07-22/return-letter-002/return-form.pdf
```

## Error Handling

The implementation includes comprehensive error handling for:
- MinIO service unavailability
- File validation failures
- Storage operation failures
- Authentication and authorization errors

## Security Features

- **File Type Validation**: Only allows specific MIME types
- **Size Limits**: Configurable file size restrictions
- **Path Sanitization**: Prevents directory traversal attacks
- **Role-Based Access**: Admin/SuperAdmin only
- **Temporary URLs**: Time-limited access with signatures

## Next Steps for Testing

1. **Start Laravel Development Server**:
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```

2. **Test Health Endpoint** first to ensure MinIO connectivity

3. **Test File Upload** with a small test file

4. **Verify File Storage** in your MinIO bucket

5. **Test Download Functionality**

6. **Test Assignment Letter Workflow** (requires valid assignment_id)

## Troubleshooting

### Common Issues:

1. **MinIO Connection Failed**
   - Check MinIO server is running
   - Verify environment variables
   - Test network connectivity

2. **Storage Unavailable**
   - Check MinIO bucket exists
   - Verify access credentials
   - Check file permissions

3. **File Upload Fails**
   - Verify file size limits
   - Check allowed file types
   - Review Laravel logs

### Debug Commands:

```bash
# Check MinIO configuration
php artisan config:cache

# View logs
tail -f storage/logs/laravel.log

# Test storage directly
php artisan tinker
Storage::disk('minio')->files()
```

## API Documentation

For complete API documentation, see: `App'sDocumentation/MINIO_API_DOCUMENTATION.md`

This file contains detailed endpoint descriptions, request/response examples, and integration guides.
