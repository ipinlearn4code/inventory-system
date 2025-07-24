# Device Assignment API Documentation

## Overview
The Device Assignment API provides endpoints for managing device assignments to users. This includes creating assignments, updating assignment details, returning devices, and retrieving assignment information.

## Base URL
```
/api/v1/device-assignments
```

## Authentication
All endpoints require authentication using Bearer token in the Authorization header:
```
Authorization: Bearer {your-token-here}
```

## Endpoints

### 1. Get Device Assignments List
Retrieve a paginated list of device assignments with optional filtering.

**Endpoint:** `GET /api/v1/device-assignments`

**Query Parameters:**
- `search` (string, optional): Search by device asset code, brand, serial number, or user name/PN
- `status` (string, optional): Filter by assignment status
- `branchId` (integer, optional): Filter by branch ID
- `activeOnly` (boolean, optional): Show only active assignments (not returned)
- `page` (integer, optional): Page number (default: 1)
- `perPage` (integer, optional): Items per page (default: 20)

**Response:**
```json
{
  "data": [
    {
      "assignmentId": 1,
      "assetCode": "BRI-001",
      "brand": "HP",
      "brandName": "EliteBook 840",
      "serialNumber": "DL123456789",
      "assignedTo": "John Doe",
      "unitName": "Jakarta Pusat",
      "status": "Digunakan",
      "assignedDate": "2024-01-15",
      "returnedDate": null
    }
  ],
  "meta": {
    "currentPage": 1,
    "lastPage": 3,
    "total": 45
  }
}
```

### 2. Get Assignment Details
Retrieve detailed information about a specific device assignment.

**Endpoint:** `GET /api/v1/device-assignments/{id}`

**Path Parameters:**
- `id` (integer, required): Assignment ID

**Response:**
```json
{
  "data": {
    "assignmentId": 1,
    "deviceId": 1,
    "assetCode": "AST001",
    "brand": "Dell Dell OptiPlex 7090",
    "serialNumber": "DL001234567",
    "assignedTo": "John Doe",
    "unitName": "Jakarta Central",
    "assignedDate": "2024-01-01T00:00:00.000000Z",
    "returnedDate": null,
    "status": null,
    "notes": "Initial assignment",
    "assignmentLetters": [
      {
        "assignmentLetterId": 9,
        "assignmentType": "assignment",
        "letterNumber": "nkjnef8909023",
        "letterDate": "2025-07-24T00:00:00.000000Z",
        "fileUrl": "http://localhost:8000/assignment-letter/9/preview"
      },
      {
        "assignmentLetterId": 14,
        "assignmentType": "maintenance",
        "letterNumber": "sssssssssssssssssssssssssss",
        "letterDate": "2025-07-24T00:00:00.000000Z",
        "fileUrl": "http://localhost:8000/assignment-letter/14/preview"
      }
    ]
  }
}
```

**Note:** The `assignmentLetters` array contains all letters associated with the assignment. Each letter includes:
- `assignmentLetterId`: Unique identifier of the letter
- `assignmentType`: Type of the letter (e.g., "assignment", "maintenance")
- `letterNumber`: The official letter number
- `letterDate`: The date of the letter
- `fileUrl`: URL to preview the letter file (requires authentication)
```

### 3. Create Device Assignment
Assign a device to a user.

**Endpoint:** `POST /api/v1/device-assignments`

**Request Body:**
```json
{
  "device_id": 1,
  "user_id": 1,
  "assigned_date": "2024-01-15",
  "status": "Digunakan",
  "notes": "For daily work activities",
  "letter_number": "SK-001/IT/2024",
  "letter_date": "2024-01-15",
  "letter_file": "<PDF_FILE>"
}
```

**Validation Rules:**
- `device_id`: required, must exist in devices table
- `user_id`: required, must exist in users table
- `assigned_date`: required, valid date, cannot be in the future
- `status`: optional, one of: Digunakan, Tidak Digunakan, Cadangan
- `notes`: optional, string, max 500 characters
- `letter_number`: required, string, max 50 characters
- `letter_date`: required, valid date
- `letter_file`: required, PDF file format, max 10MB

**Response:**
```json
{
  "data": {
    "assignmentId": 1,
    "deviceId": 1,
    "userId": 1,
    "assignedDate": "2024-01-15",
    "status": "Digunakan",
    "assignmentLetters": [
      {
        "assignmentLetterId": 1,
        "assignmentType": "assignment",
        "letterNumber": "SK-001/IT/2024",
        "letterDate": "2024-01-15",
        "fileUrl": "http://localhost:8000/assignment-letter/1/preview"
      }
    ]
  }
}
```

**Error Responses:**
```json
{
  "message": "Device is already assigned to another user.",
  "errorCode": "ERR_DEVICE_ALREADY_ASSIGNED"
}
```

```json
{
  "message": "User already has an active assignment for device type 'Laptop' in category 'IT Equipment'.",
  "errorCode": "ERR_USER_ALREADY_HAS_DEVICE_TYPE"
}
```

### 4. Update Device Assignment
Update an existing device assignment.

**Endpoint:** `PUT /api/v1/device-assignments/{id}`

**Path Parameters:**
- `id` (integer, required): Assignment ID

**Request Body:**
```json
{
  "status": "Tidak Digunakan",
  "notes": "Updated notes",
  "returned_date": "2024-02-15"
}
```

**Validation Rules:**
- `status`: optional, one of: Digunakan, Tidak Digunakan, Cadangan
- `notes`: optional, string, max 500 characters
- `returned_date`: optional, valid date, must be after assigned_date

**Response:**
```json
{
  "data": {
    "assignmentId": 1,
    "status": "Tidak Digunakan",
    "returnedDate": "2024-02-15",
    "notes": "Updated notes"
  }
}
```

### 5. Return Device
Mark a device assignment as returned.

**Endpoint:** `POST /api/v1/device-assignments/{id}/return`

**Path Parameters:**
- `id` (integer, required): Assignment ID

**Request Body:**
```json
{
  "returned_date": "2024-02-15",
  "return_notes": "Device returned in good condition",
  "letter_number": "RTN-001/IT/2024",
  "letter_date": "2024-02-15",
  "letter_file": "<PDF_FILE>"
}
```

**Validation Rules:**
- `returned_date`: optional, valid date, must be after assigned_date (defaults to today)
- `return_notes`: optional, string, max 500 characters
- `letter_number`: required, string, max 50 characters
- `letter_date`: required, valid date
- `letter_file`: required, PDF file format, max 10MB

**Response:**
```json
{
  "data": {
    "assignmentId": 1,
    "returnedDate": "2024-02-15",
    "message": "Device returned successfully",
    "assignmentLetter": {
      "assignmentLetterId": 15,
      "assignmentType": "return",
      "letterNumber": "RTN-001/IT/2024",
      "letterDate": "2024-02-15",
      "fileUrl": "http://localhost:8000/assignment-letter/15/preview"
    }
  }
}
```

**Error Response:**
```json
{
  "message": "Device has already been returned.",
  "errorCode": "ERR_DEVICE_ALREADY_RETURNED"
}
```

## Business Rules

### Device Assignment Rules
1. **One Device Per User Per Category**: A user cannot have multiple active assignments for devices in the same category (e.g., only one laptop per user).
2. **Device Availability**: Only devices without current assignments can be assigned.
3. **Auto Branch Assignment**: When assigning a device, the assignment automatically uses the user's branch.
4. **Status Updates**: When a device is assigned, its status is updated to "Digunakan" (or specified status).
5. **Return Process**: When a device is returned, its status is automatically updated to "Tidak Digunakan".

### Validation Business Logic
- Assignment date cannot be in the future
- Return date must be after assignment date
- Cannot return a device that has already been returned
- Cannot delete assignments with active devices

## Error Codes
- `ERR_ASSIGNMENT_NOT_FOUND`: Assignment with specified ID not found
- `ERR_DEVICE_ALREADY_ASSIGNED`: Device is already assigned to another user
- `ERR_USER_ALREADY_HAS_DEVICE_TYPE`: User already has an active assignment for this device type/category
- `ERR_DEVICE_ALREADY_RETURNED`: Device has already been returned
- `ERR_ASSIGNMENT_CREATION_FAILED`: Failed to create assignment
- `ERR_ASSIGNMENT_UPDATE_FAILED`: Failed to update assignment
- `ERR_RETURN_FAILED`: Failed to return device

## Examples

### cURL Examples

#### Get active assignments for a specific branch
```bash
curl -X GET "https://api.example.com/api/v1/device-assignments?activeOnly=true&branchId=1&page=1&perPage=10" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

#### Create a new device assignment
```bash
curl -X POST "https://api.example.com/api/v1/device-assignments" \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "device_id": 1,
    "user_id": 1,
    "assigned_date": "2024-01-15",
    "status": "Digunakan",
    "notes": "For daily work activities"
  }'
```

#### Return a device
```bash
curl -X POST "https://api.example.com/api/v1/device-assignments/1/return" \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "returned_date": "2024-02-15",
    "return_notes": "Device returned in good condition"
  }'
```

#### Update assignment notes
```bash
curl -X PUT "https://api.example.com/api/v1/device-assignments/1" \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "notes": "Updated assignment notes",
    "status": "Cadangan"
  }'
```
