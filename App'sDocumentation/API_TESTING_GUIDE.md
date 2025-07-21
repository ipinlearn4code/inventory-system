# API Testing Guide

This guide helps you test the Mobile Inventory App API endpoints.

## Prerequisites

1. Laravel application is running
2. Database is properly configured with sample data
3. Laravel Sanctum is configured

## Base URL
```
http://localhost:8000/api/v1
```

## Testing Authentication

### 1. Login
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
-H "Accept: application/json" \
-H "Content-Type: application/json" \
-d '{
    "pn": "12345678",
    "password": "your_password",
    "device_name": "Test Device"
}'
```

Expected Response:
```json
{
    "data": {
        "token": "1|token_string_here",
        "user": {
            "userId": 1,
            "name": "User Name",
            "pn": "12345678",
            "role": "user"
        }
    }
}
```

### 2. Test User Endpoints (replace TOKEN with actual token from login)

#### Home Summary
```bash
curl -X GET http://localhost:8000/api/v1/user/home/summary \
-H "Accept: application/json" \
-H "Authorization: Bearer TOKEN"
```

#### User Devices
```bash
curl -X GET http://localhost:8000/api/v1/user/devices \
-H "Accept: application/json" \
-H "Authorization: Bearer TOKEN"
```

#### User Profile
```bash
curl -X GET http://localhost:8000/api/v1/user/profile \
-H "Accept: application/json" \
-H "Authorization: Bearer TOKEN"
```

### 3. Test Admin Endpoints (use admin/superadmin token)

#### Dashboard KPIs
```bash
curl -X GET http://localhost:8000/api/v1/admin/dashboard/kpis \
-H "Accept: application/json" \
-H "Authorization: Bearer ADMIN_TOKEN"
```

#### Dashboard Charts
```bash
curl -X GET http://localhost:8000/api/v1/admin/dashboard/charts \
-H "Accept: application/json" \
-H "Authorization: Bearer ADMIN_TOKEN"
```

#### Admin Devices
```bash
curl -X GET http://localhost:8000/api/v1/admin/devices \
-H "Accept: application/json" \
-H "Authorization: Bearer ADMIN_TOKEN"
```

## Features Implemented

✅ **Step 1**: Laravel with Sanctum for authentication  
✅ **Step 2**: Authentication endpoints (`/auth/login`, `/auth/refresh`, `/auth/logout`, `/auth/push/register`)  
✅ **Step 3**: User endpoints with pagination  
✅ **Step 4**: Admin endpoints with filtering and pagination  
✅ **Step 5**: Standardized response format  
✅ **Step 6**: Rate limiting (100 req/min/user)  
✅ **Step 7**: 30-second timeout rules  
✅ **Step 8**: Offline caching support for key endpoints  
✅ **Step 9**: Ready for testing  
✅ **Step 10**: Changelog endpoint available  
✅ **Step 11**: Laravel Telescope available for monitoring  

## Rate Limiting
- 100 requests per minute per user
- Returns 429 status code when exceeded

## Timeout
- 30 seconds timeout for all API requests

## Offline Support
- `/user/home/summary`
- `/user/devices` 
- `/user/profile`

These endpoints include cache headers for offline functionality.

## Error Handling
All errors follow the standardized format:
```json
{
    "message": "Error description",
    "errorCode": "ERR_CODE",
    "errors": {} // For validation errors
}
```

## Changelog
Available at: `http://localhost:8000/api/v1/changelog`
