# Device API Documentation

## Overview
The Device API provides endpoints for managing devices in the inventory system. This includes creating, reading, updating, and deleting devices, as well as retrieving device details and managing device conditions.

## Base URL
```
/api/v1/devices
```

## Authentication
All endpoints require authentication using Bearer token in the Authorization header:
```
Authorization: Bearer {your-token-here}
```

## Endpoints

### 1. Get Devices List
Retrieve a paginated list of devices with optional filtering.

**Endpoint:** `GET /api/v1/devices`

**Query Parameters:**
- `search` (string, optional): Search by brand, brand name, serial number, or asset code
- `condition` (string, optional): Filter by device condition (`Baik`, `Rusak`, `Perlu Pengecekan`)
- `status` (string, optional): Filter by device status (`Digunakan`, `Tidak Digunakan`, `Cadangan`)
- `branchId` (integer, optional): Filter by branch ID
- `page` (integer, optional): Page number (default: 1)
- `perPage` (integer, optional): Items per page (default: 20)

**Response:**
```json
{
  "data": [
    {
      "deviceId": 1,
      "assetCode": "BRI-001",
      "brand": "Dell",
      "brandName": "Latitude 7420",
      "serialNumber": "DL123456789",
      "condition": "Baik",
      "category": "Laptop",
      "spec1": "Intel i5",
      "spec2": "8GB RAM",
      "spec3": "256GB SSD",
      "spec4": "14 inch",
      "spec5": "Windows 11",
      "isAssigned": true,
      "assignedTo": "John Doe",
      "assignedDate": "2024-01-15",
      "createdAt": "2024-01-01T10:00:00Z",
      "createdBy": "admin",
      "updatedAt": "2024-01-15T14:30:00Z",
      "updatedBy": "admin"
    }
  ],
  "meta": {
    "currentPage": 1,
    "lastPage": 5,
    "total": 95
  }
}
```

### 2. Get Device Details
Retrieve detailed information about a specific device.

**Endpoint:** `GET /api/v1/devices/{id}`

**Path Parameters:**
- `id` (integer, required): Device ID

**Response:**
```json
{
  "data": {
    "deviceId": 1,
    "assetCode": "BRI-001",
    "brand": "Dell",
    "brandName": "Latitude 7420",
    "serialNumber": "DL123456789",
    "condition": "Baik",
    "category": "Laptop",
    "spec1": "Intel i5",
    "spec2": "8GB RAM",
    "spec3": "256GB SSD",
    "spec4": "14 inch",
    "spec5": "Windows 11",
    "devDate": "2023-12-01",
    "currentAssignment": {
      "assignmentId": 1,
      "user": {
        "userId": 1,
        "name": "John Doe",
        "pn": "12345",
        "position": "Manager"
      },
      "branch": {
        "branchId": 1,
        "unitName": "Jakarta Pusat",
        "branchCode": "JKT001"
      },
      "assignedDate": "2024-01-15",
      "notes": "For daily work"
    },
    "status": "Digunakan",
    "assignmentHistory": [
      {
        "assignmentId": 1,
        "userName": "John Doe",
        "userPn": "12345",
        "assignedDate": "2024-01-15",
        "returnedDate": null,
        "notes": "For daily work"
      }
    ],
    "createdAt": "2024-01-01T10:00:00Z",
    "createdBy": "admin",
    "updatedAt": "2024-01-15T14:30:00Z",
    "updatedBy": "admin"
  }
}
```

### 3. Create Device
Create a new device in the system.

**Endpoint:** `POST /api/v1/devices`

**Request Body:**
```json
{
  "brand": "Dell",
  "brand_name": "Latitude 7420",
  "serial_number": "DL123456789",
  "asset_code": "BRI-001",
  "bribox_id": 1,
  "condition": "Baik",
  "spec1": "Intel i5",
  "spec2": "8GB RAM",
  "spec3": "256GB SSD",
  "spec4": "14 inch",
  "spec5": "Windows 11",
  "dev_date": "2023-12-01"
}
```

**Validation Rules:**
- `brand`: required, string, max 50 characters
- `brand_name`: required, string, max 50 characters
- `serial_number`: required, string, max 50 characters, unique
- `asset_code`: required, string, max 20 characters, unique
- `bribox_id`: required, must exist in briboxes table
- `condition`: required, one of: Baik, Rusak, Perlu Pengecekan
- `spec1-spec5`: optional, string, max 100 characters each
- `dev_date`: optional, valid date

**Response:**
```json
{
  "data": {
    "deviceId": 1,
    "assetCode": "BRI-001",
    "brand": "Dell",
    "brandName": "Latitude 7420",
    "serialNumber": "DL123456789",
    "condition": "Baik"
  }
}
```

### 4. Update Device
Update an existing device.

**Endpoint:** `PUT /api/v1/devices/{id}`

**Path Parameters:**
- `id` (integer, required): Device ID

**Request Body:** Same as create device, but all fields are optional (use `sometimes` validation)

**Response:** Same as create device response

### 5. Delete Device
Delete a device from the system.

**Endpoint:** `DELETE /api/v1/devices/{id}`

**Path Parameters:**
- `id` (integer, required): Device ID

**Response:**
```json
{
  "message": "Device deleted successfully.",
  "errorCode": null
}
```

**Error Response (if device is assigned):**
```json
{
  "message": "Cannot delete device that is currently assigned.",
  "errorCode": "ERR_DEVICE_ASSIGNED"
}
```

## Error Codes
- `ERR_DEVICE_NOT_FOUND`: Device with specified ID not found
- `ERR_DEVICE_ASSIGNED`: Cannot delete device that is currently assigned
- `ERR_DEVICE_CREATION_FAILED`: Failed to create device
- `ERR_DEVICE_UPDATE_FAILED`: Failed to update device
- `ERR_DEVICE_DELETION_FAILED`: Failed to delete device

## Examples

### cURL Examples

#### Get devices list with search
```bash
curl -X GET "https://api.example.com/api/v1/devices?search=Dell&condition=Baik&page=1&perPage=10" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

#### Create a new device
```bash
curl -X POST "https://api.example.com/api/v1/devices" \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "brand": "Dell",
    "brand_name": "Latitude 7420",
    "serial_number": "DL123456789",
    "asset_code": "BRI-001",
    "bribox_id": 1,
    "condition": "Baik"
  }'
```

#### Update a device
```bash
curl -X PUT "https://api.example.com/api/v1/devices/1" \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "condition": "Perlu Pengecekan",
    "spec1": "Intel i7"
  }'
```

#### Delete a device
```bash
curl -X DELETE "https://api.example.com/api/v1/devices/1" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```
