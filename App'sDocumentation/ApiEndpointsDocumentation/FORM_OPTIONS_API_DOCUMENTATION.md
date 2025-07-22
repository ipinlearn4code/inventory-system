
# Form Options API Documentation

## Overview
The Form Options API provides dynamic form field options and validation rules for device and device assignment management. This API supports features like search/filtering, field-specific queries, and comprehensive validation rules.

## Base URL
All Form Options endpoints are under the `/api/v1/` prefix and require authentication.

---

## Endpoints

### 1. Device Form Options

#### Get Device Form Options
Retrieve all form options needed for device creation/editing forms.

**Endpoint:** `GET /api/v1/devices/form-options`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Query Parameters:**
- `search` (optional): Filter options by search term
- `field` (optional): Get options for specific field only

**Available Fields:**
- `brands` - Device brand options
- `brandNames` - Device brand name/model options  
- `briboxes` - Device category (bribox) options
- `conditions` - Device condition options
- `statuses` - Device status options
- `categories` - Bribox category options

**Response Format:**
```json
{
    "success": true,
    "data": {
        "brands": [
            {
                "value": "Dell",
            }
        ],
        "brandNames": [
            {
                "value": "Latitude 5520",
            }
        ],
        "briboxes": [
            {
                "value": "LAPTOP-001",
                "label": "LAPTOP-001 - Laptop (IT Equipment)",
            }
        ],
        "conditions": [
            {
                "value": "Baik",
            },
            {
                "value": "Rusak", 
            },
            {
                "value": "Perlu Pengecekan",
            }
        ],
        "statuses": [
            {
                "value": "Digunakan",
            },
            {
                "value": "Tidak Digunakan",
            },
            {
                "value": "Cadangan",
            }
        ]
    }
}
```

**Example Requests:**

Get all device form options:
```bash
curl -X GET "http://localhost:8000/api/v1/devices/form-options" \
  -H "Authorization: Bearer your-token"
```

Get only brand options:
```bash
curl -X GET "http://localhost:8000/api/v1/devices/form-options?field=brands" \
  -H "Authorization: Bearer your-token"
```

Search bribox options:
```bash
curl -X GET "http://localhost:8000/api/v1/devices/form-options?field=briboxes&search=laptop" \
  -H "Authorization: Bearer your-token"
```

---

### 2. Device Assignment Form Options

#### Get Device Assignment Form Options
Retrieve all form options needed for device assignment creation/editing forms.

**Endpoint:** `GET /api/v1/device-assignments/form-options`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Query Parameters:**
- `search` (optional): Filter options by search term
- `field` (optional): Get options for specific field only

**Available Fields:**
- `devices` - Available devices for assignment
- `users` - User options
- `branches` - Branch options
- `departments` - Department options

**Response Format:**
```json
{
    "success": true,
    "data": {
        "devices": [
            {
                "device_id": 1,
                "label": "Dell Latitude 5520 (SN123456)",
                "asset_code": "DEV-001",
            }
        ],
        "users": [
            {
                "user_id": 1,
                "label": "12345 - John Doe (IT Department, Jakarta Pusat)",
            }
        ],
        "branches": [
            {
                "branch_id": 1,
                "label": "Jakarta Pusat (Head Office)",
            }
        ],
        "departments": [
            {
                "department_id": "IT",
                "label": "IT Department",
            }
        ]
    }
}
```

**Example Requests:**

Get all device assignment form options:
```bash
curl -X GET "http://localhost:8000/api/v1/device-assignments/form-options" \
  -H "Authorization: Bearer your-token"
```

Get only available devices:
```bash
curl -X GET "http://localhost:8000/api/v1/device-assignments/form-options?field=devices" \
  -H "Authorization: Bearer your-token"
```

Search users by name:
```bash
curl -X GET "http://localhost:8000/api/v1/device-assignments/form-options?field=users&search=john" \
  -H "Authorization: Bearer your-token"
```

---




## Route to retrieve field-specific options for form selections.
 
  This endpoint allows clients to fetch dynamic options for various fields
  used in forms. The options
  can be filtered based on a search query when applicable.
 
  URL: /form-options/fields
  Method: GET
  Controller: V1FormOptionsController
  Action: getFieldOptions
 
  Query Parameters:
  - field (string): The name of the field for which options are requested.
    Supported values include:
    - 'brands'
    - 'brandNames'
    - 'briboxes'
    - 'conditions'
    - 'statuses'
    - 'categories'
    - 'devices'
    - 'users'
    - 'branches'
    - 'departments'
  - search (string, optional): A search term to filter the options for fields
    that support filtering (e.g., 'brands', 'categories', 'devices', etc.).
 
  Response:
  - JSON object containing the requested field options in the format:
    {
        "data": {
            "<field>": [<options>]
        }
    }
 
  Example Usage:
  GET /form-options/fields?field=brands&search=electronics
 
  Example Response:
  {
      "data": {
          "brands": [
              "Brand A",
              "Brand B",
              "Brand C"
          ]
      }
  }
 /

### 3. Form Validation Rules

#### Get Device Validation Rules
Retrieve validation rules for device creation/editing forms.

**Endpoint:** `GET /api/v1/form-options/validation/devices`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Response Format:**
```json
{
    "success": true,
    "data": {
        "rules": {
            "brand": ["required", "string", "max:50"],
            "brand_name": ["required", "string", "max:50"],
            "serial_number": ["required", "string", "max:50", "unique:devices,serial_number"],
            "asset_code": ["required", "string", "max:20", "unique:devices,asset_code"],
            "bribox_id": ["required", "exists:briboxes,bribox_id"],
            "condition": ["required", "in:Baik,Rusak,Perlu Pengecekan"],
            "status": ["required", "in:Digunakan,Tidak Digunakan,Cadangan"],
            "spec1": ["nullable", "string", "max:100"],
            "spec2": ["nullable", "string", "max:100"],
            "spec3": ["nullable", "string", "max:100"],
            "spec4": ["nullable", "string", "max:100"],
            "spec5": ["nullable", "string", "max:100"],
            "dev_date": ["nullable", "date"]
        },
        "messages": {
            "brand.required": "Brand is required",
            "brand_name.required": "Brand name/model is required",
            "serial_number.required": "Serial number is required",
            "serial_number.unique": "This serial number already exists",
            "asset_code.required": "Asset code is required",
            "asset_code.unique": "This asset code already exists",
            "bribox_id.required": "Device category (bribox) is required",
            "bribox_id.exists": "Selected device category is invalid",
            "condition.required": "Device condition is required",
            "condition.in": "Invalid device condition",
            "status.required": "Device status is required",
            "status.in": "Invalid device status"
        }
    }
}
```

#### Get Device Assignment Validation Rules
Retrieve validation rules for device assignment creation/editing forms.

**Endpoint:** `GET /api/v1/form-options/validation/device-assignments`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Response Format:**
```json
{
    "success": true,
    "data": {
        "rules": {
            "device_id": ["required", "exists:devices,device_id"],
            "user_id": ["required", "exists:users,user_id"],
            "branch_id": ["required", "exists:branch,branch_id"],
            "assigned_date": ["required", "date", "before_or_equal:today"],
            "returned_date": ["nullable", "date", "after_or_equal:assigned_date"],
            "notes": ["nullable", "string", "max:500"]
        },
        "messages": {
            "device_id.required": "Device is required",
            "device_id.exists": "Selected device is invalid",
            "user_id.required": "User is required",
            "user_id.exists": "Selected user is invalid",
            "branch_id.required": "Branch is required",
            "branch_id.exists": "Selected branch is invalid",
            "assigned_date.required": "Assignment date is required",
            "assigned_date.before_or_equal": "Assignment date cannot be in the future",
            "returned_date.after_or_equal": "Return date must be after assignment date",
            "notes.max": "Notes cannot exceed 500 characters"
        }
    }
}
```

---

## Usage Examples

### Frontend Form Integration

#### Dynamic Dropdown Population
```javascript
// Get device form options for dropdowns
async function loadDeviceFormOptions() {
    try {
        const response = await fetch('/api/v1/devices/form-options', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Populate brand dropdown
            populateDropdown('brand-select', result.data.brands);
            
            // Populate bribox dropdown  
            populateDropdown('bribox-select', result.data.briboxes);
            
            // Populate condition dropdown
            populateDropdown('condition-select', result.data.conditions);
        }
    } catch (error) {
        console.error('Failed to load form options:', error);
    }
}

function populateDropdown(selectId, options) {
    const select = document.getElementById(selectId);
    select.innerHTML = '<option value="">Select...</option>';
    
    options.forEach(option => {
        const optionElement = document.createElement('option');
        optionElement.value = option.value;
        optionElement.textContent = option.label;
        select.appendChild(optionElement);
    });
}
```

#### Searchable Autocomplete
```javascript
// Implement searchable user selection
async function searchUsers(searchTerm) {
    try {
        const response = await fetch(
            `/api/v1/device-assignments/form-options?field=users&search=${encodeURIComponent(searchTerm)}`,
            {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            }
        );
        
        const result = await response.json();
        
        if (result.success) {
            return result.data.users;
        }
        return [];
    } catch (error) {
        console.error('Failed to search users:', error);
        return [];
    }
}
```

#### Form Validation Integration
```javascript
// Get and apply validation rules
async function setupFormValidation() {
    try {
        const response = await fetch('/api/v1/form-options/validation/devices', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Apply validation rules to form
            applyValidationRules('device-form', result.data.rules, result.data.messages);
        }
    } catch (error) {
        console.error('Failed to load validation rules:', error);
    }
}
```

---

## Error Handling

All endpoints return consistent error responses:

```json
{
    "success": false,
    "message": "Error description",
    "error": "Detailed error (only in debug mode)"
}
```

**Common HTTP Status Codes:**
- `200` - Success
- `400` - Bad Request (missing required parameters)
- `401` - Unauthorized (invalid/missing token)
- `403` - Forbidden (insufficient permissions)
- `500` - Internal Server Error

---

## Performance Notes

1. **Caching**: Form options are cacheable as they change infrequently
2. **Pagination**: Large datasets (users, devices) include pagination
3. **Search**: All search operations use database indexes for performance
4. **Lazy Loading**: Use field-specific requests to reduce payload size

---

## Security

1. **Authentication**: All endpoints require valid Bearer token
2. **Authorization**: Role-based access control (admin/superadmin required)
3. **Rate Limiting**: API calls are rate-limited (100 requests per minute)
4. **Input Validation**: All search inputs are sanitized to prevent SQL injection

---

## Architecture

### Service Layer Pattern
- **FormOptionsService**: Business logic for retrieving form options
- **Repository Pattern**: Data access abstraction
- **Dependency Injection**: Testable, maintainable code structure

### SOLID Principles Applied
- **Single Responsibility**: Each service has one clear purpose
- **Open/Closed**: Extensible through interfaces
- **Liskov Substitution**: Interface-based design
- **Interface Segregation**: Focused, specific interfaces
- **Dependency Inversion**: High-level modules depend on abstractions
