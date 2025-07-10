# API Documentation - Mobile Inventory App v1.1

This document outlines the API endpoints for the mobile inventory application.

**Base URL**: `https://api.inventory-app.com/api/v1`

**Last Updated**: 10 July 2025

## Authentication (Laravel Sanctum)
All endpoints requiring authentication **MUST** include an `Authorization` header with a Bearer Token obtained from login.

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
Responses are wrapped in a `data` object.
```json
{
    "data": {
        "userId": 1,
        "name": "Budi Santoso"
    }
}
```

### ❌ Error (4xx, 5xx)
Errors include a `message` and optional `errors` object with a specific error code.
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
Mobile apps should cache responses for `/user/home/summary`, `/user/devices`, and `/user/profile` to support offline access. Sync cached data when online.

## Versioning
API changes will be communicated via `https://api.inventory-app.com/changelog` at least 30 days in advance.

## 1. Authentication & General Endpoints
### 1.1 Login
Obtains an authentication token for subsequent requests.

**Endpoint**: `POST /auth/login`  
**Access**: Public  
**Request Body**:
| Field | Type | Description |
|-------|------|-------------|
| pn | string | Required. User’s Personal Number. |
| password | string | Required. User password. |
| device_name | string | Required. Mobile device name (e.g., "Budi's iPhone 15"). |

**Example Request**:
```bash
curl -X POST https://api.inventory-app.com/api/v1/auth/login \
-H "Accept: application/json" \
-H "Content-Type: application/json" \
-d '{"pn":"12345678","password":"secret123","device_name":"Budi\'s iPhone 15"}'
```

**Response (200 OK)**:
```json
{
    "data": {
        "token": "2|aBcDeFgHiJkLmNoPqRsTuVwXyZ1234567890abc",
        "user": {
            "userId": 1,
            "name": "Budi Santoso",
            "pn": "12345678",
            "role": "user"
        }
    }
}
```
**Note**: Superadmin login via mobile returns `admin` role.

### 1.2 Token Refresh
Refreshes an existing token to extend session validity.

**Endpoint**: `POST /auth/refresh`  
**Access**: User, Admin  
**Headers**: `Authorization: Bearer <token>`  
**Request Body**:
| Field | Type | Description |
|-------|------|-------------|
| device_name | string | Required. Mobile device name. |

**Example Request**:
```bash
curl -X POST https://api.inventory-app.com/api/v1/auth/refresh \
-H "Accept: application/json" \
-H "Authorization: Bearer <token>" \
-H "Content-Type: application/json" \
-d '{"device_name":"Budi\'s iPhone 15"}'
```

**Response (200 OK)**:
```json
{
    "data": {
        "token": "3|newToken1234567890xyz",
        "expiresIn": 86400
    }
}
```

### 1.3 Logout
Revokes the current token.

**Endpoint**: `POST /auth/logout`  
**Access**: User, Admin  
**Headers**: `Authorization: Bearer <token>`  

**Example Request**:
```bash
curl -X POST https://api.inventory-app.com/api/v1/auth/logout \
-H "Accept: application/json" \
-H "Authorization: Bearer <token>"
```

**Response (200 OK)**:
```json
{
    "message": "Logout successful.",
    "errorCode": null
}
```

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
> **Note:** The `spec1`–`spec5` fields can be `null` in the database. Your implementation should handle possible `null` values for these fields.

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

## 3. Admin Role Endpoints
### 3.1 KPI Dashboard
Fetches key performance indicators for the admin dashboard.

**Endpoint**: `GET /admin/dashboard/kpis`  
**Access**: Admin  
**Headers**: `Authorization: Bearer <token>`  
**Query Params**: `?branchId=5` (optional, filter by branch)  

**Example Request**:
```bash
curl -X GET https://api.inventory-app.com/api/v1/admin/dashboard/kpis?branchId=5 \
-H "Accept: application/json" \
-H "Authorization: Bearer <token>"
```

**Response (200 OK)**:
```json
{
    "data": {
        "totalDevices": 1250,
        "inUse": 975,
        "available": 215,
        "damaged": 60,
        "activityLog": [
            {
                "type" : "device-asigned",
                "Category": "warning",
                "title": "Perangkat Ditugaskan",
                "description" : "Printer HP Deskjet kepada Ali",
                "user": "Joko Tingkir",
                "date": "2024-01-20",
                "time": "07:06:34"
            },
            {
                "type" : "device-deleted",
                "Category": "warning",
                "title": "Perangkat Dihapus",
                "description" : "Printer HP Deskjet",
                "user": "Joko Kendil",
                "date": "2025-07-09",
                "time": "07:06:34"
            }
        ]

    }
}
```

### 3.2 Chart Data
Fetches data for admin dashboard charts.

**Endpoint**: `GET /admin/dashboard/charts`  
**Access**: Admin  
**Headers**: `Authorization: Bearer <token>`  
**Query Params**: `?branchId=5` (optional)  

**Example Request**:
```bash
curl -X GET https://api.inventory-app.com/api/v1/admin/dashboard/charts?branchId=5 \
-H "Accept: application/json" \
-H "Authorization: Bearer <token>"
```

**Response (200 OK)**:
```json
{
    "data": {
        "deviceConditions": [
            { "condition": "Baik", "count": 1100 },
            { "condition": "Rusak", "count": 60 },
            { "condition": "Perlu Pengecekan", "count": 90 }
        ],
        "devicesPerBranch": [
            { "branchName": "KCU Jakarta", "count": 350 },
            { "branchName": "KCU Bandung", "count": 280 }
        ]
    }
}
```

### 3.3 Device Management
Searches and lists devices with pagination.

**Endpoint**: `GET /admin/devices`  
**Access**: Admin  
**Headers**: `Authorization: Bearer <token>`  
**Query Params**: `?search=dell&condition=Baik&page=1&perPage=20` (all optional)  

**Example Request**:
```bash
curl -X GET https://api.inventory-app.com/api/v1/admin/devices?search=dell&condition=Baik&page=1&perPage=20 \
-H "Accept: application/json" \
-H "Authorization: Bearer <token>"
```

**Response (200 OK)**:
```json
{
    "data": [
        {
            "deviceId": 20,
            "brand": "Dell Latitude",
            "serialNumber": "J5KFG73",
            "condition": "Baik"
        }
    ],
    "meta": {
        "currentPage": 1,
        "lastPage": 10,
        "total": 100
    }
}
```