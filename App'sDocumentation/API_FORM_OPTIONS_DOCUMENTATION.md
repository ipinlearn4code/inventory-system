# API Form Options Documentation

## Overview
This API provides form options and validation rules that mirror the dropdown options used in Filament admin panels. External applications can consume these endpoints to build forms with the same data options.

## Base URL
```
/api/v1/admin/form-options/
```

## Authentication
All endpoints require authentication with admin or superadmin role:
- **Authentication**: Bearer token (Laravel Sanctum)
- **Required Role**: admin or superadmin
- **Rate Limit**: 100 requests per minute

## Endpoints

### 1. Device Form Options
**GET** `/api/v1/admin/form-options/devices`

Get all dropdown options for device creation/editing forms.

#### Query Parameters
- `search` (optional): Filter options by search term
- `field` (optional): Get options for specific field only

#### Response Format
```json
{
  "success": true,
  "data": {
    "brands": [
      {
        "value": "Dell"
      }
    ],
    "brandNames": [
      {
        "value": "OptiPlex 7090"
      }
    ],
    "briboxes": [
      {
        "value": "A1",
        "label": "PC STANDART (PC)"
      },
      {
        "value": "A2",
        "label": "PC STANDART BDS SERVER (PC)"
      }
    ],
    "conditions": [
      {
        "value": "Baik",
      },
      {
        "value": "Rusak",
        "label": "Rusak"
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

#### Example Usage
```bash
# Get all device form options
curl -H "Authorization: Bearer YOUR_TOKEN" \
  https://your-domain.com/api/v1/admin/form-options/devices

# Get only brand options with search
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "https://your-domain.com/api/v1/admin/form-options/devices?field=brands&search=Dell"
```

### 2. Device Assignment Form Options
**GET** `/api/v1/admin/form-options/device-assignments`

Get all dropdown options for device assignment creation/editing forms.

#### Query Parameters
- `search` (optional): Filter options by search term
- `field` (optional): Get options for specific field only

#### Response Format
```json
{
  "data": {
    "devices": [
      {
        "value": 1,
        "label": "AST001 - Dell OptiPlex 7090 (DL001234567)",
        "device_id": 1,
        "asset_code": "AST001",
        "brand": "Dell",
        "brand_name": "OptiPlex 7090",
        "serial_number": "DL001234567",
        "condition": "Baik",
        "status": "Tidak Digunakan"
      }
    ],
    "users": [
      {
        "value": 1,
        "label": "USER01 - John Doe (IT Department)",
        "user_id": 1,
        "pn": "USER01",
        "name": "John Doe",
        "position": "Software Engineer",
        "department": "IT Department",
        "department_id": 1
      }
    ],
    "branches": [
      {
        "value": 1,
        "label": "Jakarta Pusat (Head Office)",
        "branch_id": 1,
        "unit_name": "Jakarta Pusat",
        "branch_code": "JKT001",
        "main_branch": "Head Office",
        "address": "Jl. Sudirman No. 1"
      }
    ],
    "departments": [
      {
        "value": 1,
        "label": "IT Department",
        "department_id": 1,
        "name": "IT Department"
      }
    ]
  }
}
```

### 3. Device Validation Rules
**GET** `/api/v1/admin/form-options/validation/devices`

Get validation rules for device creation/editing.

#### Response Format
```json
{
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
      "serial_number.unique": "This serial number already exists"
    }
  }
}
```

### 4. Device Assignment Validation Rules
**GET** `/api/v1/admin/form-options/validation/device-assignments`

Get validation rules for device assignment creation/editing.

#### Response Format
```json
{
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
      "user_id.required": "User is required"
    }
  }
}
```

## Field-Specific Endpoints

You can get options for specific fields by adding the `field` parameter:

### Available Fields for Devices
- `brands` - Device brands
- `brandNames` - Device brand names/models
- `briboxes` - Device categories with bribox info
- `conditions` - Device conditions (static)
- `statuses` - Device statuses (static)
- `categories` - Bribox categories

### Available Fields for Device Assignments
- `devices` - Available devices (not currently assigned)
- `users` - Users with department info
- `branches` - Branches with main branch info
- `departments` - Departments

### Example: Get Only Available Devices
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "https://your-domain.com/api/v1/admin/form-options/device-assignments?field=devices"
```

### Example: Search Users
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "https://your-domain.com/api/v1/admin/form-options/device-assignments?field=users&search=john"
```

## Integration Example

### React.js Form Integration
```javascript
// Fetch form options
const fetchDeviceFormOptions = async () => {
  const response = await fetch('/api/v1/admin/form-options/devices', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });
  return response.json();
};

// Use in form
const [formOptions, setFormOptions] = useState({});

useEffect(() => {
  fetchDeviceFormOptions().then(data => {
    setFormOptions(data.data);
  });
}, []);

// Render dropdown
<select>
  {formOptions.brands?.map(brand => (
    <option key={brand.value} value={brand.value}>
      {brand.label}
    </option>
  ))}
</select>
```

### Vue.js Form Integration
```javascript
// In your Vue component
export default {
  data() {
    return {
      formOptions: {},
      deviceForm: {
        brand: '',
        brand_name: '',
        bribox_id: ''
      }
    };
  },
  async mounted() {
    const response = await this.$http.get('/api/v1/admin/form-options/devices');
    this.formOptions = response.data.data;
  }
};
```

## Error Handling

### HTTP Status Codes
- `200` - Success
- `401` - Unauthorized (invalid or missing token)
- `403` - Forbidden (insufficient role)
- `429` - Too Many Requests (rate limit exceeded)
- `500` - Internal Server Error

### Error Response Format
```json
{
  "message": "Error message",
  "errorCode": "ERROR_CODE"
}
```

## Best Practices

1. **Caching**: Cache form options for reasonable periods since they don't change frequently
2. **Search**: Use the search parameter for large datasets to improve performance
3. **Specific Fields**: Request only needed fields using the `field` parameter
4. **Error Handling**: Always handle rate limiting and authentication errors
5. **Validation**: Use the validation endpoints to ensure consistent form validation

## Notes

- All dropdown options are identical to those used in the Filament admin panel
- Available devices list only shows devices that are not currently assigned
- User options include department information for better identification
- Branch options include main branch information for hierarchy context
- Search functionality works across relevant fields for each option type
