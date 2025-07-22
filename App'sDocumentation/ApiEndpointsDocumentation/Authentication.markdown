# API Documentation - Authentication (Mobile Inventory App v1.1)

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

## 1. Authentication Endpoints
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