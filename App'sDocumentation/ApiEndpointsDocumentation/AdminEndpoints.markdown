# API Documentation - Admin Role Endpoints (Mobile Inventory App v1.1)

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
            "assetCode": "COMP/LAP/0725/098",
            "brand": "Dell",
            "brandName": "Latitude 5420",
            "serialNumber": "J5KFG73",
            "condition": "Baik",
            "category": "Laptop Kantor",
            "spec1": "Intel Core i5-1135G7",
            "spec2": "16 GB DDR4",
            "spec3": "512 GB NVMe SSD",
            "spec4": null,
            "spec5": null,
            "isAssigned": true,
            "assignedTo": "Budi Santoso",
            "assignedDate": "2024-01-21",
            "createdAt": "2024-01-15T10:30:00Z",
            "createdBy": "ADMIN01",
            "updatedAt": "2024-01-21T09:15:00Z",
            "updatedBy": "ADMIN01"
        }
    ],
    "meta": {
        "currentPage": 1,
        "lastPage": 10,
        "total": 100
    }
}
```

### 3.4 Device Details
Fetches detailed information for a specific device including assignment history.

**Endpoint**: `GET /admin/devices/{id}`  
**Access**: Admin  
**Headers**: `Authorization: Bearer <token>`  
**URL Params**: `id` (integer): Device ID  

**Example Request**:
```bash
curl -X GET https://api.inventory-app.com/api/v1/admin/devices/20 \
-H "Accept: application/json" \
-H "Authorization: Bearer <token>"
```

**Response (200 OK)**:
```json
{
    "data": {
        "deviceId": 20,
        "assetCode": "COMP/LAP/0725/098",
        "brand": "Dell",
        "brandName": "Latitude 5420",
        "serialNumber": "J5KFG73",
        "condition": "Baik",
        "category": "Laptop Kantor",
        "spec1": "Intel Core i5-1135G7",
        "spec2": "16 GB DDR4",
        "spec3": "512 GB NVMe SSD",
        "spec4": null,
        "spec5": null,
        "devDate": "2024-01-15",
        "currentAssignment": {
            "assignmentId": 101,
            "user": {
                "userId": 1,
                "name": "Budi Santoso",
                "pn": "12345678",
                "position": "Customer Service"
            },
            "branch": {
                "branchId": 5,
                "unitName": "KCP Sudirman",
                "branchCode": "JKT001"
            },
            "assignedDate": "2024-01-21",
            "status": "Digunakan",
            "notes": "Standard assignment"
        },
        "assignmentHistory": [
            {
                "assignmentId": 100,
                "userName " : "Ahmad Yani",
                "userPn": "87654321",
                "assignedDate": "2024-01-15",
                "returnedDate": "2024-01-20",
                "status": "Digunakan",
                "notes": "Previous assignment"
            }
        ],
        "createdAt": "2024-01-15T10:30:00Z",
        "createdBy": "ADMIN01",
        "updatedAt": "2024-01-21T09:15:00Z",
        "updatedBy": "ADMIN01"
    }
}
```

### 3.5 Create Device
Creates a new device in the inventory.

**Endpoint**: `POST /admin/devices`  
**Access**: Admin  
**Headers**: `Authorization: Bearer <token>`  
**Request Body**:
| Field | Type | Description |
|-------|------|-------------|
| brand | string | Required. Device brand (max 50). |
| brand_name | string | Required. Device model/series (max 50). |
| serial_number | string | Required. Unique serial number (max 50). |
| asset_code | string | Required. Unique asset code (max 20). |
| bribox_id | string | Required. Category ID (must exist in briboxes). |
| condition | string | Required. One of: "Baik", "Rusak", "Perlu Pengecekan". |
| spec1 | string | Optional. Specification 1 (max 100). |
| spec2 | string | Optional. Specification 2 (max 100). |
| spec3 | string | Optional. Specification 3 (max 100). |
| spec4 | string | Optional. Specification 4 (max 100). |
| spec5 | string | Optional. Specification 5 (max 100). |
| dev_date | string | Optional. Development/Purchase date (ISO 8601). |

**Example Request**:
```bash
curl -X POST https://api.inventory-app.com/api/v1/admin/devices \
-H "Accept: application/json" \
-H "Authorization: Bearer <token>" \
-H "Content-Type: application/json" \
-d '{
    "brand": "Dell",
    "brand_name": "Latitude 5420",
    "serial_number": "DL5420ABC123",
    "asset_code": "COMP/LAP/0725/099",
    "bribox_id": "01",
    "condition": "Baik",
    "spec1": "Intel Core i5-1135G7",
    "spec2": "16 GB DDR4",
    "spec3": "512 GB NVMe SSD",
    "dev_date": "2024-01-15"
}'
```

**Response (201 Created)**:
```json
{
    "data": {
        "deviceId": 21,
        "assetCode": "COMP/LAP/0725/099",
        "brand": "Dell",
        "brandName": "Latitude 5420",
        "serialNumber": "DL5420ABC123",
        "condition": "Baik"
    }
}
```

### 3.6 Update Device
Updates an existing device.

**Endpoint**: `PUT /admin/devices/{id}`  
**Access**: Admin  
**Headers**: `Authorization: Bearer <token>`  
**URL Params**: `id` (integer): Device ID  
**Request Body**: Same fields as Create Device (all optional for updates)

**Example Request**:
```bash
curl -X PUT https://api.inventory-app.com/api/v1/admin/devices/21 \
-H "Accept: application/json" \
-H "Authorization: Bearer <token>" \
-H "Content-Type: application/json" \
-d '{
    "condition": "Perlu Pengecekan",
    "spec4": "Windows 11 Pro"
}'
```

**Response (200 OK)**:
```json
{
    "data": {
        "deviceId": 21,
        "assetCode": "COMP/LAP/0725/099",
        "brand": "Dell",
        "brandName": "Latitude 5420",
        "serialNumber": "DL5420ABC123",
        "condition": "Perlu Pengecekan"
    }
}
```

### 3.7 Delete Device
Deletes a device from the inventory.

**Endpoint**: `DELETE /admin/devices/{id}`  
**Access**: Admin  
**Headers**: `Authorization: Bearer <token>`  
**URL Params**: `id` (integer): Device ID  

**Example Request**:
```bash
curl -X DELETE https://api.inventory-app.com/api/v1/admin/devices/21 \
-H "Accept: application/json" \
-H "Authorization: Bearer <token>"
```

**Response (200 OK)**:
```json
{
    "message": "Device deleted successfully.",
    "errorCode": null
}
```

**Error Response (400 Bad Request)**:
```json
{
    "message": "Cannot delete device that is currently assigned.",
    "errorCode": "ERR_DEVICE_ASSIGNED"
}
```

### 3.8 Device Assignments Management
Lists device assignments with filtering and pagination.

**Endpoint**: `GET /admin/device-assignments`  
**Access**: Admin  
**Headers**: `Authorization: Bearer <token>`  
**Query Params**: `?search=dell&status=Digunakan&branchId=5&activeOnly=true&page=1&perPage=20` (all optional)  

**Example Request**:
```bash
curl -X GET https://api.inventory-app.com/api/v1/admin/device-assignments?activeOnly=true&page=1&perPage=10 \
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
                "assetCode": "COMP/LAP/0725/098",
                "brand": "Dell",
                "brandName": "Latitude 5420",
                "serialNumber": "J5KFG73"
            },
            "user": {
                "userId": 1,
                "name": "Budi Santoso",
                "pn": "12345678",
                "position": "Customer Service",
                "department": "Operasional"
            },
            "branch": {
                "branchId": 5,
                "unitName": "KCP Sudirman",
                "branchCode": "JKT001"
            },
            "assignedDate": "2024-01-21",
            "returnedDate": null,
            "status": "Digunakan",
            "notes": "Standard assignment",
            "isActive": true,
            "createdAt": "2024-01-21T10:00:00Z",
            "createdBy": "ADMIN01"
        }
    ],
    "meta": {
        "currentPage": 1,
        "lastPage": 5,
        "total": 45
    }
}
```

### 3.9 Create Device Assignment
Creates a new device assignment.

**Endpoint**: `POST /admin/device-assignments`  
**Access**: Admin  
**Headers**: `Authorization: Bearer <token>`  
**Request Body**:
| Field | Type | Description |
|-------|------|-------------|
| device_id | integer | Required. Device ID (must exist and be available). |
| user_id | integer | Required. User ID (must exist). |
| assigned_date | string | Required. Assignment date (ISO 8601, not future). |
| status | string | Optional. One of: "Digunakan", "Tidak Digunakan", "Cadangan" (default: "Digunakan"). |
| notes | string | Optional. Assignment notes (max 500). |

**Example Request**:
```bash
curl -X POST https://api.inventory-app.com/api/v1/admin/device-assignments \
-H "Accept: application/json" \
-H "Authorization: Bearer <token>" \
-H "Content-Type: application/json" \
-d '{
    "device_id": 21,
    "user_id": 2,
    "assigned_date": "2024-07-21",
    "status": "Digunakan",
    "notes": "Assignment for new employee"
}'
```

**Response (201 Created)**:
```json
{
    "data": {
        "assignmentId": 102,
        "deviceId": 21,
        "userId": 2,
        "assignedDate": "2024-07-21",
        "status": "Digunakan"
    }
}
```

**Error Response (400 Bad Request)**:
```json
{
    "message": "Device is already assigned to another user.",
    "errorCode": "ERR_DEVICE_ALREADY_ASSIGNED"
}
```

### 3.10 Update Device Assignment
Updates an existing device assignment.

**Endpoint**: `PUT /admin/device-assignments/{id}`  
**Access**: Admin  
**Headers**: `Authorization: Bearer <token>`  
**URL Params**: `id` (integer): Assignment ID  
**Request Body**:
| Field | Type | Description |
|-------|------|-------------|
| status | string | Optional. One of: "Digunakan", "Tidak Digunakan", "Cadangan". |
| notes | string | Optional. Assignment notes (max 500). |
| returned_date | string | Optional. Return date (ISO 8601, after assigned_date). |

**Example Request**:
```bash
curl -X PUT https://api.inventory-app.com/api/v1/admin/device-assignments/102 \
-H "Accept: application/json" \
-H "Authorization: Bearer <token>" \
-H "Content-Type: application/json" \
-d '{
    "status": "Tidak Digunakan",
    "notes": "Device temporarily not in use"
}'
```

**Response (200 OK)**:
```json
{
    "data": {
        "assignmentId": 102,
        "status": "Tidak Digunakan",
        "returnedDate": null,
        "notes": "Device temporarily not in use"
    }
}
```

### 3.11 Return Device
Marks a device assignment as returned.

**Endpoint**: `POST /admin/device-assignments/{id}/return`  
**Access**: Admin  
**Headers**: `Authorization: Bearer <token>`  
**URL Params**: `id` (integer): Assignment ID  
**Request Body**:
| Field | Type | Description |
|-------|------|-------------|
| returned_date | string | Optional. Return date (ISO 8601, default: today). |
| return_notes | string | Optional. Return notes (max 500). |

**Example Request**:
```bash
curl -X POST https://api.inventory-app.com/api/v1/admin/device-assignments/102/return \
-H "Accept: application/json" \
-H "Authorization: Bearer <token>" \
-H "Content-Type: application/json" \
-d '{
    "returned_date": "2024-07-21",
    "return_notes": "Employee resigned"
}'
```

**Response (200 OK)**:
```json
{
    "data": {
        "assignmentId": 102,
        "returnedDate": "2024-07-21",
        "message": "Device returned successfully."
    }
}
```

**Error Response (400 Bad Request)**:
```json
{
    "message": "Device has already been returned.",
    "errorCode": "ERR_DEVICE_ALREADY_RETURNED"
}
```

### 3.12 Users Management
Lists users with pagination and filtering.

**Endpoint**: `GET /admin/users`  
**Access**: Admin  
**Headers**: `Authorization: Bearer <token>`  
**Query Params**: `?search=budi&departmentId=1&branchId=5&page=1&perPage=20` (all optional)  

**Example Request**:
```bash
curl -X GET https://api.inventory-app.com/api/v1/admin/users?search=budi&page=1&perPage=10 \
-H "Accept: application/json" \
-H "Authorization: Bearer <token>"
```

**Response (200 OK)**:
```json
{
    "data": [
        {
            "userId": 1,
            "pn": "12345678",
            "name": "Budi Santoso",
            "position": "Customer Service",
            "department": {
                "departmentId": 1,
                "name": "Operasional"
            },
            "branch": {
                "branchId": 5,
                "unitName": "KCP Sudirman",
                "branchCode": "JKT001"
            },
            "activeDevicesCount": 2
        }
    ],
    "meta": {
        "currentPage": 1,
        "lastPage": 3,
        "total": 25
    }
}
```

### 3.13 Branches List
Fetches all branches.

**Endpoint**: `GET /admin/branches`  
**Access**: Admin  
**Headers**: `Authorization: Bearer <token>`  

**Example Request**:
```bash
curl -X GET https://api.inventory-app.com/api/v1/admin/branches \
-H "Accept: application/json" \
-H "Authorization: Bearer <token>"
```

**Response (200 OK)**:
```json
{
    "data": [
        {
            "branchId": 5,
            "unitName": "KCP Sudirman",
            "branchCode": "JKT001",
            "address": "Jl. Sudirman No. 123, Jakarta",
            "mainBranch": {
                "mainBranchId": 1,
                "name": "Kantor Pusat Jakarta"
            }
        }
    ]
}
```

### 3.14 Device Categories
Fetches all device categories (briboxes).

**Endpoint**: `GET /admin/categories`  
**Access**: Admin  
**Headers**: `Authorization: Bearer <token>`  

**Example Request**:
```bash
curl -X GET https://api.inventory-app.com/api/v1/admin/categories \
-H "Accept: application/json" \
-H "Authorization: Bearer <token>"
```

**Response (200 OK)**:
```json
{
    "data": [
        {
            "briboxId": "01",
            "name": "Laptop Kantor",
            "description": "Laptop untuk keperluan kantor",
            "category": {
                "categoryId": 1,
                "name": "Komputer"
            }
        }
    ]
}
```