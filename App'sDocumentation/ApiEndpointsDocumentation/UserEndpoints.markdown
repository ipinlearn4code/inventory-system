# API Documentation - User Role Endpoints (Mobile Inventory App v1.1)

**Base URL**: `https://api.inventory-app.com/api/v1`

**Last Updated**: 10 July 2025

## Authentication (Laravel Sanctum)
All endpoints require an `Authorization` header with a Bearer Token.

**Header Example**:
```
Accept: application/json
Authorization: Bearer 1|aBcDeFgHiJkLmNoPqRsTuVwXyZ1234567890abc
X-Device-Info: iOS 18.1, App v1.0.0
```

**Rate Limiting**: 100 requests per minute per user. Exceeding this returns a `429 Too Many Requests` response.  
**Timeout**: Requests timeout after 30 seconds.  
Failure to provide a valid token results in a `401 Unauthorized` response.

## Standard Response Format
### ✅ Success (200 OK, 201 Created)
```json
{
    "data": {
        "userId": 1,
        "name": "Budi Santoso"
    }
}
```

### ❌ Error (4xx, 5xx)
```json
// 404 Not Found
{
    "message": "Resource not found.",
    "errorCode": "ERR_RESOURCE_NOT_FOUND"
}
// 422 Unprocessable Entity
{
    "message": "Invalid data provided.",
    "errorCode": "ERR_VALIDATION_FAILED",
    "errors": {
        "email": ["Invalid email format."]
    }
}
```

## Offline Support
Cache responses for `/user/home/summary`, `/user/devices`, and `/user/profile` for offline access. Sync when online.

## 2. User Role Endpoints
### 2.1 Home Summary
Fetches summary data for the home screen.

**Endpoint**: `GET /user/home/summary`  
**Access**: User  
**Headers**: `Authorization: Bearer <token>`  

**Example Request**:
```bash
curl -X GET https://api.inventory-app.com/api/v1/user/home/summary \
-H "Accept: application/json" \
-H "Authorization: Bearer <token>"
```

**Response (200 OK)**:
```json
{
    "data": {
        "activeDevicesCount": 2,
        "deviceHistoryCount": 5
    }
}
```

### 2.2 Device List
Retrieves a paginated list of active devices assigned to the user.

**Endpoint**: `GET /user/devices`  
**Access**: User  
**Headers**: `Authorization: Bearer <token>`  
**Query Params**: `?page=1&perPage=10` (optional, defaults: page=1, perPage=10)  

**Example Request**:
```bash
curl -X GET https://api.inventory-app.com/api/v1/user/devices?page=1&perPage=10 \
-H "Accept: application/json" \
-H "Authorization: Bearer <token>"
```

**Response (200 OK)**:
```json
{
    "data": [
        {
            "assignmentId": 101,
            "device": {
                "deviceId": 20,
                "categoryName": "Laptop Kantor",
                "brand": "Dell Latitude",
                "serialNumber": "J5KFG73"
            }
        }
    ],
    "meta": {
        "currentPage": 1,
        "lastPage": 2,
        "total": 15
    }
}
```

### 2.3 Device Details
Fetches detailed information for a specific device.

**Endpoint**: `GET /user/devices/{id}`  
**Access**: User  
**Headers**: `Authorization: Bearer <token>`  
**URL Params**: `id` (integer): Device ID  

**Example Request**:
```bash
curl -X GET https://api.inventory-app.com/api/v1/user/devices/20 \
-H "Accept: application/json" \
-H "Authorization: Bearer <token>"
```

**Response (200 OK)**:
```json
{
    "data": {
        "deviceId": 20,
        "brand": "Dell Latitude 5420",
        "serialNumber": "J5KFG73",
        "assetCode": "COMP/LAP/0725/098",
        "assignedDate": "2024-01-21",
        "spec1": "Intel Core i5-1135G7",
        "spec2": "16 GB DDR4",
        "spec3": "512 GB NVMe SSD",
        "spec4": "",
        "spec5": ""
    }
}
```
> **Note:** The `spec1`–`spec5` fields can be `null` in the database. Handle possible `null` values.

### 2.4 Report Issue
Submits a problem report for a device.

**Endpoint**: `POST /user/devices/{id}/report`  
**Access**: User  
**Headers**: `Authorization: Bearer <token>`  
**URL Params**: `id` (integer): Device ID  
**Request Body**:
| Field       | Type   | Description                       |
|-------------|--------|-----------------------------------|
| description | string | Required. Problem description.    |
| date        | string | Required. Date of the issue (ISO 8601, e.g., `2025-07-20`). |

**Example Request**:
```bash
curl -X POST https://api.inventory-app.com/api/v1/user/devices/20/report \
-H "Accept: application/json" \
-H "Authorization: Bearer <token>" \
-H "Content-Type: application/json" \
-d '{"description":"Laptop screen flickers."}'
```

**Response (200 OK)**:
```json
{
    "message": "Report submitted successfully.",
    "errorCode": null
}
```

### 2.5 User Profile
Fetches user profile data.

**Endpoint**: `GET /user/profile`  
**Access**: User  
**Headers**: `Authorization: Bearer <token>`  

**Example Request**:
```bash
curl -X GET https://api.inventory-app.com/api/v1/user/profile \
-H "Accept: application/json" \
-H "Authorization: Bearer <token>"
```

**Response (200 OK)**:
```json
{
    "data": {
        "name": "Budi Santoso",
        "pn": "12345678",
        "department": "Operasional",
        "branch": "KCP Sudirman",
        "position": "Customer Service"
    }
}
```

### 2.6 Device History
Fetches a paginated history of devices assigned to the user.

**Endpoint**: `GET /user/history`  
**Access**: User  
**Headers**: `Authorization: Bearer <token>`  
**Query Params**: `?page=1&perPage=10` (optional, defaults: page=1, perPage=10)  

**Example Request**:
```bash
curl -X GET https://api.inventory-app.com/api/v1/user/history?page=1&perPage=10 \
-H "Accept: application/json" \
-H "Authorization: Bearer <token>"
```

**Response (200 OK)**:
```json
{
    "data": [
        {
            "deviceName": "Lenovo Thinkpad T480s",
            "serialNumber": "H9LFG12",
            "assignedDate": "2022-01-10",
            "returnedDate": "2024-01-20"
        }
    ],
    "meta": {
        "currentPage": 1,
        "lastPage": 3,
        "total": 25
    }
}
```