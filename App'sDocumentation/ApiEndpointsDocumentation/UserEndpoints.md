# User API Documentation

## Overview
The User API provides endpoints for user-specific operations including viewing assigned devices, device history, profile information, and reporting device issues. This API is designed for mobile and web applications used by end users.

## Base URL
```
/api/v1/user
```

## Authentication
All endpoints require authentication using Bearer token in the Authorization header:
```
Authorization: Bearer {your-token-here}
```

## Endpoints

### 1. Get Home Summary
Retrieve summary statistics for the authenticated user's home screen.

**Endpoint:** `GET /api/v1/user/home-summary`

**Response:**
```json
{
  "data": {
    "activeDevicesCount": 3,
    "deviceHistoryCount": 8
  }
}
```

**Data Definitions:**
- `activeDevicesCount`: Number of devices currently assigned to the user
- `deviceHistoryCount`: Total number of device assignments (including returned devices)

### 2. Get User's Active Devices
Retrieve a paginated list of devices currently assigned to the authenticated user.

**Endpoint:** `GET /api/v1/user/devices`

**Query Parameters:**
- `page` (integer, optional): Page number (default: 1)
- `perPage` (integer, optional): Items per page (default: 10)

**Response:**
```json
{
  "data": [
    {
      "assignmentId": 1,
      "device": {
        "deviceId": 1,
        "categoryName": "Laptop",
        "brand": "Dell",
        "brandName": "Latitude 7420",
        "serialNumber": "DL123456789"
      }
    },
    {
      "assignmentId": 2,
      "device": {
        "deviceId": 2,
        "categoryName": "Monitor",
        "brand": "Samsung",
        "brandName": "24 inch LED",
        "serialNumber": "SM987654321"
      }
    }
  ],
  "meta": {
    "currentPage": 1,
    "lastPage": 1,
    "total": 3
  }
}
```

### 3. Get Device Details
Retrieve detailed information about a specific device assigned to the authenticated user.

**Endpoint:** `GET /api/v1/user/devices/{id}`

**Path Parameters:**
- `id` (integer, required): Device ID

**Response:**
```json
{
  "data": {
    "deviceId": 1,
    "brand": "Dell",
    "brandName": "Latitude 7420",
    "serialNumber": "DL123456789",
    "assetCode": "BRI-001",
    "assignedDate": "2024-01-15",
    "spec1": "Intel i5",
    "spec2": "8GB RAM",
    "spec3": "256GB SSD",
    "spec4": "14 inch",
    "spec5": "Windows 11"
  }
}
```

**Error Response:**
```json
{
  "message": "Device not found or not assigned to you.",
  "errorCode": "ERR_DEVICE_NOT_FOUND"
}
```

### 4. Report Device Issue
Submit an issue report for a device assigned to the authenticated user.

**Endpoint:** `POST /api/v1/user/devices/{id}/report-issue`

**Path Parameters:**
- `id` (integer, required): Device ID

**Request Body:**
```json
{
  "description": "Laptop keyboard is not working properly",
  "date": "2024-01-20"
}
```

**Validation Rules:**
- `description`: required, string, max 1000 characters
- `date`: required, valid date

**Response:**
```json
{
  "message": "Report submitted successfully.",
  "errorCode": null
}
```

**Error Response:**
```json
{
  "message": "Device not found or not assigned to you.",
  "errorCode": "ERR_DEVICE_NOT_FOUND"
}
```

### 5. Get User Profile
Retrieve the authenticated user's profile information.

**Endpoint:** `GET /api/v1/user/profile`

**Response:**
```json
{
  "data": {
    "name": "John Doe",
    "pn": "12345",
    "department": "Information Technology",
    "branch": "Jakarta Pusat",
    "position": "Senior Developer"
  }
}
```

### 6. Get Device History
Retrieve a paginated list of previously assigned devices (returned devices) for the authenticated user.

**Endpoint:** `GET /api/v1/user/history`

**Query Parameters:**
- `page` (integer, optional): Page number (default: 1)
- `perPage` (integer, optional): Items per page (default: 10)

**Response:**
```json
{
  "data": [
    {
      "brand": "Dell",
      "deviceName": "Latitude 7420",
      "serialNumber": "DL123456789",
      "assignedDate": "2023-06-15",
      "returnedDate": "2023-12-31"
    },
    {
      "brand": "HP",
      "deviceName": "EliteBook 840",
      "serialNumber": "HP987654321",
      "assignedDate": "2023-01-10",
      "returnedDate": "2023-06-10"
    }
  ],
  "meta": {
    "currentPage": 1,
    "lastPage": 2,
    "total": 15
  }
}
```

## Security & Access Control

### User Authorization
- All endpoints validate that the authenticated user can only access their own data
- Device details and issue reporting are restricted to devices currently assigned to the user
- No access to other users' device information or assignments

### Data Privacy
- Users can only see their own assignment history
- Personal information is limited to their own profile data
- No access to system-wide statistics or other users' data

## Business Rules

### Device Access Rules
1. **Current Assignments Only**: Users can only view details and report issues for devices currently assigned to them
2. **Assignment Validation**: All device operations verify the device is actually assigned to the authenticated user
3. **Issue Reporting**: Issue reports are logged with the user's PN and timestamp for tracking

### Data Consistency
- Device counts and history are calculated in real-time
- Profile information reflects current user data from the system
- Assignment dates are displayed in user's local format

## Error Codes
- `ERR_DEVICE_NOT_FOUND`: Device not found or not assigned to the user
- `ERR_UNAUTHORIZED_ACCESS`: User attempting to access data they don't have permission for
- `ERR_INVALID_REPORT`: Issue report validation failed

## Mobile App Integration

### Recommended Usage Patterns
1. **Home Screen**: Call `/home-summary` to display quick stats
2. **Device List**: Use `/devices` with pagination for device browsing
3. **Device Details**: Call `/devices/{id}` when user taps on a device
4. **Issue Reporting**: Integrate with `/devices/{id}/report-issue` for support tickets
5. **Profile Screen**: Use `/profile` for user information display
6. **History View**: Use `/history` with pagination for past assignments

### Offline Considerations
- Cache device list and details for offline viewing
- Queue issue reports for submission when connection is restored
- Profile data can be cached for extended periods

## Examples

### cURL Examples

#### Get home summary
```bash
curl -X GET "https://api.example.com/api/v1/user/home-summary" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

#### Get active devices (first page)
```bash
curl -X GET "https://api.example.com/api/v1/user/devices?page=1&perPage=5" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

#### Get device details
```bash
curl -X GET "https://api.example.com/api/v1/user/devices/1" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

#### Report device issue
```bash
curl -X POST "https://api.example.com/api/v1/user/devices/1/report-issue" \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "description": "Laptop screen has dead pixels",
    "date": "2024-01-20"
  }'
```

#### Get user profile
```bash
curl -X GET "https://api.example.com/api/v1/user/profile" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

#### Get device history
```bash
curl -X GET "https://api.example.com/api/v1/user/history?page=1&perPage=10" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

### JavaScript/React Examples

#### Fetching user's active devices
```javascript
const fetchUserDevices = async (page = 1) => {
  try {
    const response = await fetch(`/api/v1/user/devices?page=${page}&perPage=10`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    });
    
    if (!response.ok) throw new Error('Failed to fetch devices');
    
    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Error fetching devices:', error);
    throw error;
  }
};
```

#### Submitting issue report
```javascript
const reportIssue = async (deviceId, description, date) => {
  try {
    const response = await fetch(`/api/v1/user/devices/${deviceId}/report-issue`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        description,
        date
      })
    });
    
    if (!response.ok) throw new Error('Failed to submit report');
    
    const result = await response.json();
    return result;
  } catch (error) {
    console.error('Error submitting report:', error);
    throw error;
  }
};
```
