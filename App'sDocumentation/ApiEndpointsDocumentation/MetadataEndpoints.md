# Metadata API Documentation

## Overview
The Metadata API provides endpoints for retrieving reference data such as users, branches, and categories. This data is typically used for dropdowns, form options, and system configuration.

## Base URL
```
/api/v1/metadata
```

## Authentication
All endpoints require authentication using Bearer token in the Authorization header:
```
Authorization: Bearer {your-token-here}
```

## Endpoints

### 1. Get Users List
Retrieve a paginated list of users with optional filtering.

**Endpoint:** `GET /api/v1/metadata/users`

**Query Parameters:**
- `search` (string, optional): Search by name, PN, or position
- `departmentId` (integer, optional): Filter by department ID
- `branchId` (integer, optional): Filter by branch ID
- `page` (integer, optional): Page number (default: 1)
- `perPage` (integer, optional): Items per page (default: 20)

**Response:**
```json
{
  "data": [
    {
      "userId": 1,
      "pn": "12345",
      "name": "John Doe",
      "position": "Senior Developer",
      "department": {
        "departmentId": 1,
        "name": "Information Technology"
      },
      "branch": {
        "branchId": 1,
        "unitName": "Jakarta Pusat",
        "branchCode": "JKT001"
      },
      "activeDevicesCount": 2
    }
  ],
  "meta": {
    "currentPage": 1,
    "lastPage": 5,
    "total": 95
  }
}
```

### 2. Get Branches List
Retrieve a list of all branches.

**Endpoint:** `GET /api/v1/metadata/branches`

**Response:**
```json
{
  "data": [
    {
      "branchId": 1,
      "unitName": "Jakarta Pusat",
      "branchCode": "JKT001",
      "address": "Jl. Sudirman No. 1, Jakarta Pusat",
      "mainBranch": {
        "mainBranchId": 1,
        "name": "Kantor Pusat"
      }
    },
    {
      "branchId": 2,
      "unitName": "Jakarta Selatan",
      "branchCode": "JKT002",
      "address": "Jl. Gatot Subroto No. 15, Jakarta Selatan",
      "mainBranch": {
        "mainBranchId": 1,
        "name": "Kantor Pusat"
      }
    }
  ]
}
```

### 3. Get Categories List
Retrieve a list of device categories (briboxes) with their associated category information.

**Endpoint:** `GET /api/v1/metadata/categories`

**Response:**
```json
{
  "data": [
    {
      "briboxId": 1,
      "name": "Laptop Business",
      "description": "Laptop untuk keperluan bisnis dan administrasi",
      "category": {
        "categoryId": 1,
        "name": "IT Equipment"
      }
    },
    {
      "briboxId": 2,
      "name": "Monitor LED 24 inch",
      "description": "Monitor LED untuk workstation",
      "category": {
        "categoryId": 1,
        "name": "IT Equipment"
      }
    }
  ]
}
```

## Data Structure Details

### User Object
```json
{
  "userId": "Unique user identifier",
  "pn": "Personnel Number (employee ID)",
  "name": "Full name of the user",
  "position": "Job position/title",
  "department": {
    "departmentId": "Department identifier",
    "name": "Department name"
  },
  "branch": {
    "branchId": "Branch identifier", 
    "unitName": "Branch unit name",
    "branchCode": "Branch code"
  },
  "activeDevicesCount": "Number of currently assigned devices"
}
```

### Branch Object
```json
{
  "branchId": "Unique branch identifier",
  "unitName": "Official branch name",
  "branchCode": "Branch code",
  "address": "Physical address of the branch",
  "mainBranch": {
    "mainBranchId": "Parent branch identifier",
    "name": "Parent branch name"
  }
}
```

### Category Object
```json
{
  "briboxId": "Unique bribox identifier",
  "name": "Bribox name/type",
  "description": "Detailed description",
  "category": {
    "categoryId": "Category identifier",
    "name": "Category name"
  }
}
```

## Use Cases

### Form Population
These endpoints are commonly used to populate dropdown lists and selection forms:

- **User Selection**: Device assignment forms, transfer forms
- **Branch Filtering**: Dashboard filters, reporting filters
- **Category Selection**: Device creation forms, filtering options

### Validation
Reference data for validating relationships:
- Ensure selected users exist and are active
- Validate branch assignments
- Verify device categories exist

### Search and Filtering
Support for finding specific records:
- Search users by name or employee number
- Filter by organizational structure (department/branch)
- Category-based device filtering

## Business Logic

### User Data Rules
- Only active users are returned
- Users include current device assignment counts
- Department and branch information is always included
- Search is case-insensitive across name, PN, and position

### Branch Hierarchy
- All branches include their parent main branch information
- Branches are sorted alphabetically by unit name
- Branch codes are unique identifiers for integration

### Category Organization
- Categories represent device types available in the system
- Each bribox belongs to a broader category
- Used for device classification and assignment rules

## Error Handling
- Invalid department/branch IDs: Returns empty results
- Large result sets: Automatic pagination applied
- Missing relationships: Null values for optional fields

## Examples

### cURL Examples

#### Get all users
```bash
curl -X GET "https://api.example.com/api/v1/metadata/users" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

#### Search users by name
```bash
curl -X GET "https://api.example.com/api/v1/metadata/users?search=john&page=1&perPage=10" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

#### Filter users by branch
```bash
curl -X GET "https://api.example.com/api/v1/metadata/users?branchId=1&perPage=20" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

#### Get all branches
```bash
curl -X GET "https://api.example.com/api/v1/metadata/branches" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

#### Get device categories
```bash
curl -X GET "https://api.example.com/api/v1/metadata/categories" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

### JavaScript/React Examples

#### Building a user selection dropdown
```javascript
const loadUsers = async (searchTerm = '') => {
  try {
    const response = await fetch(`/api/v1/metadata/users?search=${searchTerm}&perPage=50`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    });
    
    const data = await response.json();
    
    return data.data.map(user => ({
      value: user.userId,
      label: `${user.pn} - ${user.name} (${user.branch.unitName})`,
      user: user
    }));
  } catch (error) {
    console.error('Error loading users:', error);
    return [];
  }
};
```

#### Creating branch filter options
```javascript
const loadBranches = async () => {
  try {
    const response = await fetch('/api/v1/metadata/branches', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    });
    
    const data = await response.json();
    
    return [
      { value: '', label: 'All Branches' },
      ...data.data.map(branch => ({
        value: branch.branchId,
        label: `${branch.unitName} (${branch.branchCode})`
      }))
    ];
  } catch (error) {
    console.error('Error loading branches:', error);
    return [];
  }
};
```

#### Loading device categories for forms
```javascript
const loadCategories = async () => {
  try {
    const response = await fetch('/api/v1/metadata/categories', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    });
    
    const data = await response.json();
    
    return data.data.map(item => ({
      value: item.briboxId,
      label: `${item.name} (${item.category.name})`,
      category: item.category
    }));
  } catch (error) {
    console.error('Error loading categories:', error);
    return [];
  }
};
```

## Performance Considerations

### Caching Recommendations
- **Branches**: Cache for 24 hours (rarely change)
- **Categories**: Cache for 12 hours (change infrequently)
- **Users**: Cache for 1 hour (more dynamic due to device assignments)

### Pagination Guidelines
- Default page size is optimized for form dropdowns
- For large datasets, use search to narrow results
- Consider implementing infinite scroll for mobile apps

### Search Optimization
- Use debouncing for search inputs (300ms delay)
- Minimum 2 characters for search queries
- Clear search results when input is cleared
