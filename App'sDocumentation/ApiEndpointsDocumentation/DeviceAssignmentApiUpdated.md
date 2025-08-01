# Device Assignment API - Updated Implementation

## Overview
This document describes the updated Device Assignment API endpoints that support creating and updating device assignments with assignment letters using multipart/form-data.

## Base URL
```
http://your-domain/api/v1
```

## Endpoints

### 1. Create Device Assignment
Creates a new device assignment with an associated assignment letter.

**Endpoint:** `POST /device-assignments`  
**Content-Type:** `multipart/form-data`  
**Authentication:** Required (Bearer Token)

#### Request Parameters
| Parameter | Type | Required | Validation | Description |
|-----------|------|----------|------------|-------------|
| `device_id` | integer | Yes | exists:devices,device_id | ID of the device to assign |
| `user_id` | integer | Yes | exists:users,user_id | ID of the user receiving the device |
| `assigned_date` | date | Yes | date, before_or_equal:today | Date when device is assigned |
| `notes` | string | No | nullable, max:500 | Additional notes for the assignment |
| `letter_number` | string | Yes | max:50 | Unique letter number |
| `letter_date` | date | Yes | date | Date of the assignment letter |
| `letter_file` | file | Yes | file, mimes:pdf, max:10240 | PDF file (max 10MB) |

#### Response
```json
{
  "data": {
    "assignmentId": 123,
    "assetCode": "AST001234",
    "brand": "LENOVO",
    "brandName": "ThinkPad X1",
    "serialNumber": "SN1234567890",
    "assignedTo": "John Doe",
    "userPn": "12345678",
    "unitName": "IT Department",
    "assignedDate": "2025-08-01",
    "notes": "Standard laptop assignment",
    "assignmentLetters": [
      {
        "assignmentLetterId": 456,
        "assignmentType": "assignment",
        "letterNumber": "ASG/2025/001",
        "letterDate": "2025-08-01",
        "fileUrl": "http://your-domain/assignment-letter/456/preview"
      }
    ]
  }
}
```

#### Example Request (cURL)
```bash
curl -X POST http://localhost:8000/api/v1/test-assignments \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "device_id=1" \
  -F "user_id=2" \
  -F "assigned_date=2025-08-01" \
  -F "notes=Standard laptop assignment" \
  -F "letter_number=ASG/2025/001" \
  -F "letter_date=2025-08-01" \
  -F "letter_file=@assignment_letter.pdf"
```

---

### 2. Update Device Assignment
Updates an existing device assignment and/or its associated letter. Supports partial updates.

**Endpoint:** `PATCH /device-assignments/{id}`  
**Content-Type:** `multipart/form-data`  
**Authentication:** Required (Bearer Token)

#### Request Parameters
| Parameter | Type | Required | Validation | Description |
|-----------|------|----------|------------|-------------|
| `notes` | string | No | sometimes, nullable, max:500 | Updated assignment notes |
| `assigned_date` | date | No | sometimes, date, before_or_equal:today | Updated assignment date |
| `letter_number` | string | No | sometimes, max:50 | Updated letter number |
| `letter_date` | date | No | sometimes, date | Updated letter date |
| `letter_file` | file | No | sometimes, file, mimes:pdf, max:10240 | New PDF file (replaces existing) |

#### Important Notes
- **Not Allowed:** `device_id` and `user_id` cannot be updated for data integrity
- **File Replacement:** When `letter_file` is provided, it replaces the existing file with rollback protection
- **Partial Updates:** Only include fields you want to update
- **Transaction Safety:** All changes are wrapped in database transactions

#### Response
```json
{
  "data": {
    "assignmentId": 123,
    "assetCode": "AST001234",
    "brand": "LENOVO",
    "brandName": "ThinkPad X1",
    "serialNumber": "SN1234567890",
    "assignedTo": "John Doe",
    "userPn": "12345678",
    "unitName": "IT Department",
    "assignedDate": "2025-07-30",
    "notes": "Updated assignment notes",
    "assignmentLetters": [
      {
        "assignmentLetterId": 456,
        "assignmentType": "assignment",
        "letterNumber": "ASG/2025/002",
        "letterDate": "2025-08-01",
        "fileUrl": "http://your-domain/assignment-letter/456/preview"
      }
    ]
  }
}
```

#### Example Requests (cURL)

**Update only notes and date:**
```bash
curl -X PATCH http://localhost:8000/api/v1/test-assignments/123 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "notes=Updated assignment notes" \
  -F "assigned_date=2025-07-30"
```

**Update letter details:**
```bash
curl -X PATCH http://localhost:8000/api/v1/test-assignments/123 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "letter_number=ASG/2025/002" \
  -F "letter_date=2025-08-01"
```

**Replace letter file:**
```bash
curl -X PATCH http://localhost:8000/api/v1/test-assignments/123 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "letter_file=@new_assignment_letter.pdf"
```

**Update everything:**
```bash
curl -X PATCH http://localhost:8000/api/v1/test-assignments/123 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "notes=Fully updated assignment" \
  -F "assigned_date=2025-07-29" \
  -F "letter_number=ASG/2025/003" \
  -F "letter_date=2025-08-01" \
  -F "letter_file=@updated_letter.pdf"
```

---

## Error Handling

### Common Error Responses

#### 400 Bad Request - Duplicate Letter Number
```json
{
  "message": "Letter number already used. Please use a different letter number.",
  "errorCode": "ERR_DUPLICATE_LETTER_NUMBER"
}
```

#### 400 Bad Request - Device Already Assigned
```json
{
  "message": "Device is already assigned to another user.",
  "errorCode": "ERR_DEVICE_ALREADY_ASSIGNED"
}
```

#### 400 Bad Request - User Already Has Device Type
```json
{
  "message": "User already has an active assignment of this device type.",
  "errorCode": "ERR_USER_ALREADY_HAS_DEVICE_TYPE"
}
```

#### 400 Bad Request - File Update Failed
```json
{
  "message": "Failed to update letter file: Could not backup existing file",
  "errorCode": "ERR_ASSIGNMENT_UPDATE_FAILED"
}
```

#### 422 Unprocessable Entity - Validation Errors
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "letter_file": [
      "The letter file must be a file of type: pdf."
    ],
    "assigned_date": [
      "The assigned date must be a date before or equal to today."
    ]
  }
}
```

#### 404 Not Found - Assignment Not Found
```json
{
  "message": "Assignment not found",
  "errorCode": "ERR_ASSIGNMENT_NOT_FOUND"
}
```

---

## File Handling Details

### File Storage Structure
Files are stored in MinIO with the following path structure:
```
{assignment_id}/{letter_type}/{original_filename.ext}
```

**Examples:**
- `123/assignment/surat_penugasan.pdf`
- `456/assignment/assignment_letter-1.pdf` (collision handling)

### File Features
- **Original Filename Preservation:** Files maintain their original names (slugified)
- **Collision Avoidance:** Duplicate filenames get suffixes (-1, -2, etc.)
- **Rollback Protection:** File updates include backup and rollback on failure
- **Automatic Cleanup:** Files are deleted when assignment letters are removed

### File Size & Type Limits
- **Maximum Size:** 10MB
- **Allowed Types:** PDF only (`application/pdf`)
- **Validation:** Files are validated before storage

---

## Test Routes (Development Only)

For easier testing without authentication:

### Test Endpoints
- `POST /api/v1/test-assignments` - Create assignment (same as above)
- `PATCH /api/v1/test-assignments/{id}` - Update assignment (same as above)

These routes bypass authentication and are intended for development/testing only.

---

## Implementation Details

### Transaction Safety
- All operations are wrapped in database transactions
- File operations include rollback protection
- Partial failures are handled gracefully

### Logging
- Assignment actions are logged via InventoryLogService
- File operations are logged for audit purposes
- Error details are logged for troubleshooting

### Consistency with Store Method
- Update method follows the same patterns as store method
- Response structures are identical
- File handling uses the same underlying services

### Performance Considerations
- Eager loading of related models
- Efficient file operations with MinIO
- Optimized database queries with proper indexing
