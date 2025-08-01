## Device Assignment API - Update Endpoint

### PATCH /api/v1/admin/device-assignments/{id}

Updates a device assignment and optionally its associated letter. Uses direct Eloquent/DB operations with transaction support for atomicity.

#### Additional Field (to use POST method as patch)

When using the POST method to simulate a PATCH request, include the following field in the form-data:

- **Key**: `_method`
- **Value**: `PATCH`

This allows Laravel to interpret the request as a PATCH operation, enabling partial updates for the resource.

#### Request Parameters

##### URL Parameters:
- `id` (required) - The ID of the device assignment to update

##### Body Parameters (multipart/form-data or JSON):
```json
{
    "device_id": "integer", // optional - Must exist in devices table
    "user_id": "integer", // optional - Must exist in users table
    "notes": "string", // optional - Max 500 characters, nullable
    "assigned_date": "date", // optional - Must be <= today
    "letter_number": "string", // optional - Max 50 characters
    "letter_date": "date", // optional
    "letter_file": "file" // optional - PDF file, max 10MB
}
```

**Note**: For file uploads, use `multipart/form-data`. For simple updates without files, JSON is supported.

#### Response

##### Success Response (200 OK)

###### Assignment Only Update:
```json
{
    "assignmentId": 3,
    "deviceId": 20,
    "assetCode": "ASTD018",
    "brand": "Samsung Model 18",
    "serialNumber": "SN000018",
    "assignedTo": "User Dummy 1",
    "unitName": "Jakarta Central",
    "assignedDate": "2025-01-29T17:00:00.000000Z",
    "returnedDate": null,
    "notes": "I tried to update this fields"
}
```

###### Assignment + Letter Update:
```json
{
    "assignmentId": 3,
    "deviceId": 20,
    "assetCode": "ASTD018",
    "brand": "Samsung Model 18",
    "serialNumber": "SN000018",
    "assignedTo": "User Dummy 1",
    "unitName": "Jakarta Central",
    "assignedDate": "2025-01-29T17:00:00.000000Z",
    "returnedDate": null,
    "notes": "I tried to update this fields",
    "assignmentLetters": [
        {
            "assignmentLetterId": 123,
            "assignmentType": "assignment",
            "letterNumber": "SK-002/IT/919",
            "letterDate": "2025-01-15",
            "fileUrl": "http://localhost:8000/preview/assignment-letters/123"
        }
    ]
}
```

##### Error Responses

###### 400 Bad Request
```json
{
    "message": "string",
    "errorCode": "string"
}
```

###### Common Error Codes:
- `ERR_ASSIGNMENT_UPDATE_FAILED`: Generic update failure
- `ERR_DUPLICATE_LETTER_NUMBER`: Letter number already exists in system
- `ERR_DEVICE_ALREADY_ASSIGNED`: Device is already assigned to another user
- `ERR_USER_ALREADY_HAS_DEVICE_TYPE`: User already has an active assignment for this device type

#### Technical Implementation

- **Database Operations**: Uses direct `DeviceAssignment::where()->update()` and `AssignmentLetter` model operations
- **Transaction Safety**: All operations wrapped in `DB::transaction()` for atomicity
- **File Handling**: Supports file upload/update with rollback protection via `AssignmentLetter::updateFile()`
- **Validation**: Laravel request validation with `sometimes` rules for partial updates
- **Authentication**: Requires bearer token authentication
- **Authorization**: Admin/SuperAdmin role required

#### Postman Usage Tips

##### Method 1: JSON (without file)
```bash
Method: PATCH
Content-Type: application/json
Body: raw JSON
{
    "notes": "Updated notes",
    "assigned_date": "2025-08-01"
}
```

##### Method 2: Form-data with Method Spoofing (with file)
```bash
Method: POST
Body: form-data
Fields:
- _method: PATCH
- notes: Updated notes
- letter_number: SK-003/IT/920
- letter_date: 2025-08-01
- letter_file: [PDF file]
```

#### Example Usage

```bash
# Update assignment notes only (JSON)
curl -X PATCH http://localhost:8000/api/v1/admin/device-assignments/3 \
  -H "Authorization: Bearer your-token" \
  -H "Content-Type: application/json" \
  -d '{
    "notes": "Device condition updated",
    "assigned_date": "2025-08-01"
  }'

# Update with letter file (Form-data with method spoofing)
curl -X POST http://localhost:8000/api/v1/admin/device-assignments/3 \
  -H "Authorization: Bearer your-token" \
  -F "_method=PATCH" \
  -F "notes=Updated with new letter" \
  -F "letter_number=SK-004/IT/921" \
  -F "letter_date=2025-08-01" \
  -F "letter_file=@new_assignment_letter.pdf"
```

#### Security & Validation

- **Authentication**: Bearer token required
- **Authorization**: Admin/SuperAdmin roles only
- **File Validation**: PDF files only, max 10MB
- **Database Integrity**: Foreign key constraints enforced
- **Rollback Protection**: File operations with cleanup on failure
- **Input Sanitization**: All inputs validated and sanitized

#### Notes

- Partial updates supported - only include fields you want to change
- File uploads automatically handle existing file replacement
- Letter creation is automatic if assignment has no existing letter
- All database operations are atomic via transactions
- Response format maintains consistency with existing API structure
