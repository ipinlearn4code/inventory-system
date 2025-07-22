# Form Options API Test Script

## Test the Form Options API endpoints

### 1. Device Form Options
```bash
# Get all device form options
curl -X GET "http://localhost:8000/api/v1/admin/form-options/devices" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"

# Get only brands with search
curl -X GET "http://localhost:8000/api/v1/admin/form-options/devices?field=brands&search=Dell" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"

# Get device validation rules
curl -X GET "http://localhost:8000/api/v1/admin/form-options/validation/devices" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

### 2. Device Assignment Form Options
```bash
# Get all device assignment form options
curl -X GET "http://localhost:8000/api/v1/admin/form-options/device-assignments" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"

# Get only available devices
curl -X GET "http://localhost:8000/api/v1/admin/form-options/device-assignments?field=devices" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"

# Search users
curl -X GET "http://localhost:8000/api/v1/admin/form-options/device-assignments?field=users&search=admin" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"

# Get assignment validation rules
curl -X GET "http://localhost:8000/api/v1/admin/form-options/validation/device-assignments" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

### 3. Example Response - Device Form Options
```json
{
  "data": {
    "brands": [
      {"value": "Dell", "label": "Dell"},
      {"value": "HP", "label": "HP"}
    ],
    "brandNames": [
      {"value": "OptiPlex 7090", "label": "OptiPlex 7090"},
      {"value": "EliteBook 840", "label": "EliteBook 840"}
    ],
    "briboxes": [
      {
        "value": "A1",
        "label": "A1 - Desktop Computer (Hardware)",
        "bribox_id": "A1",
        "type": "Desktop Computer",
        "category": "Hardware",
        "category_id": 1
      }
    ],
    "conditions": [
      {"value": "Baik", "label": "Baik"},
      {"value": "Rusak", "label": "Rusak"},
      {"value": "Perlu Pengecekan", "label": "Perlu Pengecekan"}
    ],
    "statuses": [
      {"value": "Digunakan", "label": "Digunakan"},
      {"value": "Tidak Digunakan", "label": "Tidak Digunakan"},
      {"value": "Cadangan", "label": "Cadangan"}
    ]
  }
}
```

### 4. Frontend Integration Examples

#### React.js Hook
```javascript
import { useState, useEffect } from 'react';

const useFormOptions = (endpoint, field = null, search = '') => {
  const [options, setOptions] = useState({});
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchOptions = async () => {
      setLoading(true);
      try {
        const params = new URLSearchParams();
        if (field) params.append('field', field);
        if (search) params.append('search', search);
        
        const response = await fetch(`/api/v1/admin/form-options/${endpoint}?${params}`, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        });
        
        if (!response.ok) throw new Error('Failed to fetch options');
        
        const data = await response.json();
        setOptions(data.data);
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    fetchOptions();
  }, [endpoint, field, search]);

  return { options, loading, error };
};

// Usage in component
const DeviceForm = () => {
  const { options: deviceOptions } = useFormOptions('devices');
  const { options: brandOptions } = useFormOptions('devices', 'brands');
  
  return (
    <form>
      <select name="brand">
        {brandOptions.brands?.map(brand => (
          <option key={brand.value} value={brand.value}>
            {brand.label}
          </option>
        ))}
      </select>
      
      <select name="condition">
        {deviceOptions.conditions?.map(condition => (
          <option key={condition.value} value={condition.value}>
            {condition.label}
          </option>
        ))}
      </select>
    </form>
  );
};
```

#### Vue.js Composable
```javascript
import { ref, onMounted, watch } from 'vue';

export const useFormOptions = (endpoint, field = null, search = '') => {
  const options = ref({});
  const loading = ref(false);
  const error = ref(null);

  const fetchOptions = async () => {
    loading.value = true;
    error.value = null;
    
    try {
      const params = new URLSearchParams();
      if (field) params.append('field', field);
      if (search) params.append('search', search);
      
      const response = await fetch(`/api/v1/admin/form-options/${endpoint}?${params}`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      });
      
      if (!response.ok) throw new Error('Failed to fetch options');
      
      const data = await response.json();
      options.value = data.data;
    } catch (err) {
      error.value = err.message;
    } finally {
      loading.value = false;
    }
  };

  onMounted(fetchOptions);
  watch([endpoint, field, search], fetchOptions);

  return { options, loading, error, refetch: fetchOptions };
};
```

### 5. Notes for Integration

1. **Authentication**: All endpoints require admin or superadmin role
2. **Rate Limiting**: 100 requests per minute per user
3. **Caching**: Consider caching options that don't change frequently
4. **Error Handling**: Handle 401, 403, and 429 status codes appropriately
5. **Search**: Use debouncing when implementing search functionality
6. **Performance**: Request specific fields when you don't need all options

### 6. Available Endpoints Summary

- `GET /api/v1/admin/form-options/devices` - Device form options
- `GET /api/v1/admin/form-options/device-assignments` - Assignment form options  
- `GET /api/v1/admin/form-options/validation/devices` - Device validation rules
- `GET /api/v1/admin/form-options/validation/device-assignments` - Assignment validation rules

Each endpoint supports:
- `search` parameter for filtering
- `field` parameter for specific field options
