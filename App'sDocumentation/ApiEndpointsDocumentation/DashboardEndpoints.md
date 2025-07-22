# Dashboard API Documentation

## Overview
The Dashboard API provides endpoints for retrieving key performance indicators (KPIs), chart data, and activity logs for the inventory system dashboard.

## Base URL
```
/api/v1/dashboard
```

## Authentication
All endpoints require authentication using Bearer token in the Authorization header:
```
Authorization: Bearer {your-token-here}
```

## Endpoints

### 1. Get Dashboard KPIs
Retrieve key performance indicators for the dashboard overview.

**Endpoint:** `GET /api/v1/dashboard/kpis`

**Query Parameters:**
- `branchId` (integer, optional): Filter data by specific branch

**Response:**
```json
{
  "data": {
    "totalDevices": 150,
    "inUse": 120,
    "available": 30,
    "damaged": 5,
    "activityLog": [
      {
        "type": "device-created",
        "Category": "success",
        "title": "Perangkat Dibuat",
        "description": "Perangkat baru ditambahkan",
        "user": "admin",
        "date": "2024-01-15",
        "time": "14:30:25"
      },
      {
        "type": "device-issue",
        "Category": "warning",
        "title": "Laporan Masalah",
        "description": "Laptop mengalami masalah keyboard",
        "user": "12345",
        "date": "2024-01-15",
        "time": "13:45:10"
      }
    ]
  }
}
```

**KPI Definitions:**
- `totalDevices`: Total number of devices in the system
- `inUse`: Number of devices currently assigned to users
- `available`: Number of devices available for assignment
- `damaged`: Number of devices with condition "Rusak"

**Activity Log Types:**
- `device-created`: New device added
- `device-updated`: Device information updated
- `device-deleted`: Device removed
- `device-issue`: Issue report submitted

### 2. Get Dashboard Charts
Retrieve chart data for dashboard visualizations.

**Endpoint:** `GET /api/v1/dashboard/charts`

**Query Parameters:**
- `branchId` (integer, optional): Filter data by specific branch

**Response:**
```json
{
  "data": {
    "deviceConditions": [
      {
        "condition": "Baik",
        "count": 140
      },
      {
        "condition": "Rusak",
        "count": 5
      },
      {
        "condition": "Perlu Pengecekan",
        "count": 5
      }
    ],
    "devicesPerBranch": [
      {
        "branchName": "Jakarta Pusat",
        "count": 45
      },
      {
        "branchName": "Jakarta Selatan",
        "count": 38
      },
      {
        "branchName": "Bandung",
        "count": 32
      },
      {
        "branchName": "Surabaya",
        "count": 35
      }
    ]
  }
}
```

**Chart Data Descriptions:**
- `deviceConditions`: Distribution of devices by their condition status
- `devicesPerBranch`: Number of devices assigned to users in each branch (only branches with devices are included)

## Data Filtering

### Branch-Specific Data
When `branchId` is provided:
- **KPIs**: Filtered to show only devices assigned to users in that specific branch
- **Charts**: Device conditions filtered by branch; devices per branch shows all branches for comparison

### Global Data
When no `branchId` is provided:
- Shows system-wide statistics across all branches
- Includes all devices regardless of assignment status

## Response Details

### Activity Log Entry Structure
```json
{
  "type": "device-created|device-updated|device-deleted|device-issue",
  "Category": "success|info|warning",
  "title": "Human-readable title",
  "description": "Detailed description of the action",
  "user": "Username or PN who performed the action",
  "date": "YYYY-MM-DD",
  "time": "HH:MM:SS"
}
```

### Chart Data Guidelines
- All counts are integers
- Branch names are the official unit names from the branch table
- Device conditions use the exact values from the device condition enum
- Only branches with at least one assigned device appear in devicesPerBranch

## Business Logic

### KPI Calculations
1. **Total Devices**: Count of all devices in the system
2. **In Use**: Count of devices with active assignments (returned_date is null)
3. **Available**: Total devices minus devices in use
4. **Damaged**: Count of devices with condition = "Rusak"

### Activity Log Rules
- Limited to the 10 most recent entries
- Sorted by creation date (most recent first)
- Includes actions from inventory_log table
- Special handling for issue reports from user submissions

## Error Handling
- Invalid `branchId`: Returns empty results or filtered data
- No data available: Returns zero counts and empty arrays
- Authentication required: Returns 401 for missing/invalid tokens

## Examples

### cURL Examples

#### Get global dashboard KPIs
```bash
curl -X GET "https://api.example.com/api/v1/dashboard/kpis" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

#### Get branch-specific dashboard KPIs
```bash
curl -X GET "https://api.example.com/api/v1/dashboard/kpis?branchId=1" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

#### Get chart data for visualizations
```bash
curl -X GET "https://api.example.com/api/v1/dashboard/charts" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

#### Get branch-specific chart data
```bash
curl -X GET "https://api.example.com/api/v1/dashboard/charts?branchId=2" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

## Use Cases

### Admin Dashboard
- Display overall system health and usage statistics
- Show recent activity across all branches
- Monitor device conditions and availability

### Branch Manager Dashboard
- Filter by specific branch to see local statistics
- Track devices assigned to users in their branch
- Monitor branch-specific activity and issues

### Real-time Updates
- KPIs update in real-time as devices are assigned/returned
- Activity log shows immediate updates for new actions
- Chart data reflects current system state
