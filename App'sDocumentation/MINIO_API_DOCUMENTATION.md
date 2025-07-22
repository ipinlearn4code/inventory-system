# MinIO File Management API Documentation

This document provides comprehensive information about the MinIO file management API endpoints for the Inventory Control System.

## Base URL
```
/api/v1/admin/files
```

## Authentication
All endpoints require authentication via Laravel Sanctum token and admin/superadmin role.

**Headers Required:**
```
Authorization: Bearer {your-token}
Content-Type: multipart/form-data (for uploads)
Content-Type: application/json (for other requests)
```

## Endpoints

### 1. Upload Assignment Letter

Upload an official assignment letter document to MinIO storage.

**Endpoint:** `POST /api/v1/admin/files/assignment-letters`

**Request Body (multipart/form-data):**
```json
{
    "file": "file (PDF/JPG, max 10MB)",
    "assignment_id": "integer (required)",
    "letter_type": "string (assignment|return)",
    "letter_number": "string (max 100 chars)",
    "letter_date": "date (YYYY-MM-DD)",
    "approver_id": "integer (user_id)"
}
```

**Success Response (201):**
```json
{
    "success": true,
    "message": "Assignment letter uploaded successfully",
    "data": {
        "letter_id": 123,
        "file_path": "assignment/456/2025-07-22/letter-001/document.pdf",
        "download_url": "/api/v1/admin/files/assignment-letters/123/download"
    }
}
```

**cURL Example:**
```bash
curl -X POST \
  http://localhost:8000/api/v1/admin/files/assignment-letters \
  -H 'Authorization: Bearer your-token-here' \
  -F 'file=@/path/to/document.pdf' \
  -F 'assignment_id=456' \
  -F 'letter_type=assignment' \
  -F 'letter_number=LETTER-001' \
  -F 'letter_date=2025-07-22' \
  -F 'approver_id=789'
```

### 2. Download Assignment Letter

Download an assignment letter file by letter ID.

**Endpoint:** `GET /api/v1/admin/files/assignment-letters/{letterId}/download`

**Parameters:**
- `letterId` (path): Integer - The assignment letter ID

**Success Response:** File download stream

**Error Response (404):**
```json
{
    "success": false,
    "message": "Assignment letter not found",
    "error_code": "LETTER_NOT_FOUND"
}
```

**cURL Example:**
```bash
curl -X GET \
  http://localhost:8000/api/v1/admin/files/assignment-letters/123/download \
  -H 'Authorization: Bearer your-token-here' \
  --output downloaded-file.pdf
```

### 3. Get Assignment Letter URL

Get a temporary signed URL for an assignment letter (valid for 60 minutes).

**Endpoint:** `GET /api/v1/admin/files/assignment-letters/{letterId}/url`

**Success Response (200):**
```json
{
    "success": true,
    "data": {
        "url": "https://minio.example.com/bucket/path/to/file?signature=...",
        "expires_at": "2025-07-22T15:30:00Z",
        "letter_info": {
            "letter_id": 123,
            "letter_type": "assignment",
            "letter_number": "LETTER-001",
            "letter_date": "2025-07-22"
        }
    }
}
```

**cURL Example:**
```bash
curl -X GET \
  http://localhost:8000/api/v1/admin/files/assignment-letters/123/url \
  -H 'Authorization: Bearer your-token-here'
```

### 4. Upload General File

Upload any file to MinIO storage for general purposes.

**Endpoint:** `POST /api/v1/admin/files/upload`

**Request Body (multipart/form-data):**
```json
{
    "file": "file (max 10MB)",
    "directory": "string (optional, default: 'general')",
    "filename": "string (optional, uses original name)"
}
```

**Success Response (201):**
```json
{
    "success": true,
    "message": "File uploaded successfully",
    "data": {
        "path": "general/filename.pdf",
        "url": "https://minio.example.com/bucket/general/filename.pdf?signature=...",
        "size": 1048576,
        "mime_type": "application/pdf",
        "original_name": "document.pdf"
    }
}
```

**cURL Example:**
```bash
curl -X POST \
  http://localhost:8000/api/v1/admin/files/upload \
  -H 'Authorization: Bearer your-token-here' \
  -F 'file=@/path/to/document.pdf' \
  -F 'directory=reports' \
  -F 'filename=monthly-report.pdf'
```

### 5. Download General File

Download any file from MinIO storage using its path.

**Endpoint:** `POST /api/v1/admin/files/download`

**Request Body (JSON):**
```json
{
    "path": "string (required) - File path in MinIO"
}
```

**Success Response:** File download stream

**cURL Example:**
```bash
curl -X POST \
  http://localhost:8000/api/v1/admin/files/download \
  -H 'Authorization: Bearer your-token-here' \
  -H 'Content-Type: application/json' \
  -d '{"path": "general/document.pdf"}' \
  --output downloaded-file.pdf
```

### 6. Delete File

Delete a file from MinIO storage.

**Endpoint:** `DELETE /api/v1/admin/files/delete`

**Request Body (JSON):**
```json
{
    "path": "string (required) - File path in MinIO"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "File deleted successfully"
}
```

**cURL Example:**
```bash
curl -X DELETE \
  http://localhost:8000/api/v1/admin/files/delete \
  -H 'Authorization: Bearer your-token-here' \
  -H 'Content-Type: application/json' \
  -d '{"path": "general/document.pdf"}'
```

### 7. Health Check

Check MinIO storage service health and connectivity.

**Endpoint:** `GET /api/v1/admin/files/health`

**Success Response (200):**
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

**Error Response (503):**
```json
{
    "success": false,
    "data": {
        "status": "error",
        "message": "MinIO storage connection failed: Connection timeout",
        "timestamp": "2025-07-22T14:30:00Z"
    }
}
```

**cURL Example:**
```bash
curl -X GET \
  http://localhost:8000/api/v1/admin/files/health \
  -H 'Authorization: Bearer your-token-here'
```

## Error Codes

| Code | Description |
|------|-------------|
| `STORAGE_UNAVAILABLE` | MinIO service is not available |
| `STORAGE_FAILED` | Failed to store file in MinIO |
| `LETTER_NOT_FOUND` | Assignment letter not found |
| `FILE_NOT_FOUND` | File not found in storage |
| `DOWNLOAD_FAILED` | Failed to download file |
| `URL_GENERATION_FAILED` | Failed to generate temporary URL |
| `DELETE_FAILED` | Failed to delete file |
| `INTERNAL_ERROR` | Internal server error |

## File Storage Structure

Assignment letters are stored with the following path structure:
```
{letter_type}/{assignment_id}/{letter_date}/{letter_number}/{filename}
```

Example:
```
assignment/456/2025-07-22/letter-001/document.pdf
return/789/2025-07-22/return-letter-002/return-form.pdf
```

General files are stored in:
```
{directory}/{filename}
```

## Validation Rules

### Assignment Letter Upload
- **file**: Required, PDF/JPG only, max 10MB
- **assignment_id**: Required, must exist in device_assignments table
- **letter_type**: Required, must be 'assignment' or 'return'
- **letter_number**: Required, max 100 characters
- **letter_date**: Required, valid date format
- **approver_id**: Required, must exist in users table

### General File Upload
- **file**: Required, max 10MB
- **directory**: Optional, max 255 characters
- **filename**: Optional, max 255 characters

## Rate Limiting

All endpoints are subject to rate limiting:
- **100 requests per minute** per authenticated user
- **30-second timeout** per request

## Security Notes

1. All file uploads are validated for size and type
2. File paths are sanitized to prevent directory traversal attacks
3. Temporary URLs expire after 60 minutes
4. All operations require admin-level authentication
5. File access is logged for audit purposes

## Testing with Postman

1. Import the provided Postman collection
2. Set the base URL to your Laravel application URL
3. Add your Bearer token to the Authorization header
4. Test each endpoint with sample data

## JavaScript Example

```javascript
// Upload assignment letter
const uploadAssignmentLetter = async (file, assignmentData) => {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('assignment_id', assignmentData.assignment_id);
    formData.append('letter_type', assignmentData.letter_type);
    formData.append('letter_number', assignmentData.letter_number);
    formData.append('letter_date', assignmentData.letter_date);
    formData.append('approver_id', assignmentData.approver_id);

    const response = await fetch('/api/v1/admin/files/assignment-letters', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`
        },
        body: formData
    });

    return response.json();
};

// Download assignment letter
const downloadAssignmentLetter = async (letterId) => {
    const response = await fetch(`/api/v1/admin/files/assignment-letters/${letterId}/download`, {
        headers: {
            'Authorization': `Bearer ${token}`
        }
    });

    if (response.ok) {
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'assignment-letter.pdf';
        a.click();
    }
};
```

## PHP Example (Laravel HTTP Client)

```php
use Illuminate\Support\Facades\Http;

// Upload assignment letter
$response = Http::withToken($token)
    ->attach('file', file_get_contents($filePath), 'document.pdf')
    ->post('/api/v1/admin/files/assignment-letters', [
        'assignment_id' => 456,
        'letter_type' => 'assignment',
        'letter_number' => 'LETTER-001',
        'letter_date' => '2025-07-22',
        'approver_id' => 789
    ]);

// Get assignment letter URL
$response = Http::withToken($token)
    ->get('/api/v1/admin/files/assignment-letters/123/url');

$data = $response->json();
```
