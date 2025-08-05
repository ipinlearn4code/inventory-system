# QR Scanner API Implementation

## Endpoint

```
GET /api/v1/user/devices/scan/{qr_code}
```

## Description

This endpoint retrieves device details and assignment history based on a scanned QR code with the prefix `briven-`. It is designed for mobile clients to verify and track devices.

## Authentication

- **Required**: Yes (Bearer Token)
- **Middleware**: `auth:sanctum`, `api.timeout`, `throttle:100,1`

## Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `qr_code` | string | Yes | The QR code scanned by the mobile device. Must start with `briven-` |

## Request Example

```bash
curl -X GET \
  -H "Accept: application/json" \
  -H "Authorization: Bearer your-auth-token" \
  "http://localhost:8000/api/v1/user/devices/scan/briven-ABC12345"
```

## Response Examples

### ✅ Success Response (200)

```json
{
    "data": {
        "device": {
            "id": 22,
            "asset_code": "ABC12345",
            "name": "Laptop Lenovo ThinkPad X1",
            "type": "Laptop high end", 
            "serial_number": "SN12345678",
            "dev_date": "2024-01-10",
            "status": "digunakan",
            "condition": "baik",
            "assigned_to": {
                "id": 4,
                "name": "John Doe",
                "department": "IT Support",
                "position": "Front Officer",
                "pn": "IT265478",
                "branch": "Jakarta HQ",
                "branch_code": "BRC263739"
            },
            "spec1": "Ram 16GB",
            "spec2": "Intel i7 9650H", 
            "spec3": "Nvidia Quadro T1000",
            "spec4": "",
            "spec5": ""
        },
        "history": [
            {
                "assignment_id": 2,
                "action": "returned",
                "user": "John Doe",
                "approver": "Admin IT",
                "date": "2025-08-01",
                "note": "Device returned after project"
            },
            {
                "assignment_id": 2,
                "action": "assigned", 
                "user": "John Doe",
                "approver": "Admin IT",
                "date": "2025-07-10",
                "note": "Assigned for project A"
            }
        ]
    }
}
```

### ❌ Error Response - Invalid QR Format (400)

```json
{
    "message": "Invalid QR code format. QR code must start with \"briven-\".",
    "errorCode": "ERR_INVALID_QR_FORMAT"
}
```

### ❌ Error Response - Device Not Found (404)

```json
{
    "message": "Device not found with the provided QR code.",
    "errorCode": "ERR_DEVICE_NOT_FOUND"
}
```

### ❌ Error Response - Server Error (500)

```json
{
    "message": "An error occurred while scanning the device.",
    "errorCode": "ERR_SCAN_FAILED"
}
```

## Business Logic

### 1. QR Code Validation
- QR code must start with `briven-` prefix
- Asset code is extracted by removing the prefix
- Returns 400 error if format is invalid

### 2. Device Lookup
- Searches devices by `asset_code` field
- Uses eager loading to optimize database queries
- Returns 404 if device not found

### 3. Device Information Assembly
- **Name**: Combines category name, brand, and brand name
- **Type**: From bribox type field
- **Assigned To**: Current active assignment details (where `returned_date` is NULL)
- **Specs**: Device specifications (spec1-spec5)

### 4. Assignment History
- Retrieves all assignments ordered by assigned_date (descending)
- For each assignment:
  - Creates "returned" entry if `returned_date` exists
  - Creates "assigned" entry for the assignment
- Approver information comes from related assignment letters

## Database Relationships Used

```php
Device::with([
    'bribox.category',                              // Device type and category
    'currentAssignment.user.department',            // Current assignee details
    'currentAssignment.user.branch',               // Current assignee branch
    'assignments' => function ($query) {
        $query->with([
            'user:user_id,name',                   // Assignment user names
            'assignmentLetters.approver:user_id,name' // Letter approvers
        ])->orderBy('assigned_date', 'desc');
    }
])
```

## Performance Considerations

- Uses eager loading to prevent N+1 queries
- Optimized relationships to load only required fields
- Caching can be added for frequently scanned devices
- Error logging for debugging failed scans

## Security Features

- Authentication required via Sanctum
- Input validation and sanitization
- Parameterized queries through Eloquent ORM
- Error logging without exposing sensitive information

## Integration Notes

### Mobile Client Implementation
```javascript
// Example mobile client usage
async function scanDevice(qrCode) {
    try {
        const response = await fetch(`/api/v1/user/devices/scan/${qrCode}`, {
            headers: {
                'Authorization': `Bearer ${userToken}`,
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            return data.data; // Device and history information
        } else {
            const error = await response.json();
            throw new Error(error.message);
        }
    } catch (error) {
        console.error('QR scan failed:', error);
        throw error;
    }
}
```

### Testing

Use the provided test files:
- `test-qr-scanner-api.php` - PHP test script
- `test-qr-api.sh` - Bash/cURL test script

## Related Files

- **Route**: `routes/api/user.php`
- **Controller**: `app/Http/Controllers/Api/v1/DeviceController.php`
- **Models**: `Device.php`, `DeviceAssignment.php`, `AssignmentLetter.php`, `User.php`
- **Middleware**: Sanctum authentication, API timeout, rate limiting
